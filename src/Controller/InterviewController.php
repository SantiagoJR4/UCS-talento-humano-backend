<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Entity\CompetenceQuestion;
use App\Entity\Question;
use App\Entity\TblCall;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class InterviewController extends AbstractController
{
    #[Route('/interview/add-question', name: 'app_interview_add_question')]
    public function addQuestion(ManagerRegistry $doctrine, Request $request, SerializerInterface $sI): JsonResponse
    {
        $data = $request->request->all();
        $newQuestion = new Question();
        $newQuestion->setStatement($data['statement']);
        $newQuestion->setDevelopment(json_decode($data['development'], true));
        $call = $doctrine->getRepository(TblCall::class)->find($data['callId']);
        $newQuestion->setCall($call);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newQuestion);
        if(isset($data['competenciesIds']))
        {
            $competencies = json_decode($data['competenciesIds'], true);
            foreach ($competencies as $key => $competenceId) {
                $competence = $doctrine->getRepository(Competence::class)->find($competenceId);
                $newCompetenceQuestion = new CompetenceQuestion();
                $newCompetenceQuestion->setCompetence($competence);
                $newCompetenceQuestion->setQuestion($newQuestion);
                $entityManager->persist($newCompetenceQuestion);
            }
        }
        $entityManager->flush();
        $response = [
            'id' => $newQuestion->getId(),
            'statement' => $newQuestion->getStatement(),
            'development' => $newQuestion->isDevelopment(),
            'competenciesIds' => isset($data['competenciesIds']) ? $data['competenciesIds'] : []
        ];
        return new JsonResponse($response, 200, []);
    }

    #[Route('/interview/read-questions', name: 'app_interview_read_questions')]
    public function readQuestions(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $callId = $request->query->get('callId');
        $queryRQ = $doctrine->getManager()->createQueryBuilder();
        $queryRQ
            ->select('q.id','q.statement','q.development')
            ->from('App\Entity\Question','q')
            ->where('q.call = :callId')
            ->setParameter('callId', $callId);
        $questionsArray = $queryRQ->getQuery()->getArrayResult();
        foreach ($questionsArray as $key => $value)
        {
            $arrayCQ = $doctrine->getRepository(CompetenceQuestion::class)->findBy(["question" => $value['id']]);
            $idsCQ = array_map(
                function($itemCQ)
                {
                    $competence = $itemCQ->getCompetence();
                    return $competence->getId();
                },
                $arrayCQ
            );
            $questionsArray[$key]['competenciesIds'] = $idsCQ;
        }
        return new JsonResponse($questionsArray, 200, []);
    }

    #[Route('/interview/update-question', name: 'app_interview_update_question')]
    public function updateQuestion(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = $request->request->all();
        $question = $doctrine->getRepository(Question::class)->find($data['id']);
        $question->setStatement($data['statement']);
        $question->setDevelopment(json_decode($data['development'], true));
        $entityManager = $doctrine->getManager();
        $arrayCQ = $doctrine->getRepository(CompetenceQuestion::class)->findBy(["question" => $data['id']]);
        foreach ($arrayCQ as $key => $itemCQ)
        {
            $entityManager->remove($itemCQ);
        }
        if(isset($data['competenciesIds']))
        {
            $competencies = json_decode($data['competenciesIds'], true);
            foreach ($competencies as $key => $competenceId)
            {
                $competence = $doctrine->getRepository(Competence::class)->find($competenceId);
                $newCompetenceQuestion = new CompetenceQuestion();
                $newCompetenceQuestion->setCompetence($competence);
                $newCompetenceQuestion->setQuestion($question);
                $entityManager->persist($newCompetenceQuestion);
            }
        }
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha modificado la pregunta'], 200, []);
    }

    #[Route('/interview/delete-question', name: 'app_interview_delete_question')]
    public function deleteQuestion(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $questionId = $request->query->get('questionId');
        $entityManager = $doctrine->getManager();
        $questionToDelete = $entityManager->getRepository(Question::class)->find($questionId);
        $entityManager->remove($questionToDelete);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha eliminado la pregunta'], 200, []);
    }

    #[Route('/interview/get-all-competencies', name: 'app_interview_get_all_competencies')]
    public function getAllCompetencies(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $queryC = $doctrine->getManager()->createQueryBuilder();
        $queryC
            ->select('c')
            ->from('App\Entity\Competence', 'c');
        $allCompetencies = $queryC->getQuery()->getArrayResult();
        return new JsonResponse($allCompetencies, 200, []);
    }
}
