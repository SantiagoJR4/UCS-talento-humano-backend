<?php

namespace App\Controller;

use App\Entity\Medicaltest;
use App\Entity\User;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ContractController extends AbstractController
{
    public function validateTokenSuper(Request $request): JsonResponse
    {
        $jwtKey = 'Un1c4t0l1c4'; //TODO: move this to .env
        $token = $request->query->get('token');
        try {
            $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return new JsonResponse(false);
        }
        $expirationTime = $decodedToken->exp;
        $isTokenValid = (new DateTime())->getTimestamp() < $expirationTime;
        
        // Validar si el userType es 1 (superusuario)
        $userType = $decodedToken->userType;
        if ($isTokenValid && $userType === 1) {
            return new JsonResponse(['isValid' => true, 'userType' => $userType]);
        } else {
            return new JsonResponse(false);
        }
    }
    
    #[Route('/contract/create-medicalTest', name: 'app_contract_medicalTest')]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $isValidToken = $this->validateTokenSuper($request)->getContent();
        $entiyManager = $doctrine->getManager();
        $data = json_decode($request->getContent(),true);
    
        if($isValidToken === false){
            return new JsonResponse(['error' => 'Token no válido']);
        }
        else{
            //Trabajador seleccionado
            $user = $entiyManager->getRepository(User::class)->find($data['id']);
            if (!$user) {
                throw $this->createNotFoundException(
                    'No user found for id '.$data['id']
                );
            }
    
            $medicalTest = new Medicaltest();
            $medicalTest -> setCity($data['city']);
            $medicalTest -> setDate(new DateTime($data['date']));
            $medicalTest -> setAddress($data['address']);
            $medicalTest -> setMedicalcenter($data['medicalCenter']);
            $medicalTest -> setHour($data['hour']);
            $medicalTest -> setPhone($data['phone']);
            $medicalTest -> setTypetest($data['typeTest']);
            $medicalTest -> setOcupationalmedicaltest($data['ocupationMedicalTest']);
            $medicalTest -> setUser($user);
    
            $entiyManager->persist($medicalTest);
            $entiyManager->flush();
            
            return new JsonResponse(['status'=>'Success','code'=>'200','message'=>'Test Medico Creado']);
        }
    }

    #[Route('/contract/update-medicalTest',name:'app_contract_medicalTest_update')]
    public function update(ManagerRegistry $doctrine,Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $data = json_decode($request->getContent(),true);
        $entiyManager = $doctrine->getManager();
        $medicalTest = $entiyManager->getRepository(Medicaltest::class)->find($data['id']);

        if(!$medicalTest){
            throw $this->createNotFoundException(
                'No medicalTest found for id'.$data['id']
            );
        }

        $medicalTest -> setCity($data['city']);
        $medicalTest -> setDate(new DateTime($data['date']));
        $medicalTest -> setAddress($data['address']);
        $medicalTest -> setMedicalcenter($data['medicalCenter']);
        $medicalTest -> setHour($data['hour']);
        $medicalTest -> setTypetest($data['typeTest']);
        $medicalTest -> setOcupationalmedicaltest($data['ocupationMedicalTest']);
        $medicalTest -> setUser($user);

        $entiyManager = $doctrine->getManager();
        $entiyManager->persist($medicalTest);
        $entiyManager->flush();

        return new JsonResponse(['status'=>'Success','code'=>'200','message'=>'Test Medico Actualizado']);
    }

    
    #[Route('/contract/list-medicalTest',name:'app_contract_medicalTest_list')]
    public function listMedicalTest(ManagerRegistry $doctrine,Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $entiyManager = $doctrine->getManager();

        $medicalTests = $entiyManager->getRepository(Medicaltest::class)->findAll();
        $response = [];

        foreach($medicalTests as $medicalTest){
            $response[] = [
                'id' => $medicalTest->getId(),
                'city' => $medicalTest->getCity(),
                'date' => $medicalTest->getDate()->format('Y-m-d'),
                'address' => $medicalTest->getAddress(),
                'medicalCenter' => $medicalTest->getMedicalCenter(),
                'hour' => $medicalTest->getHour(),
                'phone' => $medicalTest->getPhone(),
                'typeTest' =>$medicalTest->getTypetest(),
                'ocupationMedicalTest' => $medicalTest->getOcupationalmedicaltest(),
                'userId'=>$medicalTest->getUser()->getId()

            ];
        }
        return new JsonResponse($response);
    }

    #[Route('/contract/delete-medicalTest/{id}',name:'app_contract_medicalTest_delete')]
    public function delete(ManagerRegistry $doctrine,Request $request, ValidateToken $vToken, int $id): JsonResponse
    {
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $entiyManager = $doctrine->getManager();

        $medicalTest = $entiyManager->getRepository(Medicaltest::class)->find($id);

        if(!$medicalTest){
            throw $this->createNotFoundException(
                'No medicalTest found for id'.$id['id']
            );
        }

        $entiyManager->remove($medicalTest);
        $entiyManager->flush();

        return new JsonResponse(['status' => 'Success', 'code' => '200', 'message' => 'Test Medico Eliminado']);
    }

}
