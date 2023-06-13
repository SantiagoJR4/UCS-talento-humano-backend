<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\ContractAssignment;
use App\Entity\ContractCharges;
use App\Entity\Medicaltest;
use App\Entity\Profile;
use App\Entity\User;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ContractController extends AbstractController
{
    //TODO: HACER TOKEN PARA SUPERUSUARIOS
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
    public function create(ManagerRegistry $doctrine, Request $request, MailerInterface $mailer): JsonResponse
    {
        $isValidToken = $this->validateTokenSuper($request)->getContent();
        $entiyManager = $doctrine->getManager();
        $data = json_decode($request->getContent(),true);
    
        if($isValidToken === false){
            return new JsonResponse(['error' => 'Token no válido']);
        }
        else{
            //Trabajador seleccionado
            $user = $entiyManager->getRepository(User::class)->find($data['user']);
            if (!$user) {
                throw $this->createNotFoundException(
                    'No user found for id '.$data['id']
                );
            }
    
            $medicalTest = new Medicaltest();
            $medicalTest -> setCity($data['city']);
            $medicalTest -> setDate(new DateTime($data['date']));
            $medicalTest -> setAddress($data['address']);
            $medicalTest -> setMedicalcenter($data['medicalCenter']); //AMPM SAS
            //$medicalTest -> setHour($data['hour']);
            $medicalTest -> setPhone($data['phone']);
            $medicalTest -> setTypetest($data['typeTest']);
            $medicalTest -> setOcupationalmedicaltest($data['ocupationMedicalTest']);
            $medicalTest -> setState('0');
            $medicalTest -> setUser($user);
            
            $entiyManager->persist($medicalTest);
            $entiyManager->flush();
    
            try{
                $email = (new TemplatedEmail())
                    ->from('santipo12@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Asignación de cita médica')
                    ->htmlTemplate('email/medicalTestEmail.html.twig')
                    ->context([
                        'user' => $user,
                        'medicalTest' => $medicalTest
                    ]);         
                $mailer->send($email);
                $message = 'El examén médico fue programado con éxito, se envío un correo con la información a ' . $user->getEmail();
            } catch (\Throwable $th) {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse(['status'=>'Error','message'=>$message]);
            }

            return new JsonResponse(['status'=>'Success','code'=>'200','message'=>$message]);
        }
    }

    #[Route('/contract/update-medicalTest',name:'app_contract_medicalTest_update')]
    public function update(ManagerRegistry $doctrine,Request $request, ValidateToken $vToken, MailerInterface $mailer): JsonResponse
    {
        $token = $request->query->get('token');
        $userLogueado = $vToken->getUserIdFromToken($token);
        $data = json_decode($request->getContent(),true);

        $userId = $data['userId'];
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
        $medicalTest -> setPhone($data['phone']);
        $medicalTest -> setTypetest($data['typeTest']);
        $medicalTest -> setOcupationalmedicaltest($data['ocupationMedicalTest']);
        $medicalTest -> setState($data['state']);

        $user = $entiyManager->getRepository(User::class)->find($userId);
        if(!$user){
            throw $this->createNotFoundException(
                'No user found for id'. $userId
            );
        }

        $medicalTest -> setUser($user);

        $entiyManager = $doctrine->getManager();
        $entiyManager->persist($medicalTest);
        $entiyManager->flush();

        try{
            $email = (new TemplatedEmail())
                ->from('santipo12@gmail.com')
                ->to($user->getEmail())
                ->subject('Actualización Cita Médica')
                ->htmlTemplate('email/medicalTestEmail.html.twig')
                ->context([
                    'user' => $user,
                    'medicalTest' => $medicalTest
                ]);         
            $mailer->send($email);
            $message = 'El examén médico fue actualizado con éxito, se envío un correo con la información a ' . $user->getEmail();
        } catch (\Throwable $th) {
            $message = 'Error al enviar el correo:'.$th->getMessage();
            return new JsonResponse(['status'=>'Error','message'=>$message]);
        }

        return new JsonResponse(['status'=>'Success','code'=>'200','message'=>$message]);
    
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
                'date' => $medicalTest->getDate()->format('Y-m-d H:i'),
                'address' => $medicalTest->getAddress(),
                'medicalCenter' => $medicalTest->getMedicalCenter(),
                'phone' => $medicalTest->getPhone(),
                'typeTest' =>$medicalTest->getTypetest(),
                'ocupationMedicalTest' => $medicalTest->getOcupationalmedicaltest(),
                'state' => $medicalTest->getState(),
                'userId'=>$medicalTest->getUser()->getId()

            ];
        }
        return new JsonResponse($response);
    }

    #[Route('/contract/list-medicalTestUser/{id}', name:'app_contract_medicaltTest_list_user')]
    public function listMedicalTestUser(ManagerRegistry $doctrine, int $id) : JsonResponse
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        $medicalTest = $doctrine->getRepository(Medicaltest::class)->findBy(['user' => $user]);

        if(empty($medicalTest)){
            return new JsonResponse(['status'=>false,'message' => 'No se encontraron citas medicas']);
        }

        foreach($medicalTest as $medicalTest){
            $response[] = [
                'id' => $medicalTest->getId(),
                'city' => $medicalTest->getCity(),
                'date' => $medicalTest->getDate()->format('Y-m-d H:i'),
                'address' => $medicalTest->getAddress(),
                'medicalCenter' => $medicalTest->getMedicalCenter(),
                'phone' => $medicalTest->getPhone(),
                'typeTest' =>$medicalTest->getTypetest(),
                'ocupationMedicalTest' => $medicalTest->getOcupationalmedicaltest(),
                'state' => $medicalTest->getState(),
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

    //-------------------------------------------------------------------------------
    //-- CONTRACT QUERYS
    #[Route('/contract/create-contract', name:'app_contract_create_contract')]
    public function createContract(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $isValidToken = $this->validateTokenSuper($request)->getContent();
        $entiyManager = $doctrine->getManager();
        $data = json_decode($request->getContent(),true);

        if($isValidToken === false){
            return new JsonResponse(['error' => 'Token no válido']);
        }
        else{
            $user = $entiyManager->getRepository(User::class)->find($data['user']);
            if(!$user){
                throw $this->createNotFoundException(
                    'No user found for id'. $data['id']
                );
            }

            $contract = new Contract();
            $contract -> setTypeContract($data['type_contract']);
            $contract -> setWorkStart(new DateTime($data['work_start']));
            $contract -> setInitialContract($data['initial_contract']);
            $contract -> setExpirationContract(new DateTime($data['expiration_contract']));
            $contract -> setSalary($data['salary']);
            $contract -> setWeeklyHours($data['weekly_hours']);
            $contract -> setUser($user);

            $entiyManager->persist($contract);
            $entiyManager->flush();

            return new JsonResponse(['status'=>'Success','Code' => '200', 'message' => 'Contrato generado con exito']);
        }
    }

    #[Route('/contract/assignment', name:'app_contract_assignment')]
    public function assignment(ManagerRegistry $doctrine,Request $request):JsonResponse
    {
        $isValidToken = $this->validateTokenSuper($request)->getContent();
        $entiyManager = $doctrine->getManager();
        $data = json_decode($request->getContent(),true);

        if($isValidToken === false){
            return new JsonResponse(['error' => 'Token no válido']);
        }

        $contract = $entiyManager->getRepository(Contract::class)->find($data['contract']);
        $contractCharges = $entiyManager->getRepository(ContractCharges::class)->find($data['contractCharges']);
        $profile = $entiyManager->getRepository(Profile::class)->find($data['profile']);

        $assignment = new ContractAssignment();
        $assignment -> setContract($contract);
        $assignment -> setProfile($profile);
        $assignment -> setCharge($contractCharges);

        $entiyManager -> persist($assignment);
        $entiyManager -> flush();

        return new JsonResponse(['status' => 'Success','Code'=>'200','message'=>'Asignación Correcta']);

    }

    #[Route('/contract/list-charges', name:'app_contract_list_charges')]
    public function listCharges(ManagerRegistry $doctrine, SerializerInterface $serializer) : JsonResponse
    {
        $charges = $doctrine->getRepository(ContractCharges::class)->findAll();
        $serializerAllCharges = $serializer->serialize($charges,'json');
        if(empty($charges)){ return new JsonResponse(['status'=>false,'message'=>'No se encontró lista de cargos']);}
        return new JsonResponse($serializerAllCharges,200,[],true);
    }
}
