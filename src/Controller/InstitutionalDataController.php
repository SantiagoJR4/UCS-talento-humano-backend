<?php

namespace App\Controller;

use App\Entity\ContractCharges;
use App\Entity\Profile;
use App\Service\ValidateToken;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InstitutionalDataController extends AbstractController
{
    #[Route('/institutionalData/create-contract-charges', name: 'app_institutionalData_create_contract_charges')]
    public function createContractCharges(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $entityManager = $doctrine->getManager();
        $data = $request->request->all();

        $userLogueado = $vToken->getUserIdFromToken($token);

        if($token === false){
            return new JsonResponse(['ERROR' => 'Token no válido']);
        }else{
            $contractCharges = new ContractCharges();
            $contractCharges -> setTypeEmployee($data['typeEmployee']);
            $contractCharges -> setName($data['name']);
            $contractCharges -> setWorkDedication($data['workDedication']);
            $contractCharges -> setSalary($data['salary']);

            $entityManager->persist($contractCharges);
            $entityManager->flush();    

            $message = 'Cargo nuevo creado correctamente';
        }

        return new JsonResponse(['status'=>'Success','code'=>200,'message'=>$message]);
    }

    #[Route('institutionalData/update-contract-charges', name:'app_institutionalData_update_contract_charges')]
    public function updateContractCharges(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken):JsonResponse
    {
        $token = $request->query->get('token');
        $entityManager = $doctrine->getManager();
        $data = $request->request->all();

        if($token === false){
            return new JsonResponse(['ERROR' => 'Token no válido']);
        }else{
            $contractCharge = $entityManager->getRepository(ContractCharges::class)->find($data['id']);
    
            if(!$contractCharge){
                throw $this->createNotFoundException(
                    'No contractCharge found for id'.$data['id']
                );
            }
    
            $contractCharge -> setTypeEmployee($data['typeEmployee']);
            $contractCharge -> setName($data['name']);
            $contractCharge -> setWorkDedication($data['workDedication']);
            $contractCharge -> setSalary($data['salary']);

            $entityManager->persist($contractCharge);
            $entityManager->flush();
    
            $message = 'Cargo actualizado correctamente';
        }

        return new JsonResponse(['status'=>'Success','code'=>200,'message'=>$message]);
        
    }

    #[Route('institutionalData/get-AllContractCharges', name:'app_institutionalData_get_all_contract_charges')]
    public function getAllContractCharges(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');

        if($token === false){
            return new JsonResponse(['ERROR' => 'Token no válido']);
        }else{
            $contractChargeData = [];
            $allContractCharges = $doctrine->getRepository(ContractCharges::class)->findAll();
            if(empty($allContractCharges)){
                return new JsonResponse(['status'=>false, 'message'=>'No se encontraron cargos']);
            }
            foreach($allContractCharges as $contractCharge){
                $contractChargeData[] = [
                    'id' => $contractCharge->getId(),
                    'typeEmployee' => $contractCharge->getTypeEmployee(),
                    'name' => $contractCharge->getName(),
                    'workDedication' => $contractCharge->getWorkDedication(),
                    'salary' => $contractCharge->getSalary()
                ];
            }
        }
        return new JsonResponse(['status'=>true, 'allContractCharges'=>$contractChargeData]);
    }

    #[Route('institutionalData/delete-contractCharge/{id}', name:'app_institutionalData_delete_contract_charge')]
    public function deleteContraactCharge(ManagerRegistry $doctrine, Request $request, int $id) : JsonResponse
    {
        $token = $request->query->get('token');
        $entityManager = $doctrine->getManager();
        if($token === false){
            return new JsonResponse(['ERROR'=>'Token no válido']);
        }else{
            $contractCharge = $entityManager->getRepository(ContractCharges::class)->find($id);
            if(!$contractCharge){
                throw $this->createNotFoundException(
                    'No Contract Charge found for id'.$id['id']
                );
            }
            $entityManager->remove($contractCharge);
            $entityManager->flush();
        }

        return new JsonResponse(['status'=>'Success','Code'=>'200', 'message' => 'Cargo eliminado correctamente']);
    }

    //----------------------------------------------------------------------------------------
    //-------------------- CRUD PROFILES UNICATOLICA

    #[Route('institutionalData/create-profile', name:'app_institutionalData_crate_profile')]
    public function createProfile(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $token = $request->query->get('token');
        $entityManager = $doctrine->getManager();
        $data = $request->request->all();

        if($token === false){
            return new JsonResponse(['ERROR' => 'Token no válido']);
        }else{
            $profile = new Profile();
            $profile -> setName($data['name']);
            $profile -> setArea('TH');
            $profile -> setCharge($data['charge']);
            $profile -> setUnderGraduateTraining($data['underGraduateTraining']);
            $profile -> setPostGraduateTraining($data['postGraduateTraining']);
            $profile -> setPreviousExperience($data['previousExperience']);
            $profile -> setFurtherTraining($data['furtherTraining']);
            $profile -> setSpecialRequirements($data['specialRequirements']);
            $profile -> setFunctions($data['functions']);

            $entityManager->persist($profile);
            $entityManager->flush();

            $message = 'Perfil nuevo creado correctamente';
        }

        return new JsonResponse(['status'=>'Success','code'=>200,'message'=>$message]);
    }

    #[Route('institutionalData/update-profile', name:'app_institutionalData_update_profile')]
    public function updateProfile(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $entityManager = $doctrine->getManager();
        $data = $request->request->all();

        if($token === false){
            return new JsonResponse(['ERROR'=>'Token no válido']);
        }else{
            $profile = $entityManager->getRepository(Profile::class)->find($data['id']);

            if(!$profile){
                throw $this->createNotFoundException(
                    'No profile found for id'.$data['id']
                );
            }

            $profile -> setName($data['name']);
            $profile -> setArea('TH');
            $profile -> setCharge($data['charge']);
            $profile -> setUnderGraduateTraining($data['underGraduateTraining']);
            $profile -> setPostGraduateTraining($data['postGraduateTraining']);
            $profile -> setPreviousExperience($data['previousExperience']);
            $profile -> setFurtherTraining($data['furtherTraining']);
            $profile -> setSpecialRequirements($data['specialRequirements']);
            $profile -> setFunctions($data['functions']);

            $entityManager->persist($profile);
            $entityManager->flush();

            $message = 'Perfil actualizado correctamente';
        }
        return new JsonResponse(['status'=>'Success','code'=>200,'message'=>$message]);
    }

    #[Route('institutionalData/get-all-profiles', name: 'app_institutionalData_get_all_profiles')]
    public function getAllProfiles(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $token = $request->query->get('token');

        if($token === false){
            return new JsonResponse(['ERROR' => 'Token no válido']);
        }else{
            $query = $doctrine->getManager()->createQueryBuilder();
            $query->select(
                'p.id', 'p.name', 'p.area', 'p.charge','p.underGraduateTraining', 'p.postGraduateTraining',
                'p.previousExperience', 'p.furtherTraining', 'p.specialRequirements', 'p.functions')
                ->from('App\Entity\Profile', 'p');
            $allProfiles = $query->getQuery()->getArrayResult();

            // Mapeo de valores de 'charge'
            $chargeMap = [
                '[1]' => 'Rector',
                '[2]' => 'Vicerrectores',
                '[4]' => 'Coordinación I',
                '[3]' => 'Asesores',
                '[5]' => 'Coordinación II',
                '[6]' => 'Coordinación III',
                '[7]' => 'Profesionales',
                '[8]' => 'Instructores',
                '[9]' => 'Auxiliares',
                '[10,11]' => 'Director de Programa',
                '[12,13,14,15,16,17]' => 'Profesor',
                '[10]' => 'Profesor-Director/a del programa',
                '[11]' => 'Profesor-Director del Programa Adm.SS',
                '[12]' => 'Profesor Tiempo Completo',
                '[13]' => 'Profesor Medio Tiempo',
                '[14]' => 'Profesor Universitario HC',
                '[15]' => 'Profesor Especialista HC',
                '[16]' => 'Profesor Magister HC',
                '[17]' => 'Profesor Doctorado HC',
                '[18]' => 'Profesor supervisor practica hospitalaria',
                '[19]' => 'Asistentes',
                
            ];

            foreach ($allProfiles as &$profile) {
                if (isset($chargeMap[$profile['charge']])) {
                    $profile['charge'] = $chargeMap[$profile['charge']];
                }
            }
        }
        return new JsonResponse($allProfiles, 200, []);
    }

    #[Route('institutionalData/delete-profile/{id}', name:'app_institutionalData_delete_profile')]
    public function deleteProfile(ManagerRegistry $doctrine, Request $request, int $id) : JsonResponse
    {
        $token = $request->query->get('token');
        $entityManager = $doctrine->getManager();
        if($token === false){
            return new JsonResponse(['ERROR' => 'Token no válido']);

        }else{
            $profile = $entityManager->getRepository(Profile::class)->find($id);
            if(!$profile){
                throw $this->createNotFoundException(
                    'No profile found for id'.$id['id']
                );
            }
            $entityManager->remove($profile);
            $entityManager->flush();
        }

        return new JsonResponse(['status'=>'Success','Code'=>'200','message'=>'Perfil eliminado']);
    }
}
