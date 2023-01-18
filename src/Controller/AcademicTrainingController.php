<?php

namespace App\Controller;

use App\Entity\Academictraining;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AcademicTrainingController extends AbstractController
{
    #[Route('/academic-training', name: 'app_academic_training')]
    public function createAcademicTraining(ManagerRegistry $doctrine) : Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);
        $academicTraining = new Academictraining();

        $academicTraining->setacademicmodality($data['academicModality']);
        $academicTraining->setTitlename($data['titleName']);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($academicTraining);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardado nuevo producto con nombre: '.$academicTraining->getTitlename()]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
