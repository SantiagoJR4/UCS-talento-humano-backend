<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use App\Entity\CurriculumVitae;
use App\Entity\FurtherTraining;
use App\Entity\IntellectualProduction;
use App\Entity\Language;
use App\Entity\PersonalData;
use App\Entity\Record;
use App\Entity\ReferencesData;
use App\Entity\TeachingExperience;
use App\Entity\User;
use App\Entity\WorkExperience;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurriculumVitaeController extends AbstractController
{
    #[Route('/curriculum-vitae/personal-data', name:'app_curriculum_vitae_personal-data')]
    public function personalData(ManagerRegistry $doctrine, Request $request): Response
    {
        $formData = $request->request->all();
        $url_photo = $request->files->get('url_photo');
        $formValues =[];

        foreach($formData as $key => $value) {$formValues[$key] = json_decode($value);}
        $personalData = new CurriculumVitae();
        $personalData -> setResidenceAddress($formValues['residenceAddress']);
        $personalData -> setDepartment($formValues['department']);
        $personalData -> setMunicipality($formValues['municipality']);
        $personalData -> setDateIssue(new DateTime($formValues['date_issue']));
        $personalData -> setPlaceIssue($formValues['place_issue']);
        $personalData -> setBirthdate(new DateTime($formValues['birthdate']));
        $personalData -> setBirthplace($formValues['birthplace']);
        $personalData -> setGender($formValues['gender']);
        $personalData -> setBloodType($formValues['bloodType']);
        $personalData -> setMaritalStatus($formValues['maritalStatus']);
        $personalData -> setEps($formValues['eps']);
        $personalData -> setPension($formValues['pension']);
        
        $user = $doctrine -> getRepository(User::class)->find($request->get('id'));
        $personalData -> setUser($user);

        $url_photoPath='';
        if($url_photo instanceof UploadedFile){
            $newFileName = $formValues['urlPhotoName'].time().'.'.$url_photo->guessExtension();
            $url_photo->move(
                $this->getParameter('uploads_directory'),
                $newFileName
            );
            $url_photoPath = $this->getParameter('uploads_directory').'/'.$newFileName;
            $personalData -> setUrlPhoto($url_photoPath);
        }
        $entityManager = $doctrine->getManager();
        $entityManager->persist($personalData);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['urlPhoto' => $url_photoPath]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/curriculum-vitae/academic-training', name: 'app_curriculum_vitae_academic_training')]
    public function academicTraining(ManagerRegistry $doctrine, Request $request): Response
    {
        $formData = $request->request->all();
        $degreeFile = $request->files->get('degreePdfFile');
        $certifiedFile = $request->files->get('certifiedTitlePdfFile') !== NULL ? $request->files->get('certifiedTitlePdfFile') : NULL;
        $formValues = [];
        foreach( $formData as $key => $value ) { $formValues[$key] = json_decode($value); }
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
    public function furtherTraining(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): Response
    {
        $formData = $request->request->all();
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $certifiedFile = $request->files->get('certifiedPdfFile');
        $formValues = [];
        foreach( $formData as $key => $value ) { $formValues[$key] = json_decode($value); }
        $furtherTraining = new FurtherTraining();
        $furtherTraining->setComplementarymodality($formValues['complementaryModality']);
        $furtherTraining->setTitlename($formValues['titleName']);
        $furtherTraining->setInstitution($formValues['institution']);
        $furtherTraining->setHours($formValues['hours']);
        $furtherTraining->setDate(new DateTime($formValues['date']));
        $furtherTraining -> setUser($user);
        $certifiedPath = '';
        if ($certifiedFile instanceof UploadedFile) {
                $newFileName = $formValues['certifiedPdfName'].time().'.'.$certifiedFile->guessExtension();
                $certifiedFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFileName
                    );
                    $certifiedPath = $this->getParameter('uploads_directory').'/'.$newFileName;
        $furtherTraining->setCertifiedPdf($certifiedPath);
        }
        $entityManager=$doctrine->getManager();
        $entityManager->persist($furtherTraining);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['certified Pdf' => $certifiedPath]));
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

    #[Route('/curriculum-vitae/prodIntellectual', name: 'app_curriculum_vitae_prod_intellectual')]
    public function prodIntellectual(ManagerRegistry $doctrine): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(),true);

        foreach($data as $key => $value){
            $prodIntellectual = new IntellectualProduction();
            // $prodIntellectual -> setUrlCvlac($value['urlCvlac']);
            $prodIntellectual -> setTypeProd($value['typeProd']);
            $prodIntellectual -> setTitleProd($value['titleProd']);
            $prodIntellectual -> setUrlVerification($value['urlVerification']);

            $entityManager=$doctrine->getManager();
            $entityManager->persist($prodIntellectual);
            $entityManager->flush();
        }


        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardada nueva producción intelectual']));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/curriculum-vitae/references', name: 'app_curriculum_vitae_references')]
    public function references(ManagerRegistry $doctrine): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(),true);

        foreach($data as $key => $value){
            $references = new ReferencesData();
            $references -> setNames($value['names']);
            $references -> setRelationship($value['relationship']);
            $references -> setOccupation($value['occupation']);
            $references -> setPhone($value['phone']);

            $entityManager = $doctrine->getManager();
            $entityManager -> persist($references);
            $entityManager -> flush();
        }


        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardados datos referencias personales y laborales']));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/curriculum-vitae/read-cv', name: 'app_curriculum_vitae_read')]
    public function read(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken  ): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $userId = $user->getId();
        $qb = function($class, $id) use ($doctrine) {
            return $doctrine->getRepository($class)->createQueryBuilder('e')->andWhere('e.user = :user')->setParameter('user', $id)->getQuery()->getArrayResult();
        };
        return new JsonResponse([
            'personalData' => $qb(PersonalData::class, $userId),
            'academicTraining' => $qb(AcademicTraining::class, $userId),
            'furtherTraining' => $qb(FurtherTraining::class, $userId),
            'language' => $qb(Language::class, $userId),
            'workExperience' => $qb(WorkExperience::class, $userId),
            'teachingExperience' => $qb(TeachingExperience::class, $userId),
            'intellectualproduction' => $qb(IntellectualProduction::class, $userId),
            'references' => $qb(ReferencesData::class, $userId),
            'records' => $qb(Record::class, $userId)
        ]);
    }
}
