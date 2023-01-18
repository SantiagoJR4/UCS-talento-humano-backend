<?php

namespace App\Controller;

use App\Entity\Academictraining;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AcademicTrainingController extends AbstractController
{
    #[Route('/academicTraining', name: 'app_academic_training')]
    public function createAcademicTraining(ManagerRegistry $doctrine, Request $request) : Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);
        
        
        foreach($data as $key => $value){
            $academicTraining = new Academictraining();
            $academicTraining->setacademicmodality($value['academicModality']);
            $academicTraining->setTitlename($value['titleName']);
            $academicTraining->setDate(new DateTime($value['date']));
            
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
