<?php

namespace App\Controller;

use App\Entity\ContractCharges;
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
}
