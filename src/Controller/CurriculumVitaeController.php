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

function convertDateTimeToString($data) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = convertDateTimeToString($value);
        } elseif ($value instanceof \DateTime) {
            $data[$key] = $value->format('Y-m-d H:i:s');
        }
    }
    return $data;
}

class CurriculumVitaeController extends AbstractController
{
    #[Route('/curriculum-vitae/personal-data', name:'app_curriculum_vitae_personal-data')]
    public function personalData(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): Response
    {
        $formData = $request->request->all();
        $identificationFile = $request->files->get('identificationPdfFile');
        $epsFile = $request->files->get('epsPdfFile');
        $pensionFile = $request->files->get('pensionPdfFile');
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $formValues =[];
        foreach($formData as $key => $value) {$formValues[$key] = json_decode($value);}
        $personalData = new PersonalData();
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
        $personalData -> setUser($user);

        $identificationPath='';
        $epsPath = '';
        $pensionPath = '';

        if($identificationFile && $epsFile && $pensionFile instanceof UploadedFile){
            $identificationName = $formValues['identificationPdfName'].time().'.'.$identificationFile->guessExtension();
            $identificationFile->move(
                $this->getParameter('uploads_directory'),
                $identificationName
            );
            $identificationPath = $this->getParameter('uploads_directory').'/'.$identificationName;
            $personalData -> setIdentificationPdf($identificationPath);

            $epsName = $formValues['epsPdfName'].time().'.'.$epsFile->guessExtension();
            $epsFile->move(
                $this->getParameter('uploads_directory'),
                $epsName
            );
            $epsPath = $this->getParameter('uploads_directory').'/'.$epsName;
            $personalData -> setEpsPdf($epsPath);

            $pensionName = $formValues['pensionPdfName'].time().'.'.$pensionFile->guessExtension();
            $pensionFile->move(
                $this->getParameter('uploads_directory'),
                $pensionName
            );
            $pensionPath = $this->getParameter('uploads_directory').'/'.$pensionName;
            $personalData -> setPensionPdf($pensionPath);
            
        }
        $entityManager = $doctrine->getManager();
        $entityManager->persist($personalData);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['identificationPdf' => $identificationPath, 'epsPdf' => $epsPath, 'pensionPdf' => $pensionPath]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/curriculum-vitae/academic-training', name: 'app_curriculum_vitae_academic_training')]
    public function academicTraining(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): Response
    {
        $formData = $request->request->all();
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
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
        $academicTraining->setUser($user);
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

    #[Route('/curriculum-vitae/language', name:'app_curriculum_vitae_language')]
    public function languages(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): Response
    {
        $formData = $request->request->all();
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);

        $languagePdf = $request->files->get('languagePdfFile');
        $formValues = [];
        foreach($formData as $key => $value) { $formValues[$key] = json_decode($value); }
        $language = new Language();
        $language->setNameLanguage($formValues['language']);
        $language->setToSpeak($formValues['speak']);
        $language->setToRead($formValues['read']);
        $language->setToWrite($formValues['write']);
        $language->setIsCertified($formValues['isCertified']);
        $language->setLevellanguage($formValues['levelLanguage']);
        $language -> setUser($user);

        $languagePath = '';
        if($languagePdf instanceof UploadedFile){
            $newFileName = $formValues['languagePdfName'].time().'.'.$languagePdf->guessExtension();
            $languagePdf->move(
                $this->getParameter('uploads_directory'),
                $newFileName
            );
            $languagePath = $this->getParameter('uploads_directory').'/'.$newFileName;
            $language->setCertifiedPdf($languagePath);
        }

        $entityManager=$doctrine->getManager();
        $entityManager->persist($language);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['certified   Pdf' => $languagePath]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
    
    #[Route('/curriculum-vitae/teaching-experience', name: 'app_curriculum_vitae_teaching_experience')]
    public function teachingExperience(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): Response
    {
        $formData = $request->request->all();
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $certifiedFile = $request->files->get('certifiedPdfFile');
        $formValues = [];
        foreach( $formData as $key => $value ) { $formValues[$key] = json_decode($value); }
        $teachingExperience = new TeachingExperience();
        $teachingExperience -> setIsforeignuniversity($formValues['isForeignUniversity']);
        $teachingExperience -> setSnies($formValues['snies']);
        $teachingExperience -> setNameuniversity($formValues['nameUniversity']);
        $teachingExperience -> setFaculty($formValues['faculty']);
        $teachingExperience -> setProgram($formValues['program']);
        $teachingExperience -> setAdmissionDate(new DateTime($formValues['admissionDate']));
        $teachingExperience -> setIsactive($formValues['isActive']);
        $teachingExperience -> setContractmodality($formValues['contractModality']);
        $teachingExperience -> setCourseload(json_encode($formValues['courseLoad']));
        $teachingExperience -> setCertifiedpdf($formValues['certifiedPdf']);
        $teachingExperience->setUser($user);
        
        if(isSet($formValues['retirementDate'])){
            $teachingExperience -> setRetirementdate(new DateTime($formValues['retirementDate']));
        }
        else{
            $teachingExperience -> setRetirementdate(NULL);
        }
        if ($certifiedFile instanceof UploadedFile) {
            $newFileName = $formValues['certifiedPdfName'].time().'.'.$certifiedFile->guessExtension();
            $certifiedFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFileName
                );
            $certifiedPath = $this->getParameter('uploads_directory').'/'.$newFileName;
            $teachingExperience->setCertifiedPdf($certifiedPath);
        }
        $entityManager=$doctrine->getManager();
        $entityManager->persist($teachingExperience);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['respuesta' => 'Guardada nueva experiencia docente']));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    #[Route('/curriculum-vitae/work-experience', name: 'app_curriculum_vitae_work_experience')]
    public function workExperience(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): Response
    {
        $formData = $request->request->all();
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $certifiedFile = $request->files->get('certifiedPdfFile');
        $formValues = [];
        foreach( $formData as $key => $value ) { $formValues[$key] = json_decode($value); }
        $workExperience = new WorkExperience();
        $workExperience -> setCompanyname($formValues['companyName']);
        $workExperience -> setPosition($formValues['position']);
        $workExperience -> setDependence($formValues['dependence']);
        $workExperience -> setDepartment($formValues['department']);
        $workExperience -> setMunicipality($formValues['municipality']);
        $workExperience -> setCompanyaddress($formValues['companyAddress']);
        $workExperience -> setBossname($formValues['bossName']);
        $workExperience -> setPhone($formValues['phone']);
        $workExperience -> setAdmissiondate(new DateTime($formValues['admissionDate']));
        $workExperience -> setIsworking($formValues['isWorking']);
        $workExperience -> setCertifiedpdf($formValues['certifiedPdf']);
        $workExperience -> setUser($user);
        if(isSet($formValues['retirementDate'])){
            $workExperience -> setRetirementdate(new DateTime($formValues['retirementDate']));
        }
        else{
            $workExperience -> setRetirementdate(NULL);
        }
        if ($certifiedFile instanceof UploadedFile) {
            $newFileName = $formValues['certifiedPdfName'].time().'.'.$certifiedFile->guessExtension();
            $certifiedFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFileName
                );
            $certifiedPath = $this->getParameter('uploads_directory').'/'.$newFileName;
            $workExperience->setCertifiedPdf($certifiedPath);
        }
        $entityManager=$doctrine->getManager();
        $entityManager->persist($workExperience);
        $entityManager->flush();

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
            $references -> setTypereferences($value['typeReferences']);
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

    #[Route('/curriculum-vitae/record', name:'app_curriculum_vitae_record')]
    public function record(ManagerRegistry $doctrine, Request $request): Response
    {
        $taxRecordFile = $request->files->get('taxRecordPdfFile');
        $judicialRecordFile = $request->files->get('judicialRecordFile');
        $disciplinaryRecordFile = $request->files->get('disciplinaryRecordFile');
        $correctiveMeasuresFile = $request->files->get('correctiveMeasuresFile');

        $formValues = [];
        $record = new Record();

        $user = $doctrine -> getRepository(User::class)->find($request->get('sub'));
        $record -> setUser($user);

        $taxRecordPath ='';
        $judicialRecordPath ='';
        $disciplinaryRecordPath = '';
        $correctiveMeasuresPath = '';
        
        if($taxRecordFile && $judicialRecordFile && $disciplinaryRecordFile && $correctiveMeasuresFile instanceof UploadedFile){
            $newFileNameTax = $formValues['taxRecordPdfName'].time().'.'.$taxRecordFile->guessExtension();
            $taxRecordFile->move(
                $this->getParameter('uploads_directory'),
                $newFileNameTax
            );
            $taxRecordPath = $this->getParameter('uploads_directory').'/'.$newFileNameTax;
            $record->setTaxrecordPdf($taxRecordPath);


            $newFileNameJudicial = $formValues['taxRecordPdfName'].time().'.'.$judicialRecordFile->guessExtension();
            $judicialRecordFile->move(
                $this->getParameter('uploads_directory'),
                $newFileNameJudicial
            );
            $judicialRecordPath = $this->getParameter('uploads_directory').'/'.$newFileNameJudicial;
            $record->setJudicialrecordPdf($judicialRecordPath);

            $newFileNameDisciplinary = $formValues['taxRecordPdfName'].time().'.'.$disciplinaryRecordFile->guessExtension();
            $disciplinaryRecordFile->move(
                $this->getParameter('uploads_directory'),
                $newFileNameDisciplinary
            );
            $disciplinaryRecordPath = $this->getParameter('uploads_directory').'/'.$newFileNameDisciplinary;
            $record->setDisciplinaryrecordPdf($disciplinaryRecordPath);

            $newFileNameCorrective = $formValues['taxRecordPdfName'].time().'.'.$correctiveMeasuresFile->guessExtension();
            $correctiveMeasuresFile->move(
                $this->getParameter('uploads_directory'),
                $newFileNameCorrective
            );
            $correctiveMeasuresPath = $this->getParameter('uploads_directory').'/'.$newFileNameCorrective;
            $record->setCorrectivemeasuresPdf($correctiveMeasuresPath);

        }
        $entityManager = $doctrine->getManager();
        $entityManager->persist($record);
        $entityManager->flush();

        $response=new Response();
        $response->setContent(json_encode(['taxRecordPdf' => $taxRecordPath,'judicialRecordPdf' => $judicialRecordPath, 'disciplinaryRecordPdf' => $disciplinaryRecordPath, 'correctiveMeasuresPdf' => $correctiveMeasuresPath]));
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
            'personalData' => convertDateTimeToString($qb(PersonalData::class, $userId)),
            'academicTraining' => convertDateTimeToString($qb(AcademicTraining::class, $userId)),
            'furtherTraining' => convertDateTimeToString($qb(FurtherTraining::class, $userId)),
            'language' => convertDateTimeToString($qb(Language::class, $userId)),
            'workExperience' => convertDateTimeToString($qb(WorkExperience::class, $userId)),
            'teachingExperience' => convertDateTimeToString($qb(TeachingExperience::class, $userId)),
            'intellectualproduction' => convertDateTimeToString($qb(IntellectualProduction::class, $userId)),
            'references' => convertDateTimeToString($qb(ReferencesData::class, $userId)),
            'records' => convertDateTimeToString($qb(Record::class, $userId))
        ]);
    }

    #[Route('/curriculum-vitae/delete-cv', name: 'app_curriculum_vitae_delete')]
    public function delete(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $table = ucfirst($request->query->get('table'));
        $id = $request->query->get('id');
        $user =  $vToken->getUserIdFromToken($token);
        if(isSet($user)){
            $entityManager = $doctrine->getManager();
            $objectToDelete = $entityManager->getRepository('App\\Entity\\'.$table)->find($id);
            $entityManager->remove($objectToDelete);
            $entityManager->flush();
            return new JsonResponse(['response'=>'deleted '.$table.' with ID '.$id]);
        }
    }

    #[Route('/curriculum-vitae/update-cv', name: 'app_curriculum_vitae_update')]
    public function update(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $id = $request->query->get('id');
        $entity = 'App\\Entity\\'.ucFirst($request->query->get('entity'));
        $entityManager = $doctrine->getManager();
        $entityObj = $entityManager->getRepository($entity)->find($id);
        if (!$entityObj) {
            throw $this->createNotFoundException('Entity not found');
        }
        $fieldsToUpdate = $request->request->all();
        $files = $request->files->all();
        $fieldsToUpdate = array_merge($fieldsToUpdate, $files);
        foreach ($fieldsToUpdate as $fieldName => $fieldValue) {
            $dateTime = '';
            if (property_exists($entity, $fieldName)) {
                try {
                    if($fieldName !== 'snies'){
                        $dateTime = new DateTime($fieldValue);
                    }
                } catch (\Exception $e) {}
                if ($dateTime instanceof DateTime) {
                    $entityObj->{'set' . $fieldName}($dateTime);
                } elseif ($fieldValue instanceof UploadedFile) {
                    $fileName = 
                        ucfirst($request->query->get('entity')).'-'
                        .$fieldName.'-'
                        .$user->getTypeIdentification()
                        .$user->getIdentification().'-'
                        .time().'.'
                        .$fieldValue->guessExtension();
                    $fieldValue->move($this->getParameter('uploads_directory'), $fileName);
                    $fieldValue = $fileName;
                    $entityObj->{'set'.$fieldName}($fieldValue);
                } elseif($fieldValue === 'false') {
                    $entityObj->{'set'.$fieldName}(false);
                } else {
                    $entityObj->{'set'.$fieldName}($fieldValue);
                }
            }
        }
        $entityManager->persist($entityObj);
        $entityManager->flush();
        return new JsonResponse(['token' => $token, 'entity' => $entity, 'id'=> $id, 'ftU' =>$user->getIdentification()]);
    }

    #[Route('/curriculum-vitae/create-cv', name: 'app_curriculum_vitae_create')]
    public function create(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $entity = 'App\\Entity\\'.ucFirst($request->query->get('entity'));
        $test = new $entity;
        $user =  $vToken->getUserIdFromToken($token);
        $fieldsToUpdate = $request->request->all();
        $files = $request->files->all();
        $fieldsToUpdate = array_merge($fieldsToUpdate, $files);
        foreach ($fieldsToUpdate as $fieldName => $fieldValue) {
            $dateTime = '';
            if (property_exists($entity, $fieldName)) {
                try {
                    if($fieldName !== ('snies')){
                        $dateTime = new DateTime($fieldValue);
                    }
                } catch (\Exception $e) {}
                if ($dateTime instanceof DateTime) {
                    $test->{'set' . $fieldName}($dateTime);
                } elseif ($fieldValue instanceof UploadedFile) {
                    $fileName =
                        ucfirst($request->query->get('entity')) . '-'
                        . $fieldName . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $fieldValue->guessExtension();
                    $fieldValue->move($this->getParameter('uploads_directory'), $fileName);
                    $fieldValue = $fileName;
                    $test->{'set' . $fieldName}($fieldValue);
                } elseif($fieldValue === 'false') {
                    $test->{'set'.$fieldName}(false);
                } else {
                    $test->{'set'.$fieldName}($fieldValue);
                }
            }
        }
        $test->setUser($user);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($test);
        $entityManager->flush();
        return new JsonResponse(['status' => 'Success', 'code' => '200', 'message' => 'Nuevo Objeto Creado']);
    }
    
}