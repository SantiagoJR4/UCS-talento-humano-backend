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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/create-new-call', name: 'app_create_new_call')]
    public function createNewCall(ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $data = $request->request->all();
        $area = $request->request->get('area');
        $isEdited = $request->request->get('editedProfile');
        $sUnder = $request->request->get('specialUnderGraduate');
        $sPost = $request->request->get('specialpostGraduate');
        $sExp = $request->request->get('specialPreviousExperience');
        $sFurt = $request->request->get('specialfurtherTraining');
        $newCall = new TblCall();
        // var_dump($data);
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
        $newCall->setState(0);
        $entityManager = $doctrine->getManager();
        if($area === 'TH') {
            if($isEdited) {
                $newSpecialProfile = new SpecialProfile();
                $newSpecialProfile->setUnderGraduateTraining($sUnder);
                $newSpecialProfile->setPostGraduateTraining($sPost);
                $newSpecialProfile->setPreviousExperience($sExp);
                $newSpecialProfile->setFurtherTraining($sFurt);
                $entityManager->persist($newSpecialProfile);
                $entityManager->flush();
                $newCall->setSpecialProfile($newSpecialProfile);
            }
            $profile = $doctrine->getRepository(Profile::class)->find($data['profileId']);
            $newCall->setProfile($profile);
        } else {
            $subprofile = $doctrine->getRepository(Subprofile::class)->find($data['profileId']);
            $newCall->setSubprofile($subprofile);
        }
        $entityManager->persist($newCall);
        $entityManager->flush();
        return new JsonResponse($data,200,[]);
    }

    #[Route('/get-active-calls', name: 'app_get_active_calls')]
    public function getActiveCalls(ManagerRegistry $doctrine): JsonResponse
    {
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('c', 'p', 'subp', 'spec')
            ->from('App\Entity\TblCall', 'c')
            ->where('c.state = :state')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('c.subprofile', 'subp')
            ->leftJoin('c.specialProfile', 'spec')
            ->setParameter('state', 0); // TODO: Change this, when needed
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

    #[Route('/sign-up-to-call', name: 'app_sign_up_to_call')]
    public function signUpToCall(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $callId = $request->request->get('callId');
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $newUsersInCall = new UsersInCall();
        $newUsersInCall -> setUser($user);
        $newUsersInCall -> setCall($call);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newUsersInCall);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Usuario se ha inscrito'], 200, []);
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
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('cp.id', 'c.id as competenceId', 'c.name', 'c.description', 'c.icon')
        // $query->select('cp.id', 'c.id as competenceId')
            ->from('App\Entity\CompetenceProfile', 'cp')
            ->join('cp.competence', 'c')
            ->where('cp.profile = :profile')
            ->setParameter('profile', $profile);
        $competenciesProfile = $query->getQuery()->getArrayResult();
        foreach ($competenciesProfile as &$element) {
            $element['factorsInCompetence'] = [];
        }
        settype($callId, 'integer');
        $profile = $serializer->serialize($profile, 'json');
        $profile = json_decode($profile, true);
        
        return new JsonResponse(
            [
                'call' => $callId,
                'profile' => $profile,
                'competenciesProfile' => $competenciesProfile
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
        if( $userType !== 1 || !$isTokenValid)
        {
            return new JsonResponse(['isValid' => $isTokenValid], 200, []);
        }
        $callPercentage = json_decode($request->request->get('callPercentage'), true);
        $hvPercentage = json_decode($request->request->get('hvPercentage'), true);
        $competenciesPercentage = json_decode($request->request->get('competenciesPercentage'), true);
        $factorsValues = json_decode($request->request->get('factorsValues'),true);
        $callId = $request->request->get('callId');
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $entityManager = $doctrine->getManager();
        $newCallPercentage = new CallPercentage();
        try {
            foreach($callPercentage as $fieldName => $fieldValue)
        {
            $newCallPercentage->{'set'.$fieldValue['name']}($fieldValue['value']);
        }
        } catch (\Throwable $th) {
            return new JsonResponse('callPercentage');
        }
        try {
            foreach($hvPercentage as $fieldName => $fieldValue)
        {
            $newCallPercentage->{'set'.$fieldValue['name']}($fieldValue['value']);
        }
        } catch (\Throwable $th) {
            return new JsonResponse('hvPercentage');
        }
        $call = $doctrine->getRepository(TblCall::class)->find($callId);
        $newCallPercentage->setCall($call);
        $entityManager->persist($newCallPercentage);
        $entityManager->flush();
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
        try {
            foreach($competenciesPercentage as $fieldName => $fieldValue)
        {
            $competenceProfile = $entityManager->getRepository(CompetenceProfile::class)->find($fieldValue['id']);
            $newCompetencePercentage = new CompetencePercentage();
            $newCompetencePercentage->setCall($call);
            $newCompetencePercentage->setCompetenceProfile($competenceProfile);
            $newCompetencePercentage->setPsychoPercentage($fieldValue['valuePsycho'] !== 0 ? $fieldValue['valuePsycho'] : NULL);
            $newCompetencePercentage->setInterviewPercentage($fieldValue['valueInterview'] !== 0 ? $fieldValue['valueInterview'] : NULL);
            $entityManager->persist($newCompetencePercentage);
            $entityManager->flush();
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
        } catch (\Throwable $th) {
            return new JsonResponse('competenciesPercentage');
        }
        $entityManager->flush();
        return new JsonResponse($competenciesPercentage, 200, []);
    }

    #[Route('/get-users-from-call', name: 'app_get_users_from_call')]
    public function getUsersFromCall(ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $callId = $request->query->get('callId');
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select(
            'uc.id', 'uc.userStatus', 'u.id as userId', 'u.names', 'u.lastNames',
            'u.identification', 'u.email', 'u.urlPhoto', 'uc.qualifyCv', 'uc.cvStatus')
            ->from('App\Entity\UsersInCall', 'uc')
            ->join('uc.user', 'u')
            ->where('uc.call = :callId')
            ->setParameter('callId', $callId);
        $usersInCall = $query->getQuery()->getArrayResult();
        return new JsonResponse($usersInCall, 200, []);
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
        if( $userType !== 1 || !$isTokenValid || $specialUser !== 'CTH')
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
        $userInCall->setCvStatus(1);
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
                    ->from('santipo12@gmail.com')
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

}
