<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Entity\Question;
use App\Entity\TblCall;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InterviewController extends AbstractController
{
    #[Route('/interview/add-question', name: 'app_interview_add_question')]
    public function addQuestion(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = $request->request->all();
        $newQuestion = new Question();
        $newQuestion->setStatement($data['statement']);
        $newQuestion->setDevelopment(json_decode($data['development'], true));
        $call = $doctrine->getRepository(TblCall::class)->find($data['callId']);
        $newQuestion->setCall($call);
        if(isset($data['competenceId']))
        {
            $competence = $doctrine->getRepository(Competence::class)->find($data['competenceId']);
            $newQuestion->setCompetence($competence);
        }
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newQuestion);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se añadio nueva pregunta'], 200, []);
    }

    #[Route('/interview/read-questions', name: 'app_interview_read_questions')]
    public function readQuestions(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $callId = $request->query->get('callId');
        $queryRQ = $doctrine->getManager()->createQueryBuilder();
        $queryRQ
            ->select('q.id','q.statement','q.development','c.id as competenceId')
            ->from('App\Entity\Question','q')
            ->join('q.competence', 'c')
            ->where('q.call = :callId')
            ->setParameter('callId', $callId);
        $questionsArray = $queryRQ->getQuery()->getArraymessage();
        return new JsonResponse($questionsArray, 200, []);
    }

    #[Route('/interview/update-question', name: 'app_interview_update_question')]
    public function updateQuestion(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = $request->request->all();
        $question = $doctrine->getRepository(Question::class)->find($data['id']);
        $question->setStatement($data['statement']);
        $question->setDevelopment(json_decode($data['development'], true));
        if(isset($data['competenceId']))
        {
            $competence = $doctrine->getRepository(Competence::class)->find($data['competenceId']);
            $question->setCompetence($competence);
        }
        $entityManager = $doctrine->getManager();
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
}
