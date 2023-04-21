<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Entity\CompetenceProfile;
use App\Entity\Factor;
use App\Entity\FactorProfile;
use App\Entity\Profile;
use App\Entity\Score;
use App\Entity\TblCall;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
            ->setMaxResults($repository->count([]) - 3)
            ->getQuery();
        $factors = $query->getResult();
        $serializedFactors = $serializer->serialize($factors, 'json');
        
        return new JsonResponse($serializedFactors, 200, [], true);
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
        $factorsSerialized = $serializer->serialize($factors, 'json');
        $competences = json_decode($competencesSerialized, true);
        $factors= json_decode($factorsSerialized, true);
        return new JsonResponse(['competences' => $competences, 'factors' => $factors], 200);
    }

    #[Route('/get-all-profiles', name: 'app_get_all_profiles')]
    public function getAllProfiles(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $allProfiles = $doctrine->getRepository(Profile::class)->findAll();
        $serializerAllProfiles = $serializer->serialize($allProfiles, 'json');
        return new JsonResponse($serializerAllProfiles, 200, [], true);
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
        $profile = $doctrine->getRepository(Profile::class)->find($data['profileId']);
        $newCall->setProfile($profile);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newCall);
        $entityManager->flush();
        return new JsonResponse($data,200,[]);
    }

    #[Route('/last-call-number', name: 'app_last-call-number')]
    public function lastCallNumber(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $lastCallNumber = $doctrine->getRepository(TblCall::class)->findOneBy([], ['name' => 'desc']);
        $name = $lastCallNumber->getName() + 1;
        // $lastCallNumber = $serializer->serialize($lastCallNumber, 'json');
        return new JsonResponse($name, 200, [], true);
    }

}
