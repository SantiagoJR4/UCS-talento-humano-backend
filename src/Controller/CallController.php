<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use App\Entity\CallPercentage;
use App\Entity\Competence;
use App\Entity\CompetencePercentage;
use App\Entity\CompetenceProfile;
use App\Entity\Factor;
use App\Entity\FactorProfile;
use App\Entity\FurtherTraining;
use App\Entity\IntellectualProduction;
use App\Entity\Language;
use App\Entity\Materias;
use App\Entity\Notification;
use App\Entity\PersonalData;
use App\Entity\Profile;
use App\Entity\Record;
use App\Entity\ReferencesData;
use App\Entity\Score;
use App\Entity\SpecialProfile;
use App\Entity\Subjects;
use App\Entity\Subprofile;
use App\Entity\TblCall;
use App\Entity\TeachingExperience;
use App\Entity\User;
use App\Entity\UsersInCall;
use App\Entity\WorkExperience;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

function convertDateTimeToString2($data) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = convertDateTimeToString2($value);
        } elseif ($value instanceof \DateTime) {
            $data[$key] = $value->format('Y-m-d H:i:s');
        }
    }
    return $data;
} //TODO: Make it global, seems that the name cannot be the same on functions outside classes

function checkProperties($obj) {
    foreach ($obj as $property => $value) {
        if (!$value) {
            return false;
        }
    }
    return true;
}

function approvedUser($userInCall, $step, $allSteps){
    $userInCallStatus[$step] = 1;
    $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
    $currentUserStatus = end($arrayUserStatus);
    $index = array_search($currentUserStatus, $allSteps);
    array_push($arrayUserStatus, $allSteps[$index + 1]);
    $userInCall->setUserStatus(json_encode($arrayUserStatus));
}

function setEmailInfo($nextStep){
    switch ($nextStep) {
        case 'KT':
            return array(
                'title' => 'Citación Prueba de conocimientos',
                'template' => 'email/knowledgeTestCitationEmail.html.twig'
            );
        case 'PT':
            return array(
                'title' => 'Citación Prueba Psicotecnica',
                'template' => 'email/psychoTestCitationEmail.html.twig'
            );
        case 'IN':
            return array(
                'title' => 'Citación Entrevista',
                'template' => 'email/interviewCitationEmail.html.twig'
            );
        default:
            return array(
                'title' => 'Resultado de Convocatoria',
                'template' => 'email/notSelectedForCall.html.twig'
            );
    }
}

function requirementsSignUpToCall($userId, $requiredToSignUp, $doctrine){
    $entities = [
        'personalData' => PersonalData::class,
        'academicTraining' => AcademicTraining::class,
        'furtherTraining' => FurtherTraining::class,
        'language' => Language::class,
        'workExperience' => WorkExperience::class,
        'teachingExperience' => TeachingExperience::class,
        'intellectualproduction' => IntellectualProduction::class,
        'references' => ReferencesData::class,
        'records' => Record::class,
    ];
    $entityCounts = [];
    foreach ($entities as $key => $entity) {
        $entityCounts[$key] = count($doctrine->getRepository($entity)->findBy(['user' => $userId]));
    }
    try 
    {
        $cvlac = $doctrine->getRepository(PersonalData::class)->findOneBy(['user' => $userId])->getUrlCvlac();
        if($cvlac !== NULL)
        {
            $entityCounts['intellectualproduction'] += 1;
        }
    } catch (\Throwable $th)
    {
        //throw $th;
    }
    $requirementsAreMet = [];
    foreach($requiredToSignUp as $key => $value)
    {
        if($value === false || ($value === true && $entityCounts[$key] > 0))
        {
            $requirementsAreMet[$key] = true;
        } else
        {
            $requirementsAreMet[$key] = false;
        }
    }
    $canSignUp = checkProperties($requirementsAreMet);
    return [
            'requirementsAreMet' => $requirementsAreMet,
            'requiredToSignUp' => $requiredToSignUp,
            'entityCounts' => $entityCounts,
            'canSignUp' => $canSignUp
    ];
}

class CallController extends AbstractController
{
    // #[Route('/get-all-factors', name: 'app_get_all_factors')]
    // public function getAllFactors(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    // {
    //     $allFactors = $doctrine->getRepository(Factor::class)->findAll();
    //     $serializerAllFactors = $serializer->serialize($allFactors, 'json');
    //     return new JsonResponse($serializerAllFactors, 200, [], true);
    // }

    #[Route('/get-all-factors', name: 'app_get_all_factors')]
    public function getAllFactors(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $repository = $doctrine->getRepository(Factor::class);
        $query = $repository->createQueryBuilder('f')
            ->setMaxResults($repository->count([]) - 3);
        $factors = $query->getQuery()->getArrayResult();
        // $serializedFactors = $serializer->serialize($factors, 'json');
        
        return new JsonResponse($factors, 200, []);
    }

    #[Route('/get-all-competences', name: 'app_get_all_competences')]
    public function getAllCompetences(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $allCompetences = $doctrine->getRepository(Competence::class)->findAll();
        $serializerAllCompetences = $serializer->serialize($allCompetences, 'json');
        return new JsonResponse($serializerAllCompetences, 200, [], true);
    }

    #[Route('/get-competences-and-factors', name: 'app_get_competences_and_factors')]
    public function getCompetencesAndFactors(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $repository = $doctrine->getRepository(Factor::class);
        $query = $repository->createQueryBuilder('f')
            ->setMaxResults($repository->count([]) - 3)
            ->getQuery();
        $factors = $query->getResult();
        $competences = $doctrine->getRepository(Competence::class)->findAll();
        $competencesSerialized = $serializer->serialize($competences, 'json');
        $competences = json_decode($competencesSerialized, true);
        $factorsSerialized = $serializer->serialize($factors, 'json');
        $factors= json_decode($factorsSerialized, true);
        return new JsonResponse(['competences' => $competences, 'factors' => $factors], 200);
    }

    #[Route('/get-all-profiles', name: 'app_get_all_profiles')]
    public function getAllProfiles(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        // $allProfiles = $doctrine->getRepository(Profile::class)->findAll();
        // $serializerAllProfiles = $serializer->serialize($allProfiles, 'json');
        // return new JsonResponse($serializerAllProfiles, 200, [], true);
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select(
            'p.id', 'p.name', 'p.underGraduateTraining', 'p.postGraduateTraining',
            'p.previousExperience', 'p.furtherTraining', 'p.specialRequirements')
            ->from('App\Entity\Profile', 'p');
        $allProfiles = $query->getQuery()->getArrayResult();
        return new JsonResponse($allProfiles, 200, []);
    }

    #[Route('/get-all-subprofiles', name: 'app_get_all_subprofiles')]
    public function getAllSubprofiles(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $allSubprofiles = $doctrine->getRepository(Subprofile::class)->findAll();
        $serializerAllSubprofiles = $serializer->serialize($allSubprofiles, 'json');
        return new JsonResponse($serializerAllSubprofiles, 200, [], true);
    }

    #[Route('/get-all-materias', name: 'app_get_all_materias')]
    public function getMaterias(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $allMaterias = $doctrine->getRepository(Materias::class)->findAll();
        $allMaterias = $serializer->serialize($allMaterias, 'json');
        return new JsonResponse($allMaterias, 200, [], true);
    }

    #[Route('/get-all-subjects', name: 'app_get_all_subjects')]
    public function getSubjects(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        // $allSubjects = $doctrine->getRepository(Subjects::class)->findAll();
        // $allSubjects = $serializer->serialize($allSubjects, 'json');
        // return new JsonResponse($allSubjects, 200, [], true);
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('s.id', 'sb.id as subprofileId', 'm.id as materiaId', 'm.nombre as name', 'p.id as programaId')
            ->from('App\Entity\Subjects', 's')
            ->join('s.subprofile', 'sb')
            ->join('s.materia', 'm')
            ->join('m.programa', 'p')
            ->orderBy('s.id', 'ASC');
        $allProfiles = $query->getQuery()->getArrayResult();
        return new JsonResponse($allProfiles, 200, []);
    }

    #[Route('/create-new-profile', name: 'app_create_new_profile')]
    public function createNewProfile(ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $data = $request->request->all();
        $newProfile = new Profile();
        $newProfile->setName($data['profileName']);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newProfile);
        $factorsCrest = json_decode($data['factorsCrest'], true);
        $competencesFactorsPercentages = json_decode($data['competencesFactorsPercentages'], true);
        foreach($factorsCrest as $key => $crestValue){
            $factor = $entityManager->getRepository(Factor::class)->find($key + 1);
            $newFactorProfile = new FactorProfile();
            $newFactorProfile->setCrest($crestValue);
            $newFactorProfile->setfactor($factor);
            $newFactorProfile->setProfile($newProfile);
            $entityManager->persist($newFactorProfile);
        }
        foreach($competencesFactorsPercentages as $element) {
            $competence = $entityManager->getRepository(Competence::class)->find($element['competence']);
            $newCompetenceProfile = new CompetenceProfile();
            // $newCompetenceProfile->setPercentage($element['percentage']);
            $newCompetenceProfile->setCompetence($competence);
            $newCompetenceProfile->setProfile($newProfile);
            $entityManager->persist($newCompetenceProfile);
        }
        $entityManager->flush();
        foreach($competencesFactorsPercentages as $item){
            $competence = $entityManager->getRepository(Competence::class)->find($item['competence']);
            $competenceProfile = $entityManager
                ->getRepository(CompetenceProfile::class)
                ->findOneBy(['competence' => $competence, 'profile' => $newProfile ]);
            foreach($item['factorsInCompetence'] as $factorPerCompetence){
                $factor = $entityManager->getRepository(Factor::class)->find($factorPerCompetence);
                $factorProfile = $entityManager
                    ->getRepository(FactorProfile::class)
                    ->findOneBy(['factor' => $factor, 'profile' => $newProfile]);
                $newScore = new Score;
                $newScore->setCompetenceProfile($competenceProfile);
                $newScore->setFactorProfile($factorProfile);
                $entityManager->persist($newScore);
            }
            $entityManager->flush();
        }
        return new JsonResponse(['done' => 'está hecho'],200,[]);
    }

    //TODO: Agregar en jury el campo de jefe Inmediato
    #[Route('/create-new-call', name: 'app_create_new_call')]
    public function createNewCall(ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        // $cth = $doctrine->getRepository(User::class)->findOneBy(['userType' => , 'specialUser' => 'CTH'])
        $specialUsersForJury = ['CTH', 'PSI'];
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('u.id', 'u.names', 'u.lastNames', 'u.specialUser')
            ->from('App\Entity\User', 'u')
            ->where('u.specialUser IN (:specialUsersForJury) AND u.userType != 9')
            ->setParameter('specialUsersForJury', $specialUsersForJury);
        //TODO: arrayfilter just one Camila CTH 
        $jury = $query->getQuery()->getArrayResult();
        foreach ($jury as &$person) {
            $person['fullName'] = $person['names'] . ' ' . $person['lastNames'];
            unset($person['names']);
            unset($person['lastNames']);
        }
        if (!in_array($user->getSpecialUser(), $specialUsersForJury)) {
            array_push($jury, ["id"=>$user->getId(), "fullName"=>$user->getNames()." ".$user->getLastNames(), "specialUser"=> $user->getSpecialUser()]);
        }
        $data = $request->request->all();
        $area = $request->request->get('area');
        $isEdited = json_decode($request->request->get('editedProfile'),true);
        $special = json_decode($request->request->get('special'), true);
        $newCall = new TblCall();
        foreach($data as $fieldName => $fieldValue) {
            $dateTime = '';
            if (property_exists($newCall, $fieldName) && $fieldName !== 'ProfileId') {
                if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $fieldValue) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fieldValue)){
                    $dateTime = new DateTime($fieldValue);
                    $newCall->{'set'.$fieldName}($dateTime);
                }
                else {
                    $newCall->{'set'.$fieldName}($fieldValue);
                }
            }
        }
        $newCall->setJury(json_encode($jury));
        $newCall->setState(0);
date_default_timezone_set('America/Bogota');
        $addToHistory = json_encode(array(array(
            'user' => $user->getId(),
            'responsible' => $user->getSpecialUser(),
            'state' => 0,
            'message' => 'La convocatoria fue creada por '.$user->getNames()." ".$user->getLastNames(),
            'date' => date('Y-m-d H:i:s'),
        )));
        $newCall->setHistory($addToHistory);
        $entityManager = $doctrine->getManager();
        $requiredForPercentages = json_decode($data['requiredForPercentages'], true);
        $stepsOfCall = [];
        $allSteps = [];
        foreach ($requiredForPercentages as $key => $value) {
            if( $key === 'curriculumVitae' && !!$value ){ array_push( $allSteps, 'CV' ); }
            if( $key === 'knowledgeTest' && !!$value ){ array_push( $allSteps, 'KT' ); }
            if( $key === 'psychoTest' && !!$value ){ array_push( $allSteps, 'PT' ); }
            if( $key === 'interview' && !!$value ){ array_push( $allSteps, 'IN' ); }
            if( $key === 'class' && !!$value ){ array_push( $allSteps, 'CL' ); }
        }
        array_push( $allSteps, 'FI', 'SE' );
        $currentStep = $allSteps[0];
        $stepsOfCall = ['allSteps' => $allSteps, 'currentStep' => $currentStep];
        $stepsOfCall = json_encode($stepsOfCall);
        $newCall->setStepsOfCall($stepsOfCall);
        if($isEdited) {
            $newSpecialProfile = new SpecialProfile();
            $newSpecialProfile->setUnderGraduateTraining($special['specialUnderGraduate']);
            $newSpecialProfile->setPostGraduateTraining($special['specialpostGraduate']);
            $newSpecialProfile->setPreviousExperience($special['specialPreviousExperience']);
            $newSpecialProfile->setFurtherTraining($special['specialfurtherTraining']);
            $entityManager->persist($newSpecialProfile);
            $entityManager->flush();
            $newCall->setSpecialProfile($newSpecialProfile);
        }
        if($area === 'TH') {
            $profile = $doctrine->getRepository(Profile::class)->find($data['profileId']);
            $newCall->setProfile($profile);
        } else {
            $profile = $doctrine->getRepository(Profile::class)->findOneBy(['name' => 'Docentes o profesores']);
            $subprofile = $doctrine->getRepository(Subprofile::class)->find($data['profileId']);
            $newCall->setSubprofile($subprofile);
            $newCall->setProfile($profile);
        }
        $entityManager->persist($newCall);
        $entityManager->flush();
        $newNotification = new Notification();
        $newNotification->setSeen(0);
        $userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF','userType' => 1]);
        $newNotification->setUser($userForNotification);
        $newNotification->setMessage('solicita la aprobación de una convocatoria.');
        $relatedEntity = array(
            'id'=>$newCall->getId(),
            'applicant'=>$user->getNames()." ".$user->getLastNames(),
            'entity'=>'call'
        );
        $newNotification->setRelatedEntity(json_encode($relatedEntity));
        $entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'call has been created.'],200,[]);
    }

    #[Route('/get-call', name: 'app_get_call')]
    public function getCall(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $callId = $request->query->get('callId');
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('c', 'p', 'subp', 'spec')
            ->from('App\Entity\TblCall', 'c')
            ->where('c.id = :id')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('c.subprofile', 'subp')
            ->leftJoin('c.specialProfile', 'spec')
            ->setParameter('id', $callId);
        $call = $query->getQuery()->getArrayResult();
        $call = $call[0];
        $call = convertDateTimeToString2($call);
        $call['requiredForPercentages'] = json_decode($call['requiredForPercentages'], true);
        $call['requiredForCurriculumVitae'] = json_decode($call['requiredForCurriculumVitae'], true);
        $call['requiredToSignUp'] = json_decode($call['requiredToSignUp'], true);
        $call['stepsOfCall'] = json_decode($call['stepsOfCall'], true);
        $call['salary'] = json_decode($call['salary'], true);
        $call['history'] = json_decode($call['history'], true);
        return new JsonResponse($call, 200,[]);
    }

    #[Route('/get-calls', name: 'app_get_active_calls')]
    public function getActiveCalls(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $states = json_decode($request->request->get('states'));
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('c', 'p', 'subp', 'spec')
            ->from('App\Entity\TblCall', 'c')
            ->where('c.state IN (:states)')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('c.subprofile', 'subp')
            ->leftJoin('c.specialProfile', 'spec')
            ->setParameter('states', $states); // TODO: Change this, when needed
            // $querysp = $doctrine->getManager()->createQueryBuilder();
            // $querysp->select('c', 'sp')
            // ->from('App\Entity\TblCall', 'c')
            // ->where('c.state = :state')
            // ->leftJoin('c.subprofile', 'sp')
            // ->setParameter('state', 0); // TODO: Change this, when needed
            // $queryspec = $doctrine->getManager()->createQueryBuilder();
            // $queryspec->select('c', 'spe')
            //     ->from('App\Entity\TblCall', 'c')
            //     ->where('c.state = :state')
            //     ->join('c.specialProfile', 'spe')
            //     ->setParameter('state', 0); // TODO: Change this, when needed
        $profileActiveCalls = $query->getQuery()->getArrayResult();
        // $subprofileActiveCalls = $querysp->getQuery()->getArrayResult();
        // $specialProfileActiveCalls = $queryspec->getQuery()->getArrayResult();
        $allActiveCalls = array_merge($profileActiveCalls);
        $response = convertDateTimeToString2($allActiveCalls);
        return new JsonResponse( $response, 200, [] );
    }

    #[Route('/last-call-number', name: 'app_last-call-number')]
    public function lastCallNumber(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $lastCallNumber = $doctrine->getRepository(TblCall::class)->findOneBy([], ['name' => 'desc']);
        try {
            $name = $lastCallNumber->getName() + 1;
        } catch (\Throwable $th) {
            $name = 1;
        }
        return new JsonResponse($name, 200, [], true);
    }

    #[Route('/test-length', name: 'app_test-length')]
    public function testLength(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request, MailerInterface $mailer): JsonResponse
    {
        $userId = 6;
        $entities = [
            'personalData' => PersonalData::class,
            'academicTraining' => AcademicTraining::class,
            'furtherTraining' => FurtherTraining::class,
            'workExperience' => WorkExperience::class,
            'teachingExperience' => TeachingExperience::class,
            'intellectualProduction' => IntellectualProduction::class,
            'referencesData' => ReferencesData::class,
            'record' => Record::class,
            'language' => Language::class,
        ];
        $entityCounts = [];
        foreach ($entities as $key => $entity) {
            $entityCounts[$key] = count($doctrine->getRepository($entity)->findBy(['user' => $userId]));
        }
        try 
        {
            $cvlac = $doctrine->getRepository(PersonalData::class)->findOneBy(['user' => $userId])->getUrlCvlac();
            if($cvlac !== NULL)
            {
                $entityCounts['intellectualProduction'] += 1;
            }
        } catch (\Throwable $th)
        {
            //throw $th;
        }
        return new JsonResponse($entityCounts, 200, []);
    }

    #[Route('/review-sign-up', name: 'app_review_sign_up')]
    public function reviewSignUp(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $callId = $request->request->get('callId');
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $deadline = $call->getReceptionDeadlineDate()->format('Y-m-d');
        date_default_timezone_set('America/Bogota');
        if($deadline < date('Y-m-d') )
        {
            return new JsonResponse(['deadline' => true], 200, []);
        }
        $userId = $user->getId();
        $requiredToSignUp = json_decode($call->getRequiredToSignUp(), true);
        return new JsonResponse( requirementsSignUpToCall($userId , $requiredToSignUp, $doctrine), 200, []);
    }

    #[Route('/sign-up-to-call', name: 'app_sign_up_to_call')]
    public function signUpToCall(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request, MailerInterface $mailer): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $callId = $request->request->get('callId');
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $deadline = $call->getReceptionDeadlineDate()->format('Y-m-d');
        date_default_timezone_set('America/Bogota');
        if($deadline < date('Y-m-d') )
        {
            return new JsonResponse(['deadline' => true], 200, []);
        }
        $userId = $user->getId();
        $requiredToSignUp = json_decode($call->getRequiredToSignUp(), true);
        $canSignUp = requirementsSignUpToCall($userId , $requiredToSignUp, $doctrine)['canSignUp'];
        if ($canSignUp)
        {
            $newUsersInCall = new UsersInCall();
            $newUsersInCall -> setUser($user);
            $newUsersInCall -> setCall($call);
            $newUsersInCall -> setUserStatus(json_encode(['CV']));
            $newUsersInCall -> setStateUserCall(1);
            $newUsersInCall ->setStatus(json_encode(array(
                "CVSTATUS" => 0,
                "KTSTATUS" => 0,
                "PTSTATUS" => 0,
                "INSTATUS" => 0,
                "CLSTATUS" => 0
            )));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($newUsersInCall);
            $entityManager->flush();

            try{
                $email = (new TemplatedEmail())
                    ->from('pasante.santiago@unicatolicadelsur.edu.co') //correo oficina oasic
                    ->to('talento.humano@unicatolicadelsur.edu.co') //correo talento humano
                    ->subject('Usuario en proceso de inscripción')
                    ->htmlTemplate('email/callUserEmail.html.twig')
                    ->context([
                        'call' => $call,
                        'userInCall' => $newUsersInCall,
                        'user' => $user
                    ]);
                $mailer->send($email);
                $message = 'Correo exitoso!!';
            } catch (\Throwable $th){
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error', 'message'=>$message]);
            }

            return new JsonResponse(['message' => 'Usuario se ha inscrito'], 200, []);
        }
    }
    #[Route('/update-sign-up-to-call',name:'app_update_sign_up_to_call')]
    public function updateSignUpToCall(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $data = json_decode($request->getContent(),true);

        $userId = $data['userId'];
        $tblCallID = $data['tblCall'];
        $entityManager = $doctrine->getManager();
        $tblCall = $entityManager->getRepository(TblCall::class)->find($tblCallID);
        $user=$entityManager->getRepository(User::class)->find($userId);

        $signUpCall = $entityManager->getRepository(UsersInCall::class)->find($data['id']);

        $signUpCall -> setStateUserCall(1);
        $signUpCall -> setUser($user);
        $signUpCall -> setCall($tblCall);

        $entityManager->persist($signUpCall);
        $entityManager->flush();
        
        return new JsonResponse(['status'=>'Success','code'=>'200']);
    }

    #[Route('/registered-calls-by-user', name: 'app_registered_calls_by_user')]
    public function registeredCallsByUser(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('c.id', 'u.id as userId', 'cl.id as callId')
            ->from('App\Entity\UsersInCall', 'c')
            ->join('c.user', 'u')
            ->join('c.call', 'cl')
            ->where('c.user = :user')
            ->setParameter('user', $user);
        $allRegisteredCallsByUser = $query->getQuery()->getArrayResult();
        return new JsonResponse($allRegisteredCallsByUser, 200, []);
    }

    #[Route('/get-profile-and-competencies-profile', name: 'app_get_profile_and_competencies_profile')]
    public function getProfileAndCompetenciesProfile(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $callId = $request->query->get('callId');
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $profile = $call->getProfile();
        $subprofile = $call->getSubprofile();
        $specialProfile = $call->getSpecialProfile();
        $requiredForPercentages = $call->getRequiredForPercentages();
        $requiredForCurriculumVitae = $call->getRequiredForCurriculumVitae();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('cp.id', 'c.id as competenceId', 'c.name', 'c.description', 'c.icon')
        // $query->select('cp.id', 'c.id as competenceId')
            ->from('App\Entity\CompetenceProfile', 'cp')
            ->join('cp.competence', 'c')
            ->where('cp.profile = :profile')
            ->setParameter('profile', $profile);
        $competenciesProfile = $query->getQuery()->getArrayResult();
        settype($callId, 'integer');
        $profile = $serializer->serialize($profile, 'json');
        $profile = json_decode($profile, true);
        $subprofile = $serializer->serialize($subprofile, 'json');
        $subprofile = json_decode($subprofile, true);
        $specialProfile = $serializer->serialize($specialProfile, 'json');
        $specialProfile = json_decode($specialProfile, true);
        $requiredForPercentages = json_decode($requiredForPercentages, true);
        $requiredForCurriculumVitae = json_decode($requiredForCurriculumVitae, true);
        //-----------------------------------------
        $anotherquery = $doctrine->getManager()->createQueryBuilder();
        $anotherquery->select('comp.id', 'comp.name', 'comp.icon', 'comp.description')
            ->from('App\Entity\Competence', 'comp');
        $allCompetencies = $anotherquery->getQuery()->getArrayResult();
        $notIncludedCompetencies = array_filter($allCompetencies, function($element) use ($competenciesProfile) {
            foreach ($competenciesProfile as $item) {
                if ($item['competenceId'] === $element['id']) {
                    return false;
                }
            }
            return true;
        }); 
        
        return new JsonResponse(
            [
                'call' => $callId,
                'profile' => $profile,
                'subprofile' => $subprofile,
                'specialProfile' => $specialProfile,
                'requiredForPercentages' => $requiredForPercentages,
                'requiredForCurriculumVitae' => $requiredForCurriculumVitae,
                'competenciesProfile' => $competenciesProfile,
                'notIncludedCompetencies' => $notIncludedCompetencies
            ]
            , 200, []);
    }

    #[Route('/assign-percentages-to-call', name: 'app_assign_percentages_to_call')]
    public function assignPercentagesToCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $jwtKey = 'Un1c4t0l1c4'; //TODO: move this to .env
        $token = $request->query->get('token');
        try {
            $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return new JsonResponse(false);
        }
        $expirationTime = $decodedToken->exp;
        $userType = $decodedToken->userType;
        $isTokenValid = (new DateTime())->getTimestamp() < $expirationTime;
        if( $userType !== 8 || !$isTokenValid)
        {
            return new JsonResponse(['isValid' => $isTokenValid], 200, []);
        }
        $callPercentage = json_decode($request->request->get('callPercentage'), true);
        $hvPercentage = json_decode($request->request->get('hvPercentage'), true);
        $competenciesPercentage = json_decode($request->request->get('competenciesPercentage'), true);
        $factorsValues = json_decode($request->request->get('factorsValues'),true);
        $scoreHV = $request->request->get('score');
        $callId = $request->request->get('callId');
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        //_______________________________________
        $entityManager = $doctrine->getManager();
        $newCallPercentage = new CallPercentage();
        try {
            foreach($callPercentage as $fieldName => $fieldValue)
        {
            $newCallPercentage->{'set'.$fieldValue['name']}($fieldValue['value'] !== 0 ? $fieldValue['value'] : NULL);
        }
        } catch (\Throwable $th) {
            return new JsonResponse('callPercentage');
        }
        try {
            // TODO:  see what happens when hvPercentage === NULL
            foreach($hvPercentage as $fieldName => $fieldValue)
        {
            $newCallPercentage->{'set'.$fieldValue['name']}($fieldValue['value'] !== 0 ? $fieldValue['value'] : NULL);
        }
        } catch (\Throwable $th) {
            return new JsonResponse('hvPercentage');
        }
        $newCallPercentage->setHvScore($scoreHV);
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $newCallPercentage->setCall($call);
        $entityManager->persist($newCallPercentage);
        $entityManager->flush();
        if( $factorsValues !== NULL )
        {
            try {
                foreach($factorsValues as $key => $crestValue)
            {
                $factor = $entityManager->getRepository(Factor::class)->find($key + 1);
                $newFactorProfile = new FactorProfile();
                $newFactorProfile->setCrest($crestValue);
                $newFactorProfile->setfactor($factor);
                $newFactorProfile->setCall($call);
                $entityManager->persist($newFactorProfile);
            }
            $entityManager->flush();
            } catch (\Throwable $th) {
                return new JsonResponse('factorsValues');
            }
        }
        try {
            foreach($competenciesPercentage as $fieldName => $fieldValue)
            {
            $newCompetencePercentage = new CompetencePercentage();
            if($fieldValue['id'] !== NULL){
                $competenceProfile = $entityManager->getRepository(CompetenceProfile::class)->find($fieldValue['id']);
                $newCompetencePercentage->setCompetenceProfile($competenceProfile);
            } else {
                $newCompetencePercentage->setExtraCompetence($fieldValue['extraCompetence']);
            }
            $newCompetencePercentage->setCall($call);
            $newCompetencePercentage->setPsychoPercentage($fieldValue['valuePsycho'] !== 0 ? $fieldValue['valuePsycho'] : NULL);
            $newCompetencePercentage->setInterviewPercentage($fieldValue['valueInterview'] !== 0 ? $fieldValue['valueInterview'] : NULL);
            $entityManager->persist($newCompetencePercentage);
            $entityManager->flush();
            if( $factorsValues !== NULL ){
                foreach($fieldValue['factorsInCompetence'] as $index => $factorId)
                {
                    $factorCall = $entityManager
                        ->getRepository(FactorProfile::class)
                        ->findOneBy(['factor' => $factorId, 'call' => $call]);
                    $newScore = new Score();
                    $newScore->setCompetencePercentage($newCompetencePercentage);
                    $newScore->setFactorProfile($factorCall);
                    $entityManager->persist($newScore);
                }
            }
        }
        } catch (\Throwable $th) {
            return new JsonResponse('competenciesPercentage');
        }
        $entityManager->flush();
        return new JsonResponse(['done'=>'hecho'], 200, []);
    }

    #[Route('/get-users-from-call', name: 'app_get_users_from_call')]
    public function getUsersFromCall(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        
        $callId = $request->query->get('callId');
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $specialUser =  $user->getSpecialUser();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select(
            'uc.id', 'uc.userStatus', 'u.id as userId', 'u.names', 'u.lastNames',
            'u.identification', 'u.email', 'u.urlPhoto', 'uc.qualifyCv', 'uc.status',
            'uc.hvRating','uc.knowledgeRating', 'uc.psychoRating','uc.interviewRating',
            'uc.classRating', 'uc.finalRating', 'u.phone', 'uc.knowledgeTestFile',
            'uc.psychoTestFile', 'uc.psychoTestReport','uc.interviewFile')
            ->from('App\Entity\UsersInCall', 'uc')
            ->join('uc.user', 'u')
            ->where('uc.call = :callId')
            ->setParameter('callId', $callId);
        $usersInCall = $query->getQuery()->getArrayResult();
        foreach ($usersInCall as &$value) {
            $value['status'] = json_decode($value['status'], true);
            $value['userStatus'] = json_decode($value['userStatus'], true);
            $value['hvRating'] = json_decode($value['hvRating'], true);
            $value['qualifyCv'] = json_decode($value['qualifyCv'], true);
            $value['names'] = $value['names'] . ' ' . $value['lastNames'];
            unset($value['lastNames']);
        }
        switch ($specialUser) {
            case 'CTH':
                return new JsonResponse($usersInCall, 200, []);
            case 'PSI':
                $usersInCall = array_filter($usersInCall, function($item){
                    return array_search('PT', $item['userStatus']);
                });
                return new JsonResponse($usersInCall, 200, []);
            default:
                return new JsonResponse(['message'=>'No tienes autorización'], 200, []);
        }
    }

    #[Route('/test-email-call', name: 'app_test_email_call')]
    public function testEmailCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        //Inicio Token
        $jwtKey = 'Un1c4t0l1c4'; //TODO: move this to .env
        $token = $request->query->get('token');
        try {
            $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return new JsonResponse(false);
        }
        $expirationTime = $decodedToken->exp;
        $userType = $decodedToken->userType;
        $specialUser = $decodedToken->specialUser;
        $isTokenValid = (new DateTime())->getTimestamp() < $expirationTime;
        if( $userType !== 8 || !$isTokenValid || $specialUser !== 'CTH')
        {
            return new JsonResponse(['isValid' => $isTokenValid], 403, []);
        }
        //Final Token
        $qualifyCV = $request->request->get('qualifyCV');
        $askAgain = json_decode($request->request->get('askAgain'), true);
        $user = json_decode($request->request->get('user'), true);
        $entityManager = $doctrine->getManager();
        $userInCall = $entityManager->getRepository(UsersInCall::class)->find($user['id']);
        $userInCall->setQualifyCv($qualifyCV);
        $userInCallStatus = json_decode($userInCall->getStatus(), true);
        $userInCallStatus['CVSTATUS'] = $askAgain === NULL ? 3 : 4; 
        $userInCall->setStatus(json_encode($userInCallStatus));
        $entityManager->flush();

        if( $askAgain !== NULL ){
            $qb = function($class, $ids) use ($doctrine) {
                return $doctrine->getRepository($class)
                    ->createQueryBuilder('e')
                    ->andWhere('e.id IN (:ids)')
                    ->setParameter('ids', $ids)
                    ->getQuery()
                    ->getArrayResult();
            };
            
            $dataForEmail = [
                'personalData' => $qb(PersonalData::class, array_column($askAgain['personalData'], 'id')),
                'academicTraining' => $qb(AcademicTraining::class, array_column($askAgain['academicTraining'], 'id')),
                'furtherTraining' => $qb(FurtherTraining::class, array_column($askAgain['furtherTraining'], 'id')),
                'language' => $qb(Language::class, array_column($askAgain['language'], 'id')),
                'workExperience' => $qb(WorkExperience::class, array_column($askAgain['workExperience'], 'id')),
                'teachingExperience' => $qb(TeachingExperience::class, array_column($askAgain['teachingExperience'], 'id')),
                'intellectualproduction' => $qb(IntellectualProduction::class, array_column($askAgain['intellectualproduction'], 'id')),
                'references' => $qb(ReferencesData::class, array_column($askAgain['references'], 'id')),
                'records' => $qb(Record::class, array_column($askAgain['records'], 'id')),
            ];
            foreach ($askAgain as $key => $value) {
                foreach ($dataForEmail[$key] as $index => $element) {
                    $dataForEmail[$key][$index]['textReview'] = $value[$index]['textReview'];
                }
            }
    
            try{
                $email = (new TemplatedEmail())
                    ->from('convocatorias@unicatolicadelsur.edu.co')
                    ->to($user['email'])
                    ->subject('Revisión')
                    ->htmlTemplate('email/askAgainCallEmail.html.twig')
                    ->context([
                        'user' => $user,
                        'dataForEmail' => $dataForEmail
                    ]);         
                $mailer->send($email);
                $message = 'La revisión fue enviada con éxito';
            } catch (\Throwable $th) {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error','message'=>$message]);
            }
        }

        return new JsonResponse(['data'=>'hecho'], 200, []);
    }

    #[Route('/presentation-of-knowledge-test', name:'app_presentation_of_knowledge_test')]
    public function presentationOfKnowledgeTest(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        $identification = $request->request->get('identification');
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['identification' => $identification]);
        $userEmail = $user->getEmail();
        $fullname = $user->getNames().' '.$user->getlastNames();
        try{
            $email = (new TemplatedEmail())
                ->from('convocatorias@unicatolicadelsur.edu.co')
                ->to($userEmail)
                ->subject('Citación Prueba de Conocimiento')
                ->htmlTemplate('email/approvedHVCallEmail.html.twig')
                ->context([
                    'fullname' => $fullname,
                ]);         
            $mailer->send($email);
            $message = 'La revisión fue enviada con éxito';
        } catch (\Throwable $th) {
            $message = 'Error al enviar el correo:'.$th->getMessage();
            return new JsonResponse(['status'=>'Error','message'=>$message]);
        }
        return new JsonResponse(['status'=>'Correo Enviado'],200,[]);
    }

    #[Route('/results-cv', name:'app_results_cv')]
    public function resultsCv(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode( $request->request->get('data'), true);
        $callId = $request->request->get('callId');
        $callName = $request->request->get('callName');
        $date = $request->request->get('date');
        $hour = $request->request->get('hour');
        $step =  $request->request->get('step');
        
        $entityManager = $doctrine->getManager();
        $call = $entityManager->getRepository(TblCall::class)->find($callId);
        $callSteps = json_decode($call->getStepsOfCall(), true);
        $allSteps = $callSteps['allSteps'];
        $currentStep = $callSteps['currentStep'];
        foreach($data as $key => $value)
        {
            $user = $entityManager->getRepository(User::class)->find($value['id']);
            $userInCall = $entityManager->getRepository(UsersInCall::class)->findOneBy(['user' => $value['id'], 'call' => $callId]);
            $userInCallStatus = json_decode($userInCall->getStatus(), true);
            if($value['approved'])
            {
                $userInCallStatus[$step] = 1;
                $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
                $currentUserStatus = end($arrayUserStatus);
                $index = array_search($currentUserStatus, $allSteps);
                array_push($arrayUserStatus, $allSteps[$index + 1]);
                $userInCall->setUserStatus(json_encode($arrayUserStatus));
            }
            else
            {
                $userInCallStatus[$step] = 2;
            }
            $userInCall->setStatus(json_encode($userInCallStatus));
            $entityManager->flush();
            $userEmail = $user->getEmail();
            $fullname = $user->getNames().' '.$user->getlastNames();
            try{
                $email = (new TemplatedEmail())
                    ->from('convocatorias@unicatolicadelsur.edu.co')
                    ->to($userEmail)
                    ->subject($value['approved'] ? 'Citación Prueba de conocimientos' : 'Resultado de Convocatoria')
                    ->htmlTemplate($value['approved'] ? 'email/approvedHVCallEmail.html.twig' : 'email/notSelectedForCall.html.twig')
                    ->context([
                        'fullname' => $fullname,
                        'callName' => $callName,
                        'date' => $date,
                        'hour' => $hour,
                    ]);         
                $mailer->send($email);
                $message = 'El correo fue enviado con éxito';
            } catch (\Throwable $th) {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error','message'=>$message]);
            }
        }
        $index = array_search($currentStep, $allSteps);
        $nextStep = $allSteps[$index + 1];
        $stepsOfCall = json_encode(['allSteps' => $allSteps, 'currentStep' => $nextStep]);
        $call->setStepsOfCall($stepsOfCall);
        $entityManager->flush();
        return new JsonResponse(['status'=>'Correo Enviado'],200,[]);
    }

    #[Route('/results-call', name:'app_results_call')]
    public function resultsCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        // ------------
        $data = json_decode( $request->request->get('data'), true);
        $callId = $request->request->get('callId');
        $callName = $request->request->get('callName');
        $date = $request->request->get('date');
        $hour = $request->request->get('hour');
        $step = $request->request->get('step');
        $entityManager = $doctrine->getManager();
        $call = $entityManager->getRepository(TblCall::class)->find($callId);
        $callPercentage = $entityManager->getRepository(CallPercentage::class)->findOneBy(['call'=>$callId]);
        $callSteps = json_decode($call->getStepsOfCall(), true);
        $allSteps = $callSteps['allSteps'];
        $currentStep = $callSteps['currentStep'];
        $index = array_search($currentStep, $allSteps);
        $nextStep = $allSteps[$index + 1];
        $stepsOfCall = json_encode(['allSteps' => $allSteps, 'currentStep' => $nextStep]);
        $call->setStepsOfCall($stepsOfCall);
        // $entityManager->flush();
        foreach($data as $key => $value)
        {
            $user = $entityManager->getRepository(User::class)->find($value['id']);
            $userInCall = $entityManager->getRepository(UsersInCall::class)->findOneBy(['user' => $value['id'], 'call' => $callId]);
            $userInCallStatus = json_decode($userInCall->getStatus(), true);
            $emailInfo =  setEmailInfo('NO');
            switch ($step) {
                case 'CVSTATUS':
                    if($value['approved']) {
                        $userInCallStatus[$step] = 1;
                        $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
                        $currentUserStatus = end($arrayUserStatus);
                        $index = array_search($currentUserStatus, $allSteps);
                        array_push($arrayUserStatus, $allSteps[$index + 1]);
                        $userInCall->setUserStatus(json_encode($arrayUserStatus));
                        $emailInfo = setEmailInfo($nextStep);
                    } else {
                        $userInCallStatus[$step] = 2;
                    }
                    break;
                case 'KTSTATUS':
                    $knowledgeTestMinimumScore = $request->request->get('knowledgeTestMinimumScore');
                    $call->setKnowledgeTestMinimumScore($knowledgeTestMinimumScore); 
                    if($value['knowledgeRating'] >= $knowledgeTestMinimumScore){
                        $userInCallStatus[$step] = 1;
                        $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
                        $currentUserStatus = end($arrayUserStatus);
                        $index = array_search($currentUserStatus, $allSteps);
                        array_push($arrayUserStatus, $allSteps[$index + 1]);
                        $userInCall->setUserStatus(json_encode($arrayUserStatus));
                        $emailInfo = setEmailInfo($nextStep);
                    } else {
                        $userInCallStatus[$step] = 2;
                    }
                    $userInCall->setKnowledgeRating(number_format($value['knowledgeRating'], 3));
                    $fileKT = $request->files->get('knowledgeTestFile'.$value['id']);
                    if ( $fileKT instanceof UploadedFile){
                        $directory = $this->getParameter('hv')
                        . '/'
                        . $user->getTypeIdentification()
                        . $user->getIdentification();
                        if (!is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }
                        $fileName =
                        'Call' . '-'
                        . 'knowledgeTestFile' . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $fileKT->guessExtension();
                        $fileKT->move( $directory, $fileName);
                        $fileKT = $user->getTypeIdentification().$user->getIdentification().'/'.$fileName;
                        $userInCall->setKnowledgeTestFile($fileKT);
                    }
                    break;
                case 'PTSTATUS':
                    if($value['psychoRating'] >= 3){ //TODO may act like knowledgeTestMinimumScore
                        $userInCallStatus[$step] = 1;
                        $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
                        $currentUserStatus = end($arrayUserStatus);
                        $index = array_search($currentUserStatus, $allSteps);
                        array_push($arrayUserStatus, $allSteps[$index + 1]);
                        $userInCall->setUserStatus(json_encode($arrayUserStatus));
                        $emailInfo = setEmailInfo($nextStep);
                    } else {
                        $userInCallStatus[$step] = 2;
                    }
                    $userInCall->setpsychoRating(number_format($value['psychoRating'],3));
                    $filePT = $request->files->get('psychoTestFile'.$value['id']);
                    $reportPT = $request->files->get('psychoTestReport'.$value['id']);
                    if ( $filePT instanceof UploadedFile){
                        $directory = $this->getParameter('hv')
                        . '/'
                        . $user->getTypeIdentification()
                        . $user->getIdentification();
                        if (!is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }
                        $fileName =
                        'Call' . '-'
                        . 'psychoTestFile' . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $filePT->guessExtension();
                        $filePT->move( $directory, $fileName);
                        $filePT = $user->getTypeIdentification().$user->getIdentification().'/'.$fileName;
                        $userInCall->setPsychoTestFile($filePT);
                    }
                    if ( $reportPT instanceof UploadedFile){
                        $directory = $this->getParameter('hv')
                        . '/'
                        . $user->getTypeIdentification()
                        . $user->getIdentification();
                        if (!is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }
                        $fileName =
                        'Call' . '-'
                        . 'psychoTestReport' . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $reportPT->guessExtension();
                        $reportPT->move( $directory, $fileName);
                        $reportPT = $user->getTypeIdentification().$user->getIdentification().'/'.$fileName;
                        $userInCall->setPsychoTestReport($reportPT);
                    }
                    break;
                case 'INSTATUS':
                    if($value['interviewRating'] >= 3){ //TODO may act like knowledgeTestMinimumScore
                        $userInCallStatus[$step] = 1;
                        $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
                        $currentUserStatus = end($arrayUserStatus);
                        $index = array_search($currentUserStatus, $allSteps);
                        array_push($arrayUserStatus, $allSteps[$index + 1]);
                        $userInCall->setUserStatus(json_encode($arrayUserStatus));
                        $emailInfo = setEmailInfo($nextStep);
                    } else {
                        $userInCallStatus[$step] = 2;
                    }
                    $userInCall->setInterviewRating(number_format($value['interviewRating'], 3));
                    $fileIN = $request->files->get('interviewFile'.$value['id']);
                    if ( $fileIN instanceof UploadedFile){
                        $directory = $this->getParameter('hv')
                        . '/'
                        . $user->getTypeIdentification()
                        . $user->getIdentification();
                        if (!is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }
                        $fileName =
                        'Call' . '-'
                        . 'interviewFile' . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $fileIN->guessExtension();
                        $fileIN->move( $directory, $fileName);
                        $fileIN = $user->getTypeIdentification().$user->getIdentification().'/'.$fileName;
                        $userInCall->setInterviewFile($fileIN);
                    }
                    break;
                case 'CLSTATUS':
                    if($value['classRating'] >= 3){ //TODO may act like knowledgeTestMinimumScore
                        $userInCallStatus[$step] = 1;
                        $arrayUserStatus = json_decode($userInCall->getUserStatus(), true);
                        $currentUserStatus = end($arrayUserStatus);
                        $index = array_search($currentUserStatus, $allSteps);
                        array_push($arrayUserStatus, $allSteps[$index + 1]);
                        $userInCall->setUserStatus(json_encode($arrayUserStatus));
                        $emailInfo = setEmailInfo($nextStep);
                    } else {
                        $userInCallStatus[$step] = 2;
                    }
                    $userInCall->setClassRating(number_format($value['classRating'],3));
                    break;
                default:
                    break;
            }
            $userInCall->setStatus(json_encode($userInCallStatus));
            $entityManager->flush();
            $userEmail = $user->getEmail();
            $fullname = $user->getNames().' '.$user->getlastNames();
            if ($nextStep !== 'FI'){
                try {
                    $email = (new TemplatedEmail())
                        ->from('convocatorias@unicatolicadelsur.edu.co')
                        ->to($userEmail)
                        ->subject($emailInfo['title'])
                        ->htmlTemplate($emailInfo['template'])
                        ->context([
                            'fullname' => $fullname,
                            'callName' => $callName,
                            'date' => $date,
                            'hour' => $hour,
                        ]);         
                    $mailer->send($email);
                    $message = 'El correo fue enviado con éxito';
                } catch (\Throwable $th) {
                    $message = 'Error al enviar el correo:'.$th->getMessage();
                    // return new JsonResponse(['status'=>'Error','message'=>$message]);
                }
            }
            if(end($arrayUserStatus) === 'FI') {
                
                $hvRating = json_decode($userInCall->getHvRating(), true);
                $weigthedCV = $hvRating['total'] ? $hvRating['total']*$callPercentage->getCurriculumVitae()/100 : 0;
                $weigthedKT = $userInCall->getKnowledgeRating()*$callPercentage->getKnowledgeTest()/100;
                $weigthedPT = $userInCall->getPsychoRating()*$callPercentage->getpsychoTest()/100;
                $weigthedIN = $userInCall->getInterviewRating()*$callPercentage->getInterview()/100;
                $weigthedCL = $userInCall->getClassRating()*$callPercentage->getClass()/100;
                $finalRating = $weigthedCV + $weigthedKT + $weigthedPT + $weigthedIN + $weigthedCL;
                $userInCall->setFinalRating(number_format($finalRating, 3));
            }
        }
        $entityManager->flush();
        return new JsonResponse(['status' => 'Correo Enviado'], 200, []);
    }

    #[Route('/not-selected-for-call', name:'app_not_selected_for_call')]
    public function notSelectedForCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        $allIdentifications = json_decode( $request->request->get('allIdentifications'), true);
        $entityManager = $doctrine->getManager();
        foreach($allIdentifications as $key => $currentIdentification)
        {
            $user = $entityManager->getRepository(User::class)->findOneBy(['identification' => $currentIdentification]);
            $userEmail = $user->getEmail();
            $fullname = $user->getNames().' '.$user->getlastNames();
            try{
                $email = (new TemplatedEmail())
                    ->from('convocatorias@unicatolicadelsur.edu.co')
                    ->to($userEmail)
                    ->subject('Resultado de Convocatoria')
                    ->htmlTemplate('email/notSelectedForCall.html.twig')
                    ->context([
                        'fullname' => $fullname,
                    ]);         
                $mailer->send($email);
                $message = 'El correo fue enviado con éxito';
            } catch (\Throwable $th) {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error','message'=>$message]);
        }
        }
        return new JsonResponse(['status'=>'Correos Enviados'],200,[]);
    }

    #[Route('/recordatory-for-class', name:'app_recordatory_for_class')]
    public function recordatoryForClass(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        $allIdentifications = json_decode( $request->request->get('allIdentifications'), true);
        $allStartTimes = json_decode($request->request->get('allStartTimes'), true);
        $date = $request->request->get('date');
        $subject = $request->request->get('subject');
        $entityManager = $doctrine->getManager();
        foreach($allIdentifications as $key => $currentIdentification)
        {
            $user = $entityManager->getRepository(User::class)->findOneBy(['identification' => $currentIdentification]);
            $userEmail = $user->getEmail();
            $fullname = $user->getNames().' '.$user->getlastNames();
            $currentStartTime = $allStartTimes[$key];
            try{
                $email = (new TemplatedEmail())
                    ->from('convocatorias@unicatolicadelsur.edu.co')
                    ->to($userEmail)
                    ->subject('Información Adicional Entrevista')
                    ->htmlTemplate('email/recordatoryClass.html.twig')
                    ->context([
                        'fullname' => $fullname,
                        'startTime' => $currentStartTime,
                        'date' => $date,
                        'subject' => $subject
                    ]);         
                $mailer->send($email);
                $message = 'La revisión fue enviada con éxito';
            } catch (\Throwable $th) {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error','message'=>$message]);
        }
        }
        return new JsonResponse(['status'=>'Correos Enviados'],200,[]);
    }

    #[Route('/selected-for-call', name:'app_selected_for_call')]
    public function selectedForCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, MailerInterface $mailer): JsonResponse
    {
        // TODO: DO IT WELL!
        // $allIdentifications = json_decode( $request->request->get('allIdentifications'), true);
        // $allStartTimes = json_decode($request->request->get('allStartTimes'), true);
        // $date = $request->request->get('date');
        // $subject = $request->request->get('subject');
        // $entityManager = $doctrine->getManager();
        // foreach($allIdentifications as $key => $currentIdentification)
        // {
        //     $user = $entityManager->getRepository(User::class)->findOneBy(['identification' => $currentIdentification]);
        //     $userEmail = $user->getEmail();
        //     $fullname = $user->getNames().' '.$user->getlastNames();
        //     $currentStartTime = $allStartTimes[$key];
            try{
                $email = (new TemplatedEmail())
                    ->from('convocatorias@unicatolicadelsur.edu.co')
                    ->to('mariacarolinaguerrero10.05@gmail.com')
                    ->subject('¡Felicitaciones!')
                    ->htmlTemplate('email/selectedForCall.html.twig')
                    ->context([
                        // 'fullname' => $fullname,
                        // 'startTime' => $currentStartTime,
                        // 'date' => $date,
                        // 'subject' => $subject
                    ]);         
                $mailer->send($email);
                $message = 'La revisión fue enviada con éxito';
            } catch (\Throwable $th) {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error','message'=>$message]);
        }
        // }
        return new JsonResponse(['status'=>'Correos Enviados'],200,[]);
    }

    #[Route('/get-state-user-call', name:'app_get_state_user_call')]
    public function getStateUserCall(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken) : JsonResponse 
    {
        $token= $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);

        if($token === false){
            return new JsonResponse(['error' => 'Token no válido']);
        }
        else{
            //Trabajador seleccionado
            if (!$user) {
                throw $this->createNotFoundException(
                    'No user found for id '     
                );
            }
        }
        $usersInCall = $doctrine->getRepository(UsersInCall::class)->findBy(['user'=>$user]);
        $call = $doctrine->getRepository(TblCall::class);

        if(empty($usersInCall)){
            return new JsonResponse(['status'=>false,'message' => 'El usuario no está inscrito a ninguna convocatoria']);
        }

        foreach($usersInCall as $usersInCall){
            $callId = $usersInCall ->getCall()->getId();
            $call = $doctrine ->getRepository(TblCall::class)->find($callId);
            $response[] = [
                'id' => $usersInCall->getId(),
                'call' => $call->getName(),
                'stateUserCall' => $usersInCall -> isStateUserCall()

            ];
        }
        return new JsonResponse($response);
    }

    #[Route('/get-call-percentages', name: 'app_get_call_percentages')]
    public function getCallPercentages(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $callId = $request->query->get('callId');
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('cp.id','cp.curriculumVitae','cp.knowledgeTest','cp.psychoTest',
        'cp.interview','cp.class','cp.underGraduateTraining','cp.postGraduateTraining',
        'cp.previousExperience','cp.furtherTraining','cp.hvScore')
            ->from('App\Entity\CallPercentage', 'cp')
            ->where('cp.call = :callId')
            ->setParameter('callId', $callId);
        $callPercentages = $query->getQuery()->getArrayResult();
        $query->select('comp.id','comp.psychoPercentage','comp.interviewPercentage','comppercomp.name',
        'comp.extraCompetence')
            ->from('App\Entity\CompetencePercentage', 'comp')
            ->leftJoin('comp.competenceProfile', 'compper')
            ->leftJoin('compper.competence','comppercomp')
            ->where('comp.call = :callId')
            ->setParameter('callId', $callId);
        $competencePercentages = $query->getQuery()->getArrayResult();
        // TODO: Aqui va el score -> aplicable a la prueba psicotecnica.
        return new JsonResponse(['callPercentages' => $callPercentages, 'competencePercentages' => $competencePercentages], 200, []);
    }

    #[Route('/set-hv-rating', name: 'app_set_hv_rating')]
    public function settingHvRating(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $userInCallId = $request->request->get('userInCallId');
        $hvRating = $request->request->get('hvRating');
        $usersInCall = $doctrine->getRepository(UsersInCall::class)->find($userInCallId);
        $usersInCall->setHvRating($hvRating);
        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        return new JsonResponse(['done' => 'hecho'], 200, []);
    }

    #[Route('/get-posible-users-for-jury', name: 'app_posible_users_for_jury')]
    public function posibleUsersForJury(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer): JsonResponse
    {
        //TODO: Add token
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('u.id', 'u.names', 'u.lastNames')
            ->from('App\Entity\User', 'u')
            ->where('u.userType IN (:userType)')
            ->setParameter('userType', [1,2,8]);
        $array = $query->getQuery()->getArrayResult();
        $usersForJury = array();
        foreach ($array as $person) {
            $fullName = $person["names"] . " " . $person["lastNames"];
            $usersForJury[] = array("id" => $person["id"], "fullName" => $fullName);
        }
        return new JsonResponse($usersForJury, 200, []);
    }

    #[Route('/reject-call', name: 'app_reject_call')]
    public function rejectCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, ValidateToken $vToken): JsonResponse
    {
        $token= $request->query->get('token');
        $callId = $request->query->get('callId');
        $rejectText = $request->request->get('rejectText');
        $user = $vToken->getUserIdFromToken($token);
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        if($call === NULL)
        {
            return new JsonResponse(['message' => 'No existe esta convocatoria.'], 400, []);
        }
        $call->setState(5);
        $history = $call->getHistory();
        date_default_timezone_set('America/Bogota');
        $addToHistory = json_encode(array(
            'user' => $user->getId(),
            'responsible' => $user->getSpecialUser(),
            'state' => 5,
            'message' => 'La convocatoria fue rechazada por '.$user->getNames()." ".$user->getLastNames(),
            'userInput' => $rejectText,
            'date' => date('Y-m-d H:i:s'),
        ));
        $newHistory= rtrim($history, ']').','.$addToHistory.']';
        $call->setHistory($newHistory);
        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha rechazado esta convocatoria con el id '.$callId], 200, []);
    }
    #[Route('/approve-call', name: 'app_approve_call')]
    public function approveCall(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $token= $request->query->get('token');
        $callId = $request->query->get('callId');
        $notificationId = $request->query->get('notificationId');
        $applicant = $request->query->get('applicant');
        $user = $vToken->getUserIdFromToken($token);
        $specialUser = $user->getSpecialUser();
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        if($call === NULL) {
            return new JsonResponse(['message' => 'No existe esta convocatoria.'], 400, []);
        }
        $newStateForCall = 0;
        $newNotification = new Notification();
        $newNotification->setSeen(0);
        $relatedEntity = array(
            'id'=>$callId,
            'applicant'=>$applicant,
            'entity'=>'call'
        );
        $newNotification->setRelatedEntity(json_encode($relatedEntity));
        switch ($specialUser) {
            case 'VF':
                $newStateForCall = 1;
                $userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'REC','userType' => 1]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('solicita la aprobación de una convocatoria.');
                break;
            case 'REC':
                $newStateForCall = 2;
                $userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('solicita la aprobación de una convocatoria.');
                break;
            case 'CTH':
                $newStateForCall = 3;
                break;
            
            default:
            return new JsonResponse(['message' => 'Usuario no autorizado.'], 403, []);
        }
        $notification = $doctrine->getRepository(Notification::class)->find($notificationId);
        $notification->setSeen(1);
        $call->setState($newStateForCall);
        $history = $call->getHistory();
        date_default_timezone_set('America/Bogota');
        $addToHistory = json_encode(array(
            'user' => $user->getId(),
            'responsible' => $user->getSpecialUser(),
            'state' => $newStateForCall,
            'message' => 'La convocatoria fue aprobada por '.$user->getNames()." ".$user->getLastNames(),
            'date' => date('Y-m-d H:i:s'),
        ));
        $newHistory= rtrim($history, ']').','.$addToHistory.']';
        $call->setHistory($newHistory);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha aprobado esta convocatoria con el id '.$callId], 200, []);
    }

    #[Route('/get-references-from-candidate', name: 'app_get_references_from_candidate')]
    public function getReferencesFromCandidate(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $userId = $request->query->get('userId');
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('r.typeReferences', 'r.names', 'r.relationship', 'r.occupation', 'r.phone')
            ->from('App\Entity\ReferencesData', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $userId);
        $array = $query->getQuery()->getArrayResult();
        return new JsonResponse($array, 200, []);
    }

    #[Route('/select-candidate', name: 'app_select_candidate')]
    public function selectCandidate(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $userId = $request->query->get('userId');
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('r.typeReferences', 'r.names', 'r.relationship', 'r.occupation', 'r.phone')
            ->from('App\Entity\ReferencesData', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $userId);
        $array = $query->getQuery()->getArrayResult();
        return new JsonResponse($array, 200, []);
    }

}
