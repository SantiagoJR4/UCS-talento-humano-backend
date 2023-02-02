<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use App\Entity\FurtherTraining;
use App\Entity\TeachingExperience;
use App\Entity\WorkExperience;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurriculumVitaeController extends AbstractController
{
    #[Route('/curriculum-vitae/academic-training', name: 'app_curriculum_vitae_academic_training')]
    public function academicTraining(ManagerRegistry $doctrine, Request $request): Response
    {
        $formData = $request->request->all();
        $degreeFile = $request->files->get('degreePdfFile');
        $certifiedFile = $request->files->get('certifiedTitlePdfFile') !== NULL ? $request->files->get('certifiedTitlePdfFile') : NULL;
        $formValues = [];
        foreach( $formData as $key => $value ) {
            $formValues[$key] = json_decode($value);
        }
        $academicTraining = new AcademicTraining();
        $academicTraining->setAcademicModality($formValues['academicModality']);
        $academicTraining->setDate(new DateTime($formValues['date']));
        $academicTraining->setProgramMethodology($formValues['programMethodology']);
        $academicTraining->setTitleName($formValues['titleName']);
        $academicTraining->setSnies($formValues['snies']);
        $academicTraining->setIsForeignUniversity($formValues['isForeignUniversity']);
        $academicTraining->setNameUniversity($formValues['nameUniversity']);
        $degreePath = '';
        $certifiedPath = '';
        if( $degreeFile instanceof UploadedFile ) {
            $newFileName = $formValues['degreePdfName'].time().'.'.$degreeFile->guessExtension();
            $degreeFile->move(
                $this->getParameter('uploads_directory'),
                $newFileName
            );
            $degreePath = $this->getParameter('uploads_directory').'/'.$newFileName;
            $academicTraining->setDegreePdf($degreePath);
        }
        if ($certifiedFile !== NULL && $certifiedFile instanceof UploadedFile) {
            $newFileName = $formValues['certifiedTitlePdfName'].time().'.'.$certifiedFile->guessExtension();
            $certifiedFile->move(
                $this->getParameter('uploads_directory'),
                $newFileName
            );
            $certifiedPath = $this->getParameter('uploads_directory').'/'.$newFileName;
            $academicTraining->setCertifiedTitlePdf($certifiedPath);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($academicTraining);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['degreePdf' => $degreePath,'certifiedTitlePdf' => $certifiedPath]));
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
            $teachingExperience -> setDateadmission(new DateTime($value['admissionDate']));
            $teachingExperience -> setIsactive($value['isActive']);

            $teachingExperience -> setContractmodality($value['contractModality']);
            $teachingExperience -> setCourseload(json_encode($value['courseLoad']));
            $teachingExperience -> setCertifiedpdf($value['certifiedPdf']);
         
            if($value['retirementDate'] !== NULL){
                $teachingExperience -> setRetirementdate(new DateTime($value['retirementDate']));
            }
            else{
                $teachingExperience -> setRetirementdate(NULL);
            }
                
        
            $entityManager=$doctrine->getManager();
            $entityManager->persist($teachingExperience);
            $entityManager->flush();
        }

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardada nueva experiencia docente']));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    #[Route('/curriculum-vitae/work-experience', name: 'app_curriculum_vitae_work_experience')]
    public function workExperience(ManagerRegistry $doctrine): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        foreach($data as $key => $value){

            $workExperience = new WorkExperience();
            $workExperience -> setCompanyname($value['companyName']);
            $workExperience -> setPosition($value['position']);
            $workExperience -> setDependence($value['dependence']);
            $workExperience -> setDepartment($value['department']);
            $workExperience -> setMunicipality($value['municipality']);
            $workExperience -> setCompanyaddress($value['companyAddress']);
            $workExperience -> setBossname($value['bossName']);
            $workExperience -> setPhone($value['phone']);
            $workExperience -> setAdmissiondate(new DateTime($value['admissionDate']));
            $workExperience -> setIsworking($value['isWorking']);
            $workExperience -> setCertifiedpdf($value['certifiedPdf']);
         
            if($value['retirementDate'] !== NULL){
                $workExperience -> setRetirementdate(new DateTime($value['retirementDate']));
            }
            else{
                $workExperience -> setRetirementdate(NULL);
            }
        
            $entityManager=$doctrine->getManager();
            $entityManager->persist($workExperience);
            $entityManager->flush();
        }

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardada nueva experiencia laboral']));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
}
