<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use App\Entity\PersonalData;
use App\Entity\User;
use App\Service\Helpers;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListCurriculumVitaeController extends AbstractController
{
    #[Route('/listCurriculum-vitae/{id}', name: 'app_list_curriculum_vitae')]
    public function listPersonalData(ManagerRegistry $doctrine, Helpers $helper, int $id, Request $request): Response
    {
        if($id === NULL){
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('El usuario no existe!!');
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;

        }else{
            $personaldata = $doctrine->getRepository(PersonalData::class)->findOneBy(['user'=>$request->get('id')]);
            $academicTraining = $doctrine->getRepository(AcademicTraining::class)->findBy(['user'=>$request->get('id')]);
        }

        

        $json = $helper->serializador($academicTraining);

        return $json;
    }
}
