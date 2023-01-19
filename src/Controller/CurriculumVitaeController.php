<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurriculumVitaeController extends AbstractController
{
    #[Route('/curriculum-vitae/academic-training', name: 'app_curriculum_vitae_academic_training')]
    public function academicTraining(ManagerRegistry $doctrine, Request $request): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        foreach($data as $key => $value){
            $academicTraining = new AcademicTraining();
            $academicTraining->setAcademicModality($value['academicModality']);
            $academicTraining->setDate(new DateTime($value['date']));
            $academicTraining->setTitleName($value['titleName']);
            $academicTraining->setSnies($value['snies']);
            $academicTraining->setIsForeignUniversity($value['isForeignUniversity']);
            $academicTraining->setNameUniversity($value['nameUniversity']);
            $academicTraining->setDegreePdf($value['degreePdf']);
            $academicTraining->setCertifiedTitle($value['certifiedTitle']);
            $academicTraining->setCertifiedTitlePdf($value['certifiedTitlePdf']);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($academicTraining);
            $entityManager->flush();
        }

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardado nueva formación academica con nombre: '.$academicTraining->getTitlename()]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
