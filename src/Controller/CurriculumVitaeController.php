<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use App\Entity\FurtherTraining;
use App\Entity\TeachingExperience;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurriculumVitaeController extends AbstractController
{
    #[Route('/curriculum-vitae/academic-training', name: 'app_curriculum_vitae_academic_training')]
    public function academicTraining(ManagerRegistry $doctrine): Response
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
            $academicTraining->setisCertifiedTitle($value['isCertifiedTitle']);
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

    #[Route('/curriculum-vitae/further-training', name: 'app_curriculum_vitae_further_training')]
    public function furtherTraining(ManagerRegistry $doctrine): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        foreach($data as $key => $value){
            $furtherTraining = new FurtherTraining();
            $furtherTraining->setComplementarymodality($value['complementaryModality']);
            $furtherTraining->setTitlename($value['titleName']);
            $furtherTraining->setInstitution($value['institution']);
            $furtherTraining->setHours($value['hours']);
            $furtherTraining->setDate(new DateTime($value['date']));
            $furtherTraining->setCertifiedpdf($value['certifiedPdf']);
        
            $entityManager=$doctrine->getManager();
            $entityManager->persist($furtherTraining);
            $entityManager->flush();
        }

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardado nueva formación complementaria con nombre: '.$furtherTraining->getTitlename()]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
    
    #[Route('/curriculum-vitae/teaching-experience', name: 'app_curriculum_vitae_teaching_experience')]
    public function teachingExperience(ManagerRegistry $doctrine): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        foreach($data as $key => $value){

            $teachingExperience = new TeachingExperience();
            $teachingExperience -> setIsforeignuniversity($value['isForeignUniversity']);
            $teachingExperience -> setSnies($value['snies']);
            $teachingExperience -> setNameuniversity($value['nameUniversity']);
            $teachingExperience -> setFaculty($value['faculty']);
            $teachingExperience -> setProgram($value['program']);
            $teachingExperience -> setDateadmission(new DateTime($value['dateAdmission']));
            $teachingExperience -> setIsactive($value['isActive']);
            $teachingExperience -> setRetirementdate(new DateTime($value['retirementDate']));
            $teachingExperience -> setContractmodality($value['contractModality']);
            $teachingExperience -> setCourseload($value['courseLoad']);
            $teachingExperience -> setCertifiedpdf($value['certifiedPdf']);
         
        
            $entityManager=$doctrine->getManager();
            $entityManager->persist($teachingExperience);
            $entityManager->flush();
        }

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardada nueva experiencia docente']));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
}
