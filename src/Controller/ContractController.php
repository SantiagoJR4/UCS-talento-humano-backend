<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\ContractAssignment;
use App\Entity\ContractCharges;
use App\Entity\DirectContract;
use App\Entity\Incapacity;
use App\Entity\License;
use App\Entity\Medicaltest;
use App\Entity\Notification;
use App\Entity\Permission;
use App\Entity\Permissions;
use App\Entity\PermissionsAndLicences;
use App\Entity\Profile;
use App\Entity\Reemployment;
use App\Entity\Requisition;
use App\Entity\User;
use App\Entity\UsersInDirectContract;
use App\Entity\UsersInRequisition;
use App\Entity\WorkHistory;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use PhpParser\Node\Expr\Cast\Array_;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        if ($isTokenValid && $userType === 8) {
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
							->to($user->getEmail(),'pasante.santiago@unicatolicadelsur.edu.co') //remplazar correo de seguridad y salud
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
	#[Route('/contract/create-contract-and-assignment', name:'app_contract_create_contract_and_assignment')]
	public function createContractAndAssignment(ManagerRegistry $doctrine, Request $request): JsonResponse
	{
		$isValidToken = $this->validateTokenSuper($request)->getContent();
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		$workStart = $data['work_start']; 
		$expirationContract = $data['expiration_contract'];

		if ($isValidToken === false) {
				return new JsonResponse(['error' => 'Token no válido']);
		} else {
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if (!$user) {
					throw $this->createNotFoundException('No user found for id' . $data['id']);
			}

			$employeeType = json_decode($data['employee_type'], true);
			if (count($employeeType) > 0) {
				$firstEmployeeType = $employeeType[0];
			
				if ($firstEmployeeType === 'AD') {
					$userTypeValue = 1; 
				} elseif ($firstEmployeeType === 'PR') {
					$userTypeValue = 2; 
				} else {
					$userTypeValue = 6;
				}
			}
			$user->setUserType($userTypeValue);

			$contract = new Contract();
			//$contract->setTypeEmployee($data['employee_type']);
			$contract->setTypeContract($data['type_contract']);
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$workStart)){
					$dateTimeWorkStart = new DateTime($workStart);
					$contract->setWorkStart($dateTimeWorkStart);
			}
			$contract->setInitialContract($data['initial_contract']);
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$expirationContract)){
					$dateTimeExpirationContract = new DateTime($expirationContract);
					$contract->setExpirationContract($dateTimeExpirationContract);
			}
			$contract->setWorkDedication($data['work_dedication']);
			$contract->setSalary($data['salary']);
			$contract->setWeeklyHours($data['weekly_hours']);
			$contract->setFunctions($data['functions']);
			$contract->setSpecificfunctions($data['specific_functions']);
			$contract->setWorkload($data['workload']);

			$currentYear = date('Y');
			$currentMonth = date('m');

			if ($currentMonth >= '1' && $currentMonth <= '6') {
				
				$contract->setPeriod('A' . $currentYear);
			} elseif ($currentMonth >= '7' && $currentMonth <= '12') {
				$contract->setPeriod('B' . $currentYear);
			}
		
			$contract->setState(1); //Activo

			$contract->setUser($user);

			$file = $request->files->get('file');
			$identificationUser = $data['identificationUser'];
			$namesUser =$user->getNames()." ".$user->getLastNames();
			
			if ($file instanceof UploadedFile) {
				$folderDestination = $this->getParameter('contract')
											.'/'
											.$identificationUser;
				$fileName = 'contrato_'.$currentYear.'- 1 -'.$namesUser.'.docx';
				try {
						$file->move($folderDestination, $fileName);
						$contract->setContractFile($fileName);
					} catch (\Exception $e) {
						return new JsonResponse(['error' => 'Error al guardar el archivo en el servidor.']);
					}
			}

			$entityManager->persist($contract);
			$entityManager->flush();
			$contractId = $contract->getId(); // Obtener el ID del contrato recién creado
			$contractEntity = $entityManager->getRepository(Contract::class)->find($contractId);

			$contractCharges = json_decode($data['contractCharges'],true);
			$profiles = json_decode($data['profiles'],true);
			// Filtrar los elementos nulos del array
			$profiles = array_filter($profiles, function ($profileId) {
				return $profileId !== null;
			});

			$numAssignments = min(count($contractCharges), count($profiles));

			for ($i = 0; $i < $numAssignments; $i++) {
					$contractChargeId = $contractCharges[$i];
					$profileId = $profiles[$i];

					$contractChargesEntity = $entityManager->getRepository(ContractCharges::class)->find($contractChargeId);
					if (!$contractChargesEntity) {
							throw $this->createNotFoundException('No contract charge found for id ' . $contractChargeId);
					}

					$profile = $entityManager->getRepository(Profile::class)->find($profileId);
					if (!$profile) {
							throw $this->createNotFoundException('No profile found for id ' . $profileId);
					}

					$assignment = new ContractAssignment();
					$assignment->setContract($contractEntity);
					$assignment->setProfile($profile);
					$assignment->setCharge($contractChargesEntity);

					$entityManager->persist($assignment);
			}

			$entityManager->flush();

			$activedReemployment = $doctrine->getRepository(Reemployment::class)->findOneBy(['user' => $user]);
			$activedDirectContract = $doctrine->getRepository(DirectContract::class)->findOneBy(['user' => $user]);
			
			if ($activedReemployment !== null) {
				$activedReemployment->setState(2);
				$doctrine->getManager()->persist($activedReemployment);
				$doctrine->getManager()->flush();
			} elseif ($activedDirectContract !== null) {
				$activedDirectContract->setState(3);
				$doctrine->getManager()->persist($activedDirectContract);
				$doctrine->getManager()->flush();
			}

			return new JsonResponse(['status' => 'Success', 'Code' => '200', 'message' => 'Contrato y asignación generados con éxito']);
		}
	}

	// Subir archivo pdf del contrato con el id del mismo
	#[Route('/contract/save-contract-file', name:'app_save_contract_file')]
	public function SaveContractFile(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken) : JsonResponse
	{
		$token = $request->query->get('token');
		$entityManager = $doctrine->getManager();
		$userLogueado = $vToken->getUserIdFromToken($token);

		$data = $request->request->all();
		$userId = intval($data['userId']);
		$user = $doctrine->getRepository(User::class)->find($userId);

		$currentYear = date('Y');
		$currentMonth = date('m');

		if ($currentMonth >= '1' && $currentMonth <= '6') {
			$period = 'A' . $currentYear;
		} elseif ($currentMonth >= '7' && $currentMonth <= '12') {
			$period = 'B' . $currentYear;
		}

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$contract = $entityManager->getRepository(Contract::class)->find($data['idContract']);
			$file = $request->files->get('file');
			$identificationUser = $data['identificationUser'];
			//$namesUser = $data['user']; 
			$fileName = $data['fileName'];
						
			if ($file instanceof UploadedFile) {
				$folderDestination = $this->getParameter('contract')
											.'/'
											.$identificationUser;
				//$fileName = 'contrato_'.$period.'- 1 -'.$namesUser.'.pdf';
				try {
						$file->move($folderDestination, $fileName);
						$contract->setContractFilePdf($fileName);
					} catch (\Exception $e) {
						return new JsonResponse(['error' => 'Error al guardar el archivo en el servidor.']);
					}
			}

			$entityManager->persist($contract);
			$entityManager->flush();

			$reemployment = $entityManager->getRepository(Reemployment::class)->findOneBy(['user' => $user]);
			
			$reemployment->setStateContract(1); //Archivo Cargado

			$entityManager->persist($reemployment);
			$entityManager->flush();
			
			return new JsonResponse(['status' => 'Success', 'Code' => '200', 'message' => 'Contrato cargado correctamente']);
		}
	}

	#[Route('/contract/read-contract/{id}', name:'app_read_contract')]
	public function readContract(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
        }
        $contracts = $doctrine->getRepository(Contract::class)->findBy(['user' => $user]);

        if (empty($contracts)) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontraron contratos para este usuario']);
        }

		$contractData = [];
        foreach ($contracts as $contract) {
            // Obtener todas las asignaciones del contrato actual
            $assignmentContract = $doctrine->getRepository(ContractAssignment::class)->findBy(['contract' => $contract->getId()]);

            $assignmentsProfiles = [];
            $assignmentsCharges = [];
            foreach ($assignmentContract as $assignment) {
                $profile = $assignment->getProfile();
                $charge = $assignment->getCharge();

                if ($profile) {
                    $assignmentsProfiles[] = [
                        'id' => $profile->getId(),
                        'name' => $profile->getName(),
                        // Agregar más campos del perfil según tu modelo
                    ];
                }

                if ($charge) {
                    $assignmentsCharges[] = [
                        'id' => $charge->getId(),
                        'name' => $charge->getName(),
                        // Agregar más campos del cargo según tu modelo
                    ];
                }
            }

            $contractData[] = [
                'contract' => [
                    'id' => $contract->getId(),
                    'type_contract' => $contract->getTypeContract(),
                    'work_start' => $contract->getWorkStart()->format('Y-m-d'),
                    'initial_contract' => $contract->getInitialContract(),
                    'expiration_contract' => $contract->getExpirationContract()->format('Y-m-d'),
                    'work_dedication' => $contract->getWorkDedication(),
                    'salary' => $contract->getSalary(),
                    'weekly_hours' => $contract->getWeeklyHours(),
                    'functions' => $contract->getFunctions(),
                    'specific_functions' => $contract->getSpecificFunctions(),
                    'contract_file' => $contract->getContractFile(),
					'contract_file_pdf' => $contract->getContractFilePdf(),
                    // Agregar más campos del contrato según tu modelo
                ],
                'assignmentsProfiles' => $assignmentsProfiles,
                'assignmentsCharges' => $assignmentsCharges,
            ];
        }

        return new JsonResponse([
            'status' => true,
            'contract_data' => $contractData,
        ]); 
	}
	//--------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------
	//----------------- CONTRATOS VIGENTES
	#[Route('/contract/read-contract-current/{id}', name:'app_read_contract_current')]
	public function readContractCurrent(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
        }

		$query = $doctrine->getManager()->createQueryBuilder();
		$query
			->select('c')
			->from('App\Entity\Contract', 'c')
			->where('c.expirationContract >= :expirationDate')
			->andWhere('c.user = :user')
			->setParameters(array('expirationDate'=> date('Y-m-d'), 'user' => $user));
		$contracts = $query->getQuery()->getResult();
        if (empty($contracts)) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontraron contratos para este usuario']);
        }

        foreach ($contracts as $contract) {
            // Obtener todas las asignaciones del contrato actual
            $assignmentContract = $doctrine->getRepository(ContractAssignment::class)->findBy(['contract' => $contract->getId()]);

            $assignmentsProfiles = [];
            $assignmentsCharges = [];
			$assignmentInmmediateBoss=[];
            foreach ($assignmentContract as $assignment) {
                $profile = $assignment->getProfile();
                $charge = $assignment->getCharge();
				$inmmediateBoss = $assignment->getInmmediateBoss();

                if ($profile) {
                    $assignmentsProfiles[] = [
                        'id' => $profile->getId(),
                        'name' => $profile->getName(),
                        // Agregar más campos del perfil según tu modelo
                    ];
                }

                if ($charge) {
                    $assignmentsCharges[] = [
                        'id' => $charge->getId(),
                        'name' => $charge->getName(),
                        // Agregar más campos del cargo según tu modelo
                    ];
                }

				if($inmmediateBoss){
					$assignmentInmmediateBoss[]=[
						'id' => $inmmediateBoss->getId(),
						'names' => $inmmediateBoss->getNames().' '.$inmmediateBoss->getLastNames(),
						'identification'=>$inmmediateBoss->getIdentification()
					];
				}
            }

            $contractData[] = [
                'contract' => [
                    'id' => $contract->getId(),
                    'type_contract' => $contract->getTypeContract(),
                    'work_start' => $contract->getWorkStart()->format('Y-m-d'),
                    'initial_contract' => $contract->getInitialContract(),
                    'expiration_contract' => $contract->getExpirationContract()->format('Y-m-d'),
                    'work_dedication' => $contract->getWorkDedication(),
                    'salary' => $contract->getSalary(),
                    'weekly_hours' => $contract->getWeeklyHours(),
                    'functions' => $contract->getFunctions(),
                    'specific_functions' => $contract->getSpecificFunctions(),
                    'contract_file' => $contract->getContractFile(),
					'contract_file_pdf' => $contract->getContractFilePdf(),
                    'state' => $contract->getState(),
					'period' => $contract->getPeriod(),
					'userIdentification' => $contract->getUser()->getIdentification(),
					'idUser' => $contract->getUser()->getId()
                ],
                'assignmentsProfiles' => $assignmentsProfiles,
                'assignmentsCharges' => $assignmentsCharges,
				'inmmediateBoss' => $assignmentInmmediateBoss
            ];
        }

        return new JsonResponse([
            'status' => true,
            'contract_data' => $contractData,
        ]); 
	}
	//--------------------------------------------------------------------------------------------
	#[Route('/contract/work-history', name:'app_contract_work_history')]
	public function addWorkHistory(ManagerRegistry $doctrine, Request $request) : JsonResponse
	{
		$isValidToken = $this->validateTokenSuper($request)->getContent();
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		$dateDocument = $data['dateDocumentInitial'];
		$dateDocumentFinal = $data['dateDocumentFinal'];

		if($isValidToken === false){
			return new JsonResponse(['error' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException(
					'No user found for id'.$data['id']
				);
			}
			$workHistory = new WorkHistory();
			
			$addToTypeDocument = '';
			switch (true) {
				case isset($data['typeOtroSi']) :
					$addToTypeDocument = $data['typeOtroSi'];
					break;
				case isset($data['typeAfiliaciones']):
					$addToTypeDocument = $data['typeAfiliaciones'];
					break;
				case isset($data['typeExamenMedico']):
					$addToTypeDocument = $data['typeExamenMedico'];
					break;
				case isset($data['other_document']):
					$addToTypeDocument = $data['other_document'];
					break;
			}

			if($addToTypeDocument !== '') {
				$typeDocument = $data['typeDocument'] . '-' . $addToTypeDocument;
			} else {
				$typeDocument = $data['typeDocument'];
			}

			$workHistory->setTypeDocument($typeDocument);
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$dateDocument)){
				$dateTimeDocument = new DateTime($dateDocument);
				$workHistory->setDateDocumentInitial($dateTimeDocument);
			}
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$dateDocumentFinal)){
				$dateTimeDocumentFinal = new DateTime($dateDocumentFinal);
				$workHistory->setDateDocumentFinal($dateTimeDocumentFinal);
			}

			if(isset($data['description'])){
				$workHistory->setDescription($data['description']);
			}
			$workHistory->setNewCharge($data['newCharge'] ?? NULL);
			$workHistory->setNewProfile($data['newProfile']?? NULL);
			$workHistory->setNewWorkDedication($data['newWorkDedication']?? NULL);
			$workHistory->setNewDuration($data['newDuration']?? NULL);
			$workHistory->setNewSalary($data['newSalary']?? NULL);
			$workHistory->setNewWeeklyHours($data['newWeeklyHours']?? NULL);
			if(isset($data['newHourPermits'])){
				$hourString = $data['newHourPermits'];
				$hourDateTime = DateTime::createFromFormat('H:i', $hourString);
				$workHistory->setHour($hourDateTime);
			}else{
				$workHistory->setHour(NULL);
			}

			$workHistory->setUser($user);

			$file = $request->files->get('documentPdf');
			$nameFile = $data['fileName'];
			$identificationUser = $data['identificationUser'];

			if($file instanceof UploadedFile){
				$folderDestination = $this->getParameter('contract').'/'.$identificationUser;
				$fileName = $identificationUser.'_'.time().'_'.$nameFile;
				try{
					$file->move($folderDestination,$fileName);
					$workHistory->setDocumentPdf($fileName);
				}catch(\Exception $e) {
					return new JsonResponse(['error' => 'Error al guardar el archivo en el servidor.']);
				}
			}

			$entityManager->persist($workHistory);
			$entityManager->flush();
		}

		return new JsonResponse(['status'=>'Success','message'=>'Historia laboral creada con éxito']);
	}

	#[Route('/contract/list-work-history/{id}',name:'app_contract_list_work_history')]
	public function listWorkHistory(ManagerRegistry $doctrine, int $id) : JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
		if (!$user) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
        }
		$workHistorys = $doctrine->getRepository(WorkHistory::class)->findBy(['user'=>$user]);
		if(empty($workHistorys)){
			return new JsonResponse(['status'=>false,'message'=>'No se encontró historia laboral para el usuario']);
		}

		$workHistoryData = [];
		foreach($workHistorys as $workHistory){
			$workHistoryData[] = [
				'id' => $workHistory->getId(),
				'type_document' => $workHistory->getTypeDocument(),
				'new_charge' => $workHistory->getNewCharge(),
				'new_profile' => $workHistory->getNewProfile(),
				'new_work_dedication' => $workHistory->getNewWorkDedication(),
				'date_document_initial' => $workHistory->getDateDocumentInitial() ? $workHistory->getDateDocumentInitial()->format('Y-m-d') : NULL,
				'date_document_final' => $workHistory->getDateDocumentFinal() ? $workHistory->getDateDocumentFinal()->format('Y-m-d') : NULL,
				'new_duration' => $workHistory->getNewDuration(),
				'new_salary' => $workHistory->getNewSalary(),
				'new_weekly_hours' => $workHistory->getNewWeeklyHours(),
				'hour' => $workHistory->getHour() ? $workHistory->getHour()->format('H:i') : NULL,
				'description' => $workHistory->getDescription(),
				'document_pdf' => $workHistory->getDocumentPdf()
			];
		}
		return new JsonResponse(['work_history_data' => $workHistoryData]);
	}
	//--------------------------------------------------------------------------------------------
	#[Route('/contract/list-charges', name:'app_contract_list_charges')]
	public function listCharges(ManagerRegistry $doctrine, SerializerInterface $serializer) : JsonResponse
	{
		$charges = $doctrine->getRepository(ContractCharges::class)->findAll();
		$serializerAllCharges = $serializer->serialize($charges,'json');
		if(empty($charges)){ return new JsonResponse(['status'=>false,'message'=>'No se encontró lista de niveles']);}
		return new JsonResponse($serializerAllCharges,200,[],true);
	}

	#[Route('/contract/get-AllProfiles', name: 'app_get_AllProfiles')]
	public function getAllProfiles(ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
	{
		$allProfiles = $doctrine->getRepository(Profile::class)->findAll();
		$serializerAllProfiles = $serializer->serialize($allProfiles,'json');
		if(empty($allProfiles)){ return new JsonResponse(['status'=>false,'message'=>'No se encontró lista de cargos']);}
		return new JsonResponse($serializerAllProfiles,200,[],true);
	}

	#[Route('/contract/get-profiles-charges', name: 'app_get_profiles_charges')]
	public function getProfilesCharges(Request $request, Connection $connection): JsonResponse
	{
		$contractChargeId = $request->query->get('contractChargeId');
		$sql = "
				SELECT p.id, p.charge, p.functions, c.type_employee, c.name, p.name
				FROM profile p
				JOIN contract_charges c ON JSON_CONTAINS(p.charge, CAST(c.id AS CHAR), '$')
				WHERE c.id = :contractChargeId;
		";
		$results = $connection->executeQuery($sql, ['contractChargeId' => $contractChargeId])->fetchAllAssociative();
		return new JsonResponse($results);
	}
	//--------------------------------------------------------------------------------------------
	// PERMISOS Y LICENCIAS.
	#[Route('/contract/create-permission', name:'app_contract_create_permission')]
	public function createPermission( ManagerRegistry $doctrine,Request $request): JsonResponse
	{
		$isValidToken = $this->validateTokenSuper($request)->getContent();
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();
		if($isValidToken === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if (!$user) {
					throw $this->createNotFoundException('No user found for id' . $data['id']);
			}
			$permission = new Permission();
			$solicitudeDate = new DateTime();
			$permission -> setSolicitudeDate($solicitudeDate);

			$permission -> setTypePermission($data['type_permission'] );
			$permission -> setTypeFlexibility($data['type_flexibility'] ?? NULL);
			$permission -> setTypeCompensation($data['type_compensation'] ?? NULL );
			$permission -> setTypeDatePermission($data['type_date_permission'] );
			$permission -> setReason($data['reason']);
			
			$permission -> setDatesArray($data['datesArray']);
			$permission -> setDatesCompensation($data['datesCompensation']);
			$permission -> setState(0);
			$permission -> setUser($user);

			$file = $request->files->get('support_pdf');
			if(isset($file)){
				$nameFile = $data['fileName'];
				$identificationUser = $data['identificationUser'];
	
				if($file instanceof UploadedFile){
					$folderDestination = $this->getParameter('contract').'/'.$identificationUser;
					$fileName = $identificationUser.'_'.time().'_'.$nameFile;
					try{
						$file->move($folderDestination,$fileName);
						$permission->setSupportPdf($fileName);
					}catch(\Exception $e){
						return new JsonResponse(['error' => 'Error al solicitar el permiso']);
					}
				}
			}else{
				$permission->setSupportPdf('Sin soporte');
			}

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $user->getId(),
				'responsible' => $user->getSpecialUser(),
				'state' => 0,
				'message' => 'El permiso fue solicitado por '.$user->getNames()." ".$user->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$permission->setHistory($addToHistory);

			$entityManager->persist($permission);
			$entityManager->flush();

			$immediateBossArray = json_decode($data['arrayImmediateBoss'],true);
			// $immediateBossIds = [];

			foreach ($immediateBossArray as $boss) {
				$bossID = $boss['id'];
				$immediateBossUsers = $doctrine->getRepository(User::class)->find($bossID);
				$newNotification = new Notification();
				$newNotification->setSeen(0);
				$newNotification->setUser($immediateBossUsers);
				$newNotification->setMessage('Solicita la aprobación de un permiso');
				
				$relatedEntity = array(
					'id' => $permission->getId(),
					'applicantId'=>$user->getId(),
					'applicantName' => $user->getNames() . " " . $user->getLastNames(),
					'entity' => 'permission'    
				);
				$newNotification->setRelatedEntity(json_encode($relatedEntity));
				
				$entityManager->persist($newNotification);
			}

			$entityManager->flush();

		}

		return new JsonResponse(['status'=>'Success','message'=>'Permiso solicitado con éxito']);
	}

	//-------LISTAR PERMISO CON EL ID DEL USUARIO
	#[Route('contract/list-permission/{id}', name:'app_contract_list_permission')]
	public function listPermission(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
		if(!$user){
			return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
		}
		$permissions = $doctrine->getRepository(Permission::class)->findBy(['user'=>$user]);
		if(empty($permissions)){
			return new JsonResponse(['status'=>false,'message'=>'No se encontró ninguna solicitud de permisos']);
		}

		$permissionData = [];
		foreach($permissions as $permission){
			$user = $permission->getUser();
			$compensation = $permission->getDatesCompensation();
			if($compensation === '[]'){
				$compensation = 'Sin fechas de compensación';
			}
			$permissionData[] = [
				'permission' => [
					'id' => $permission->getId(),
					'solicitude_date' => $permission->getSolicitudeDate()->format('Y-m-d'),
					'type_permission' => $permission->getTypePermission(),
					'type_flexibility' => $permission->getTypeFlexibility(),
					'type_compensation' => $permission->getTypeCompensation(),
					'reason' => $permission->getReason(),
					'support_pdf' => $permission->getSupportPdf(),
					'state' => $permission->getState(),
					'history' => $permission->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification()
				],
				'datesArray' => json_decode($permission->getDatesArray(),true),
				'datesCompensation' => json_decode($compensation,true)
			];
		}
		return new JsonResponse(['status'=>true, 'permission'=>$permissionData]);
	}
	//----------------------------------------------------------------------------------------
	//-------LISTAR PERMISO CON EL ID DEL PERMISO
	#[Route('contract/get-permission/{id}', name:'app_contract_get_permission')]
	public function getPermission(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$permission = $doctrine->getRepository(Permission::class)->find($id);

		if (!$permission) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró el permiso']);
		}

		$user = $permission->getUser();
		$compensation = $permission->getDatesCompensation();
		if($compensation === '[]'){
			$compensation = 'Sin fechas de compensación';
		}
		$permissionData = [
			'permission' => [
				'id' => $permission->getId(),
				'solicitude_date' => $permission->getSolicitudeDate()->format('Y-m-d'),
				'type_permission' => $permission->getTypePermission(),
				'type_flexibility' => $permission->getTypeFlexibility(),
				'type_compensation' => $permission->getTypeCompensation(),
				'reason' => $permission->getReason(),
				'support_pdf' => $permission->getSupportPdf(),
				'state' => $permission->getState(),
				'history' => $permission->getHistory(),
				'username' => $user->getNames().' '.$user->getLastNames(),
				'idUser' => $user->getId(),
				'userIdentification' => $user->getIdentification()
			],
			'datesArray' => json_decode($permission->getDatesArray(),true),
			'datesCompensation' => json_decode($compensation,true)
		];
		return new JsonResponse(['status'=>true, 'permission'=>$permissionData]);
	}
	#[Route('contract/approve-permission', name:'app_approve_permission')]
	public function approvePermission(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
	{
		$token = $request->query->get('token');
		$permissionId = $request->query->get('permissionId');
		$notificationId = $request->query->get('notificationId');
		$applicant = $request->query->get('applicant');
		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$permission = $doctrine->getRepository(Permission::class)->find($permissionId);
	
		if($permission === NULL){
			return new JsonResponse(['message'=>'No existe un permiso'],400,[]);
		}
		$newStateForPermission = 0;
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicant);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'id' => $permissionId,
			'applicantId'=>$applicant,
			'applicantName'=>$userNames,
			'entity'=>'permission'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'CTH':
				$newStateForPermission = 2;
				$userWhoMadePermission = $permission->getUser();

				$newNotification->setUser($userWhoMadePermission);
				$newNotification->setMessage('Revisión de permiso finalizada.');
				break;
			default:
				$newStateForPermission = 1;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('solicita la aprobación de un permiso por parte de Talento Humano');
				break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$permission->setState($newStateForPermission);
		$history = $permission->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser() ?? $user->getNames(),
			'state' => $newStateForPermission,
			'message' => 'El permiso fue aprobado por '.$user->getNames()." ".$user->getLastNames(),
			'date' => date('Y-m-d H:i:s'),
		));
		$newHistory = rtrim($history, ']').','.$addToHistory.']';
		$permission->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
		$entityManager->flush();
		return new JsonResponse(['message'=> 'Se ha aprobado el permiso con el id'. $permissionId]);
		
	}
	#[Route('contract/reject-permission', name:'app_reject_permission')]
	public function rejectPermission(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token= $request->query->get('token');
		$permissionId = $request->query->get('permissionId');
		$notificationId = $request->query->get('notificationId');
		$rejectText = $request->request->get('rejectText');
		$applicant = $request->query->get('applicant');
		
		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$permission = $doctrine->getRepository(Permission::class)->find($permissionId);
		if($permission === NULL){
			return new JsonResponse(['message'=>'No existe ningun permiso solicitado'],400,[]);
		}
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicant);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'id' => $permissionId,
			'applicantId' => $applicant,
			'applicantName' => $userNames,
			'entity' => 'permission'	
		);

		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'CTH':
				$newNotification->setMessage('Permiso rechazado por Talento humano');
				$userWhoMadePermission = $permission->getUser();
				$newNotification->setUser($userWhoMadePermission);
				break;
			default:
                $newNotification->setMessage('Permiso rechazado por Jefe inmediato');
				$userWhoMadePermission = $permission->getUser();
				$newNotification->setUser($userWhoMadePermission);
				break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$permission->setState(3);
		$history = $permission->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(), 
			'responsible' => $user->getSpecialUser(),
			'state' => 3,
			'message' => 'El permiso fue rechazado por'.$user->getNames()." ".$user->getLastNames(),
			'userInput' => $rejectText,
            'date' => date('Y-m-d H:i:s'),
		));
		$newHistory= rtrim($history, ']').','.$addToHistory.']';
		$permission->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha rechazado este permiso con el id '.$permissionId], 200, []);
	}
	#[Route('contract/all-permissions',name:'app_contract_all_permissions')]
	public function allPermissions(ManagerRegistry $doctrine) : JsonResponse
	{
		$permissionData = [];
		$permissions = $doctrine->getRepository(Permission::class)->findAll();
		if(empty($permissions)){
			return new JsonResponse(['status'=>false, 'message'=>'No se encontraron permisos solicitados']);
		}
		foreach($permissions as $permission){
			$user = $permission->getUser();
			$compensation = $permission->getDatesCompensation();
			if($compensation === '[]'){
				$compensation = 'Sin fechas de compensación';
			}
			$permissionData[] = [
				'permission' => [
					'id' => $permission->getId(),
					'solicitude_date' => $permission->getSolicitudeDate()->format('Y-m-d'),
					'type_permission' => $permission->getTypePermission(),
					'type_flexibility' => $permission->getTypeFlexibility(),
					'type_compensation' => $permission->getTypeCompensation(),
					'reason' => $permission->getReason(),
					'support_pdf' => $permission->getSupportPdf(),
					'state' => $permission->getState(),
					'history' => $permission->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification()
				],
				'datesArray' => json_decode($permission->getDatesArray(),true),
				'datesCompensation' => json_decode($compensation,true)
			];
		}
		return new JsonResponse(['status' => true, 'permissions' => $permissionData]);
	}
	//----********************************LICENCIAS****************************************---
	//----------------------------------------------------------------------------------------
	//----------------------------------------------------------------------------------------
	#[Route('contract/create-license', name:'app_contract_create_license')]
	public function createLicense(ManagerRegistry $doctrine, Request $request): JsonResponse
	{
		$isTokenValid = $this->validateTokenSuper($request)->getContent();
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		$initialDate = $data['initial_date'] ?? NULL;
		$finalDate = $data['final_date'] ?? NULL;

		if($isTokenValid === false){
			return new JsonResponse(['error' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException('No user found for id' . $data['id']);
			}
			$license = new License();
			$solicitudeDate = new DateTime();
			$license -> setSolicitudeDate($solicitudeDate);

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$initialDate)){
				$dateTimeInitial = new DateTime($initialDate);
				$license -> setInitialDate($dateTimeInitial);
			}

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finalDate)){
				$dateTimeFinal = new DateTime($finalDate);
 				$license -> setFinalDate($dateTimeFinal);
			}

			$license -> setTypeCompensation($data['type_compensation']);
			$license -> setTypeLicense($data['type_license']);
			$license -> setLicense($data['license']);
			$license -> setOthertypeLicense($data['otherTypeLicense'] ?? NULL);
			$license -> setReason($data['reason']);
			$license -> setState(0);
			$license -> setUser($user);

			$file = $request->files->get('support_pdf_license');
			if(isset($file)){
				$nameFile = $data['fileName'];
				$identificationUser = $data['identificationUser'];
	
				if($file instanceof UploadedFile){
					$folderDestination = $this->getParameter('contract').'/'.$identificationUser;
					$fileName = $identificationUser.'_'.time().'_'.$nameFile;
					try{
						$file->move($folderDestination,$fileName);
						$license->setSupportPdf($fileName);
					}catch(\Exception $e){
						return new JsonResponse(['error' => 'Error al solicitar la licencia']);
					}
				}
			}else{
				$license->setSupportPdf('Sin soporte');
			}

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $user->getId(),
				'responsible' => $user->getSpecialUser(),
				'state' => 0,
				'message' => 'La licencia fue solicitada por '.$user->getNames()." ".$user->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$license->setHistory($addToHistory);

			$entityManager->persist($license);
			$entityManager->flush();

			$immediateBossArray = json_decode($data['arrayImmediateBoss'],true);

			foreach($immediateBossArray as $boss){
				$bossID = $boss['id'];
				$immediateBossUsers = $doctrine->getRepository(User::class)->find($bossID);
				$newNotification = new Notification();
				$newNotification->setSeen(0);
				$newNotification->setUser($immediateBossUsers);
				$newNotification->setMessage('Solicita la aprobación de una licencia');
				$relatedEntity = array(
					'id'=>$license->getId(),
					'applicantId'=>$user->getId(),
					'applicantName'=>$user->getNames()." ".$user->getLastNames(),
					'entity' => 'license'
				);
				$newNotification->setRelatedEntity(json_encode($relatedEntity));
				$entityManager->persist($newNotification);
			}

			$entityManager->flush();
		}
		return new JsonResponse(['status'=>'Success','message'=>'Licencia solicitada con éxito']);
	}
	//-------LISTAR LICENCIA CON EL ID DEL USUARIO
	#[Route('contract/list-licenses/{id}', name:'app_contract_list_licenses')]
	public function listLicense(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
		if(!$user){
			return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
		}
		$licenses = $doctrine->getRepository(License::class)->findBy(['user'=>$user]);
		if(empty($licenses)){
			return new JsonResponse(['status'=>false,'message'=>'No se encontró ninguna solicitud de licencias']);
		}
		$licensesData = [];
		foreach($licenses as $license){
			$user = $license->getUser();
			$licensesData[] = [
				'license' => [
					'id' => $license->getId(),
					'solicitude_date' => $license->getSolicitudeDate()->format('Y-m-d'),
					'type_license' => $license->getTypelicense(),
					'type_compensation' => $license->getTypeCompensation(),
					'license' => $license->getLicense(),
					'otherLicense' => $license->getOthertypeLicense(),
					'reason' => $license->getReason(),
					'initial_date' => $license->getInitialDate()->format('Y-m-d'),
					'final_date' => $license->getFinalDate()->format('Y-m-d'),
					'support_pdf' => $license->getSupportPdf(),
					'state' => $license->getState(),
					'history' => $license->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification()
				]
			];
		}
		return new JsonResponse(['status'=>true, 'license'=>$licensesData]);
	}
	//----------------------------------------------------------------------------------------
	//-------LISTAR LICENCIA CON EL ID DE LA LICENCIA
	#[Route('contract/get-license/{id}', name:'app_contract_get_license')]
	public function getLicense(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$license = $doctrine->getRepository(License::class)->find($id);

		if (!$license) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró la licencia']);
		}

		$user = $license->getUser();
		$licenseData = [
			'license' => [
				'id' => $license->getId(),
				'solicitude_date' => $license->getSolicitudeDate()->format('Y-m-d'),
				'type_license' => $license->getTypelicense(),
				'type_compensation' => $license->getTypeCompensation(),
				'license' => $license->getLicense(),
				'otherLicense' => $license->getOthertypeLicense(),
				'reason' => $license->getReason(),
				'initial_date' => $license->getInitialDate()->format('Y-m-d'),
				'final_date' => $license->getFinalDate()->format('Y-m-d'),
				'support_pdf' => $license->getSupportPdf(),
				'state' => $license->getState(),
				'history' => $license->getHistory(),
				'username' => $user->getNames().' '.$user->getLastNames(),
				'userIdentification' => $user->getIdentification()
			]
		];
		return new JsonResponse(['status'=>true, 'license'=>$licenseData]);
	}
	#[Route('contract/approve-license', name:'app_approve_license')]
	public function approveLicense(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
	{
		$token = $request->query->get('token');
		$licenseId = $request->query->get('licenseId');
		$notificationId = $request->query->get('notificationId');
		$applicant = $request->query->get('applicant');
		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$license = $doctrine->getRepository(License::class)->find($licenseId);
	
		if($license === NULL){
			return new JsonResponse(['message'=>'No existe una licencia'],400,[]);
		}
		$newStateForLicense = 0;
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicant);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'id' => $licenseId,
			'applicantId'=>$applicant,
			'applicantName' => $userNames,
			'entity'=>'license'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'CTH':
				$newStateForLicense = 2;
				$userWhoMadeLicense = $license->getUser();
				$newNotification->setUser($userWhoMadeLicense);
				$newNotification->setMessage('Revisión de licencia finalizada.');
				break;
			default:
				$newStateForLicense = 1;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
				$newNotification->setUser($userForNotification);
				$newNotification->setMessage('solicita la aprobación de una licencia por parte de Coordinación de talento humano');
				break;
			return new JsonResponse(['message'=>'Usuario no autorizado'],403,[]);
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$license->setState($newStateForLicense);
		$history = $license->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => $newStateForLicense,
			'message' => 'La licencia fue aprobada por '.$user->getNames()." ".$user->getLastNames(),
			'date' => date('Y-m-d H:i:s'),
		));
		$newHistory = rtrim($history, ']').','.$addToHistory.']';
		$license->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
		$entityManager->flush();
		return new JsonResponse(['message'=> 'Se ha aprobado la licencia con el id'. $licenseId]);
		
	}
	#[Route('contract/reject-license', name:'app_reject_license')]
	public function rejectLicense(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token= $request->query->get('token');
		$licenseId = $request->query->get('licenseId');
		$rejectText = $request->request->get('rejectText');
		$applicant = $request->query->get('applicant');
		$notificationId = $request->query->get('notificationId');

		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$license = $doctrine->getRepository(License::class)->find($licenseId);
		if($license === NULL){
			return new JsonResponse(['message'=>'No existe ninguna licencia solicitada'],400,[]);
		}
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicant);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'id' => $licenseId,
			'applicantId' => $applicant,
			'applicantName' => $userNames,
			'entity' => 'license'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'CTH':
				// $userWhoMadeLicense = $license->getUser();
				// $newNotification->setUser($userWhoMadeLicense);
				$newNotification->setMessage('Licencia rechazada por Talento humano');
				break;
			default:
				//$userWhoMadeLicense = $license->getUser();
				// $newNotification->setUser($userWhoMadeLicense);
                $newNotification->setMessage('Licencia rechazada por Jefe inmediato');
				break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$license->setState(3);
		$history = $license->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => 3,
			'message' => 'La licencia fue rechazado por'.$user->getNames()." ".$user->getLastNames(),
			'userInput' => $rejectText,
            'date' => date('Y-m-d H:i:s'),
		));
		$newHistory= rtrim($history, ']').','.$addToHistory.']';
		$license->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha rechazado la licencia con el id '.$licenseId], 200, []);
	}
	#[Route('contract/all-licenses',name:'app_contract_all_licenses')]
	public function allLicenses(ManagerRegistry $doctrine) : JsonResponse
	{
		$licenseData = [];
		$licenses = $doctrine->getRepository(License::class)->findAll();
		if(empty($licenses)){
			return new JsonResponse(['status'=>false, 'message'=>'No se encontraron licencias solicitados']);
		}
		foreach($licenses as $license){
			$user = $license->getUser();
			$licenseData[] = [
				'license' => [
					'id' => $license->getId(),
					'solicitude_date' => $license->getSolicitudeDate()->format('Y-m-d'),
					'type_license' => $license->getTypelicense(),
					'type_compensation' => $license->getTypeCompensation(),
					'license' => $license->getLicense(),
					'otherLicense' => $license->getOthertypeLicense(),
					'reason' => $license->getReason(),
					'initial_date' => $license->getInitialDate()->format('Y-m-d'),
					'final_date' => $license->getFinalDate()->format('Y-m-d'),
					'support_pdf' => $license->getSupportPdf(),
					'state' => $license->getState(),
					'history' => $license->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification()
				]
			];
		}
		return new JsonResponse(['status' => true, 'licenses' => $licenseData]);
	}
	//******************************************INCAPACITY****************************************/
	//--------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------
	#[Route('/contract/create-incapacity', name:'app_contract_create_incapacity')]
	public function createIncapacity(ManagerRegistry $doctrine, Request $request) : JsonResponse 
	{	
		$isValidToken = $this->validateTokenSuper($request)->getContent();
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		$incapacityDate = $data['incapacity_date'];

		if($isValidToken === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException('No user found for id'. $data['id']);
			}
			$incapacity = new Incapacity();
			$solicitudeDate = new DateTime();
			$incapacity -> setSolicitudeDate($solicitudeDate);

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$incapacityDate)){
				$dateIncapacity = new DateTime($incapacityDate);
				$incapacity -> setIncapacityDate($dateIncapacity);
			}
			$incapacity -> setNumberDaysIncapacity($data['number_days_incapacity']);
			$incapacity -> setOriginIncapacity($data['origin_incapacity']);
			$incapacity -> setState(0);
			$incapacity -> setUser($user);

			$file1 = $request->files->get('medical_support_pdf');
			$file2 = $request->files->get('eps_support_pdf');
			$identificationUser = $data['identificationUser'];

			if(isset($file1)){
				$nameFileMedical = $data['fileName1'];	
				if($file1 instanceof UploadedFile){
					$folderDestination = $this->getParameter('contract').'/'.$identificationUser;
					$fileNameMedical = $identificationUser.'_'.time().'_'.$nameFileMedical;
					try{
						$file1->move($folderDestination,$fileNameMedical);
						$incapacity->setMedicalSupportPdf($fileNameMedical);
					}catch(\Exception $e){
						return new JsonResponse(['error' => 'Error al solicitar una incapacidad']);
					}
				}
			}else{
				$incapacity->setMedicalSupportPdf('Sin soporte');
			}
			if(isset($file2)){
				$nameFileEps = $data['fileName2'];	
				if($file2 instanceof UploadedFile){
					$folderDestination = $this->getParameter('contract').'/'.$identificationUser;
					$fileNameEps = $identificationUser.'_'.time().'_'.$nameFileEps;
					try{
						$file2->move($folderDestination,$fileNameEps);
						$incapacity->setEpsSupportPdf($fileNameEps);
					}catch(\Exception $e){
						return new JsonResponse(['error' => 'Error al solicitar una incapacidad']);
					}
				}
			}else{
				$incapacity->setEpsSupportPdf('Sin soporte');
			}

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $user->getId(),
				'responsible' => $user->getSpecialUser(),
				'state' => 0,
				'message' => 'La incapacidad fue solicitada por '.$user->getNames()." ".$user->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$incapacity->setHistory($addToHistory);

			$entityManager->persist($incapacity);
			$entityManager->flush();

			$immediateBossArray = json_decode($data['arrayImmediateBoss'],true);
			// $immediateBossIds = [];

			foreach ($immediateBossArray as $boss) {
				$bossID = $boss['id'];
				$immediateBossUsers = $doctrine->getRepository(User::class)->find($bossID);
				$newNotification = new Notification();
				$newNotification->setSeen(0);
				$newNotification->setUser($immediateBossUsers);
				$newNotification->setMessage('Solicita la aprobación de una incapacidad');
				
				$relatedEntity = array(
					'id' => $incapacity->getId(),
					'applicantId'=>$user->getId(),
					'applicantName' => $user->getNames() . " " . $user->getLastNames(),
					'entity' => 'incapacity'    
				);
				$newNotification->setRelatedEntity(json_encode($relatedEntity));
				
				$entityManager->persist($newNotification);
			}
			$entityManager->flush();

		}
		return new JsonResponse(['status'=>'Success','message'=>'Incapacidad solicitada con éxito']);
	}
	//-----------------------------------------------------------------------------------------------
	//---------- LISTAR INCAPACIDAD CON EL ID DEL USUARIO
	#[Route('contract/list-incapacity/{id}', name:'app_contract_list_incapacity')]
	public function listIncapacity(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
		if(!$user){
			return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
		}
		$incapacitys = $doctrine->getRepository(Incapacity::class)->findBy(['user'=>$user]);
		if(empty($incapacitys)){
			return new JsonResponse(['status'=>false,'message'=>'No se encontró ninguna solicitud de incapacidades']);
		}
		$contracts = $doctrine->getRepository(Contract::class)->findBy(['user'=>$user]);
		if (empty($contracts)) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontraron contratos para este usuario']);
        }
		foreach($contracts as $contract){
			$assignmentContract = $doctrine->getRepository(ContractAssignment::class)->findBy(['contract' => $contract->getId()]);
			$assignmentsCharges = [];
		
			if (!empty($assignmentContract)) {
				foreach ($assignmentContract as $assignment) {
					$charge = $assignment->getCharge();
		
					if ($charge) {
						$assignmentsCharges[] = [
							'nameCharge' => $charge->getName(),
						];
					}
				}
			}
		}

		$incapacityData = [];
		foreach($incapacitys as $incapacity){
			$user = $incapacity->getUser();
			$incapacityData[] = [
				'incapacity' => [
					'id' => $incapacity->getId(),
					'solicitude_date' => $incapacity->getSolicitudeDate()->format('Y-m-d'),
					'incapacity_date' => $incapacity->getIncapacityDate()->format('Y-m-d'),
					'number_days_incapacity' => $incapacity->getNumberDaysIncapacity(),
					'origin_incapacity' => $incapacity->getOriginIncapacity(),
					'medical_support_pdf' => $incapacity->getMedicalSupportPdf(),
					'eps_support_pdf' => $incapacity->getEpsSupportPdf(),
					'state' => $incapacity->getState(),
					'history' => $incapacity->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification(),
					'emailUser' => $user->getEmail(),
					'phoneUser' => $user->getPhone(),
					'charge'=>$assignmentsCharges
				]
			];
		}
		return new JsonResponse(['status'=>true, 'incapacity'=>$incapacityData]);
	}
	//----------------------------------------------------------------------------------------------
	//--------- LISTAR INCAPACIDAD CON EL ID DE LA INCAPACIDAD
	#[Route('contract/get-incapacity/{id}', name:'app_contract_get_incapacity')]
	public function getIncapacity(ManagerRegistry $doctrine, int $id):JsonResponse
	{
		$incapacity = $doctrine->getRepository(Incapacity::class)->find($id);
		if(!$incapacity){
			return new JsonResponse(['status'=>false, 'message'=>'No se encontró la incapacidad']);
		}
		$user = $incapacity->getUser();
		$contracts = $doctrine->getRepository(Contract::class)->findBy(['user'=>$user]);
		if (empty($contracts)) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontraron contratos para este usuario']);
        }
		foreach($contracts as $contract){
			// Obtener todas las asignaciones del contrato actual
			$assignmentContract = $doctrine->getRepository(ContractAssignment::class)->findBy(['contract' => $contract->getId()]);
			$assignmentsCharges = [];
			foreach ($assignmentContract as $assignment) {
				$charge = $assignment->getCharge();

				if ($charge) {
					$assignmentsCharges[] = [
						'nameCharge' => $charge->getName(),
					];
				}
			}
		}

		$incapacityData = [
			'incapacity' => [
				'id' => $incapacity->getId(),
				'solicitude_date' => $incapacity->getSolicitudeDate()->format('Y-m-d'),
				'incapacity_date' => $incapacity->getIncapacityDate()->format('Y-m-d'),
				'number_days_incapacity' => $incapacity->getNumberDaysIncapacity(),
				'origin_incapacity' => $incapacity->getOriginIncapacity(),
				'medical_support_pdf' => $incapacity->getMedicalSupportPdf(),
				'eps_support_pdf' => $incapacity->getEpsSupportPdf(),
				'state' => $incapacity->getState(),
				'history' => $incapacity->getHistory(),
				'username' => $user->getNames().' '.$user->getLastNames(),
				'userIdentification' => $user->getIdentification(),
				'emailUser' => $user->getEmail(),
				'phoneUser' => $user->getPhone(),
				'charge'=>$assignmentsCharges
			]
		];
		return new JsonResponse(['status'=>true, 'incapacity'=>$incapacityData]);
	}
	#[Route('contract/approve-incapacity', name:'app_approve_incapacity')]
	public function approveIncapacity(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
	{
		$token = $request->query->get('token');
		$incapacityId = $request->query->get('incapacityId');
		$notificationId = $request->query->get('notificationId');
		$applicant = $request->query->get('applicant');
		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$incapacity = $doctrine->getRepository(Incapacity::class)->find($incapacityId);

		if($incapacity === NULL){
			return new JsonResponse(['message'=>'No existe una incapacidad'],400,[]);
		}
		$newStateForIncapacity = 0;
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicant);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'id' => $incapacityId,
			'applicantId'=>$applicant,
			'applicantName'=>$userNames,
			'entity' => 'incapacity'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'ATHSST':
				$newStateForIncapacity = 1;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('solicita la aprobación de una incapacidad por parte de Coordinación de talento humano');
				break;
			case 'CTH':
				$newStateForIncapacity = 2;
				$userWhoMadeIncapacity = $incapacity->getUser();
				$newNotification->setUser($userWhoMadeIncapacity);
				$newNotification->setMessage('Revisión de incapacidad finalizada.');
				break;
			default:
			return new JsonResponse(['message'=>'Usuario no autorizado'],403,[]);
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$incapacity->setState($newStateForIncapacity);
		$history = $incapacity->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => $newStateForIncapacity,
			'message' => 'La incapacidad fue aprobada por '.$user->getNames()." ".$user->getLastNames(),
			'date' => date('Y-m-d H:i:s'),
		));
		$newHistory = rtrim($history, ']').','.$addToHistory.']';
		$incapacity->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
		$entityManager->flush();
		return new JsonResponse(['message'=> 'Se ha aprobado la incapacidad con el id'. $incapacityId]);
	}
	#[Route('contract/reject-incapacity', name:'app_reject_incapacity')]
	public function rejectIncapacity(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken):JsonResponse
	{
		$token= $request->query->get('token');
		$incapacityId = $request->query->get('incapacityId');
		$rejectText = $request->request->get('rejectText');
		$applicant = $request->query->get('applicant');
		$notificationId = $request->query->get('notificationId');

		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$incapacity = $doctrine->getRepository(Incapacity::class)->find($incapacityId);
		if($incapacity === NULL){
			return new JsonResponse(['message'=>'No existe ninguna incapacidad solicitada'],400,[]);
		}
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicant);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'id' => $incapacityId,
			'applicantId' => $applicant,
			'applicantName'=>$userNames,
			'entity' => 'incapacity'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'ATHSST':
				$userWhoMadeIncapacity = $incapacity->getUser();
				$newNotification->setUser($userWhoMadeIncapacity);
                $newNotification->setMessage('Incapacidad rechazada por Asistente seguridad y salud en el trabajo');
				break;
			case 'CTH':
				$userWhoMadeIncapacity = $incapacity->getUser();
				$newNotification->setUser($userWhoMadeIncapacity);
				$newNotification->setMessage('Incapacidad rechazada por Talento humano');
				break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$incapacity->setState(3);
		$history = $incapacity->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => 3,
			'message' => 'La incapacidad fue rechazada por'.$user->getNames()." ".$user->getLastNames(),
			'userInput' => $rejectText,
            'date' => date('Y-m-d H:i:s'),
		));
		$newHistory= rtrim($history, ']').','.$addToHistory.']';
		$incapacity->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha rechazado la incapacidad con el id '.$incapacityId], 200, []);

	}

	#[Route('contract/all-incapacities', name:'incapacities')]
	public function allIncapacities(ManagerRegistry $doctrine): JsonResponse
	{
		$incapacities = $doctrine->getRepository(Incapacity::class)->findAll();
		if (empty($incapacities)) {
			return new JsonResponse(['status'=>false, 'message'=>'No se encontró ninguna solicitud de incapacidades']);
		}

		$incapacityData = [];
		foreach ($incapacities as $incapacity) {
			$user = $incapacity->getUser();
			$assignmentsCharges = [];

			// Obtener todos los contratos del usuario actual
			$contracts = $doctrine->getRepository(Contract::class)->findBy(['user' => $user]);

			foreach ($contracts as $contract) {
				// Obtener todas las asignaciones del contrato actual
				$assignmentContract = $doctrine->getRepository(ContractAssignment::class)->findBy(['contract' => $contract->getId()]);

				foreach ($assignmentContract as $assignment) {
					$charge = $assignment->getCharge();

					if ($charge) {
						$assignmentsCharges[] = [
							'nameCharge' => $charge->getName(),
						];
					}
				}
			}

			$incapacityData[] = [
				'incapacity' => [
					'id' => $incapacity->getId(),
					'solicitude_date' => $incapacity->getSolicitudeDate()->format('Y-m-d'),
					'incapacity_date' => $incapacity->getIncapacityDate()->format('Y-m-d'),
					'number_days_incapacity' => $incapacity->getNumberDaysIncapacity(),
					'origin_incapacity' => $incapacity->getOriginIncapacity(),
					'medical_support_pdf' => $incapacity->getMedicalSupportPdf(),
					'eps_support_pdf' => $incapacity->getEpsSupportPdf(),
					'state' => $incapacity->getState(),
					'history' => $incapacity->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification(),
					'emailUser' => $user->getEmail(),
					'phoneUser' => $user->getPhone(),
					'charge' => $assignmentsCharges,
				]
			];
   			return new JsonResponse(['status'=>true, 'incapacities'=>$incapacityData]);
		}
	}
	//--****************************************REQUISITON*********************************************----
	///-------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------
	#[Route('contract/create-requisition', name:'app_contract_create_requisition')]
	public function createRequisition(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		$userLogueado = $vToken->getUserIdFromToken($token);
		$specialUser = $userLogueado->getSpecialUser();

		if($token === false){
			return new JsonResponse(['error' => 'Token no válido']);
		}else{

			// $chargeId = $data['charge'];
			// $charge = $entityManager->getRepository(ContractCharges::class)->find($chargeId);

			$requisition = new Requisition();
			
			$currentDate= new DateTime();
			$requisition->setCurrentdate($currentDate);

			$requisition->setTypeRequisition($data['type_requisition']);
			$requisition->setTypeContract($data['type_contract'] ?? NULL);
			$requisition->setTypeAnotherif($data['type_anotherIf'] ?? NULL);
			$requisition->setNamesCharge($data['names_charge']);
			$requisition->setJustification($data['justification']);
			// $requisition->setWorkDedication($data['work_dedication']);
			// $requisition->setHours($data['hours']);
			// $requisition->setDurationContract($data['duration']);
			// $requisition->setSpecificFunctions($data['specific_functions']);
			// $requisition->setSalary($data['salary']);
			$requisition->setApprobationDirective($data['approbation_directive']);
			$requisition->setApprobationRector($data['approbation_rector']);
			$requisition->setNumberAct($data['number_act']);
			$requisition->setState(0); //Pendiente
			$requisition->setCharge(NULL);
			$requisition->setUser($userLogueado);

			// $initialDate = $data['initial_date'];
			// $finalDate = $data['final_date'];

			// if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$initialDate)){
			// 	$dateTimeInitial = new DateTime($initialDate);
			// 	$requisition -> setInitialDate($dateTimeInitial);
			// }	

			// if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finalDate)){
			// 	$dateTimeFinal = new DateTime($finalDate);
 			// 	$requisition -> setFinalDate($dateTimeFinal);
			// }
			
			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $userLogueado->getId(),
				'responsible' => $userLogueado->getSpecialUser(),
				'state' => 0,
				'message' => 'La requisición fue solicitada por '.$userLogueado->getNames()." ".$userLogueado->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$requisition->setHistory($addToHistory);

			$entityManager->persist($requisition);
			$entityManager->flush();

			// $newNotification = new Notification();
			// $newNotification->setSeen(0);
			// $relatedEntity = array(	
			// 	'id' => $requisition->getId(),
			// 	'applicantId'=>$user->getId(),
			// 	'applicantName'=>$user->getNames()." ".$user->getLastNames(),
			// 	'entity'=>'requisition'
			// );
			// $newNotification->setRelatedEntity(json_encode($relatedEntity));

			// switch($specialUser){
			// 	case 'CPSI':
			// 	case 'REC':
			// 	case 'CPB':
			// 	case 'DIRENF':
			// 	case 'DIRASS':
			// 	case 'CRCAD':
			// 	case 'AOASIC':
			// 	case 'CTH':
			// 	case 'VPSB':
			// 	case 'VAE':
			// 	case 'VII':
			// 	case 'ASIAC':
			// 		$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF','userType' => 1]);
			// 		$newNotification->setUser($userForNotification);
			// 		$newNotification->setMessage('Solicita la aprobación de una requisición.');
			// 		break;
			// 	case 'VF':
			// 		$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType'=>8]);
			// 		$newNotification->setUser($userForNotification);
			// 		$requisition->setState(1);
			// 		$newNotification->setMessage('Envia la solicitud de revinculación APROBADA por parte de Vicerrectoría Financiera.');
			// 		break;
			// }

			// $entityManager->persist($newNotification);
			// $entityManager->flush();

			if (isset($data['user_requisition']) && $data['user_requisition'] !== null) {
				// Obtenemos la requisición y el usuario
				$requisitionId = $requisition->getId();
				$requisitionEntity = $entityManager->getRepository(Requisition::class)->find($requisitionId);
				$dataUserRequisition = $entityManager->getRepository(User::class)->find($data['user_requisition']);
			
				// Creamos el objeto UsersInRequisition y lo configuramos
				$userInRequisition = new UsersInRequisition();
				$userInRequisition->setRequisition($requisitionEntity);
				$userInRequisition->setUser($dataUserRequisition);
				$userInRequisition->setState(2); //ganador revinculación
			
				// Persistimos los datos si todo está bien
				$entityManager->persist($userInRequisition);
				$entityManager->flush();
			}

		}

		return new JsonResponse(['status'=>'Success','message'=>'Requisición creada con éxito', 'idRequisition' => $requisition->getId()]);
	}
	
	// LISTAR REQUISICIÓN CON EL ID DEL USUARIO SOLICITANTE
	#[Route('contract/list-requisition/{id}', name:'app_contract_list_requisition')]
	public function listRequisition(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$user = $doctrine->getRepository(User::class)->find($id);
		if (!$user) {
            return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
        }
		$requisitions = $doctrine->getRepository(Requisition::class)->findBy(['user'=>$user]);
		if(empty($requisitions)){
			return new JsonResponse(['status'=>false,'message'=>'No se encontró una solicitud de requisición solicitada.']);
		}

		$requisitionData = [];
		foreach($requisitions as $requisition){
			//$requisitionUser = [];
			// $userInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findBy(['requisition'=>$requisition->getId()]);
			// foreach($userInRequisitions as $userInRequisition){
			// 	$userName = $userInRequisition->getUser();
			// 	if($userName){
			// 		$requisitionUser[] = [
			// 			'names' => $userName->getNames(),
			// 			'lastNames' => $userName->getLastNames()
			// 		];
			// 	}
			// }

			//---------------------------------------------------------------------------------
			//----------------------------- CAMBIOS DE REQUISICIÓN ----------------------------
			//$charge = $requisition->getCharge();
			$user = $requisition->getUser();

			$requisitionData[] = [
				'requisition'=>[
					'id' => $requisition->getId(),
					'currentDate' => $requisition->getCurrentdate() ? $requisition->getCurrentdate()->format('Y-m-d') : NULL,
					'type_requisition' => $requisition->getTypeRequisition(),
					'type_contract' => $requisition->getTypeContract(),
					'type_anotherIF' => $requisition->getTypeAnotherif(),
					'names_charge' => $requisition->getNamesCharge(),
					'justification' => $requisition->getJustification(),
					// 'work_dedication' => $requisition->getWorkDedication(),
					// 'hours' => $requisition->getHours(),
					// 'initial_date' => $requisition->getInitialDate()->format('Y-m-d'),
					// 'final_date' => $requisition->getFinalDate()->format('Y-m-d'),
					// 'duration' => $requisition->getDurationContract(),
					// 'specific_functions' => $requisition->getSpecificFunctions(),
					// 'salary' => $requisition->getSalary(),
					'approbation_directive' => $requisition->getApprobationDirective(),
					'approbation_rector' => $requisition->getApprobationRector(),
					'number_act' => $requisition->getNumberAct(),
					'state' => $requisition->getState(),
					'history' => $requisition->getHistory(),
					//'chargeName' => $charge->getName(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification(),
					'idUserRequisition' => $user->getId()
				],
				//'requisitionUser' => $requisitionUser
			];
		}

		return new JsonResponse(['status' => true, 'requisition_data' => $requisitionData]);
	}
	
	// Listar requisición con el id de la requisición
	#[Route('contract/get-requisition/{id}', name:'app_contract_get_requisition')]
	public function getRequisition(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$requisition = $doctrine->getRepository(Requisition::class)->find($id);

		if (!$requisition) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró la requisición']);
		}

		//$userInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findBy(['requisition' => $requisition]);

		//$requisitionUser = [];

		// foreach ($userInRequisitions as $userInRequisition) {
		// 	$userName = $userInRequisition->getUser();
		// 	if ($userName) {
		// 		$requisitionUser[] = [
		// 			'names' => $userName->getNames(),
		// 			'lastNames' => $userName->getLastNames()
		// 		];
		// 	}
		// }

		//$charge = $requisition->getCharge();
		$user = $requisition->getUser();

		$requisitionData = [
			'requisition' => [
				'id' => $requisition->getId(),
				'currentDate' => $requisition->getCurrentdate() ? $requisition->getCurrentdate()->format('Y-m-d') : NULL,
				'type_requisition' => $requisition->getTypeRequisition(),
				'type_contract' => $requisition->getTypeContract(),
				'type_anotherIF' => $requisition->getTypeAnotherif(),
				'names_charge' => $requisition->getNamesCharge(),
				'justification' => $requisition->getJustification(),
				// 'work_dedication' => $requisition->getWorkDedication(),
				// 'hours' => $requisition->getHours(),
				// 'initial_date' => $requisition->getInitialDate()->format('Y-m-d'),
				// 'final_date' => $requisition->getFinalDate()->format('Y-m-d'),
				// 'duration' => $requisition->getDurationContract(),
				// 'specific_functions' => $requisition->getSpecificFunctions(),
				// 'salary' => $requisition->getSalary(),
				'approbation_directive' => $requisition->getApprobationDirective(),
				'approbation_rector' => $requisition->getApprobationRector(),
				'number_act' => $requisition->getNumberAct(),
				'state' => $requisition->getState(),
				'history' => $requisition->getHistory(),
				//'chargeName' => $charge->getName(),
				'username' => $user->getNames().' '.$user->getLastNames(),
				'userIdentification' => $user->getIdentification(),
				'idUserRequisition' => $user->getId()
			],
			//'requisitionUser' => $requisitionUser
		];

    	return new JsonResponse(['status' => true, 'requisition_data' => $requisitionData]);
	}

	#[Route('contract/approve-requisition', name:'app_approve_requisition')]
	public function approveRequisition(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request) : JsonResponse
	{
		$token = $request->query->get('token');
		$requisitionId = $request->query->get('requisitionId');
		$notificationId = $request->query->get('notificationId');
		$applicantId = $request->query->get('applicant');

		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$requisition = $doctrine->getRepository(Requisition::class)->find($requisitionId);
		if($requisition === NULL){
			return new JsonResponse(['message'=>'No existe una requisición'],400,[]);
		}
		$newStateForRequisition = 0;
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicantId);
		$userNames = $userNames->getNames()." ".$userNames->getLastNames();

		$relatedEntity = array(
			'id'=>$requisitionId,
			'applicantId'=>$applicantId,
            'applicantName'=>$userNames,
			'entity'=>'requisition'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'VF':
				$newStateForRequisition = 1;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('Envia la aprobación de la requisición solicitada');
				break;
			case 'CTH':
				$userWhoMadeRequisition = $requisition->getUser();
				$newNotification->setUser($userWhoMadeRequisition);
				$newNotification->setMessage('Revisión de requisición finalizada.');
				break;
			// case 'REC':
			// 	$newStateForRequisition = 3;
			// 	$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
            //     $newNotification->setUser($userForNotification);
            //     $newNotification->setMessage('Finalización de requisición exitosa.');
			// 	break;

			// default:
			// 	$newStateForRequisition = 1;
			// 	$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF', 'userType'=>1]);
			// 	$newNotification->setUser($userForNotification);
			// 	$newNotification->setMessage('solicita la aprobación de una requisición por parte de Vicerrectoría Financiera');
			// 	break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$requisition->setState($newStateForRequisition);
		$history = $requisition->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => $newStateForRequisition,
			'message' => 'La requisición fue aprobada por '.$user->getNames()." ".$user->getLastNames(),
			'date' => date('Y-m-d H:i:s'),
		));
		$newHistory = rtrim($history, ']').','.$addToHistory.']';
		$requisition->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
		$entityManager->flush();
		return new JsonResponse(['message'=> 'Se ha aprobado la requisición con el id'. $requisitionId]);
		
	}

	#[Route('contract/reject-requisition', name:'app_reject_requisition')]
	public function rejectRequisition(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$requisitionId= $request->query->get('requisitionId');
		$rejectText = $request->request->get('rejectText');
		$applicantId = $request->query->get('applicant');
		$notificationId = $request->query->get('notificationId');

		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$requisition = $doctrine->getRepository(Requisition::class)->find($requisitionId);
		if($requisition === NULL){
			return new JsonResponse(['message' => 'No existe ninguna requisición solicitada'], 400, []);
		}
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicantId);
		$userNames = $userNames->getNames()." ".$userNames->getLastNames();

		$relatedEntity = array(
			'id' => $requisitionId,
			'applicantId'=>$applicantId,
            'applicantName'=>$userNames,
			'entity' => 'requisition'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'VF':
                $newNotification->setMessage('Requisición rechazada por Vicerrectoria financiera');
				$userWhoMadeLicense = $requisition->getUser();
				$newNotification->setUser($userWhoMadeLicense);
				break;
			// case 'REC':
            //     $newNotification->setMessage('Requisición rechazada por Rectoría');
			// 	$userWhoMadeLicense = $requisition->getUser();
			// 	$newNotification->setUser($userWhoMadeLicense);
			// 	break;
			// case 'CTH':
            //     $newNotification->setMessage('Requisición rechazada por Talento humano');
			// 	$userWhoMadeLicense = $requisition->getUser();
			// 	$newNotification->setUser($userWhoMadeLicense);
			// 	break;
			// default:
			// 	$newNotification->setMessage('Requisición rechazada por Jefe inmediato');
			// 	$userWhoMadeRequisition = $requisition->getUser();
			// 	$newNotification->setUser($userWhoMadeRequisition);
			// 	break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);

		$requisition->setState(2); //Rechazada por VF
		$history = $requisition->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => 2,
			'message' => 'La requisición fue rechazada por'.$user->getNames()." ".$user->getLastNames(),
			'userInput' => $rejectText,
            'date' => date('Y-m-d H:i:s'),
		));
		$newHistory= rtrim($history, ']').','.$addToHistory.']';
		$requisition->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha rechazado la requisición con el id '.$requisitionId], 200, []);
	}

	#[Route('contract/all-requisitions', name: 'app_contract_all_requisitions')]
	public function allRequisitions(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$requisitionData = [];
		
		$userLogueado = $vToken->getUserIdFromToken($token);
		
		if($userLogueado === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		} else {
			// Obtén el repositorio de la entidad Requisition
			$requisitionRepository = $doctrine->getRepository(Requisition::class);
			
			// Filtra las requisiciones del usuario logueado
			$requisitions = $requisitionRepository->findBy(['user' => $userLogueado]);
			
			if (empty($requisitions)) {
				return new JsonResponse(['status' => false, 'message' => 'No se encontraron requisiciones.']);
			}
	
			foreach ($requisitions as $requisition) {
				$user = $requisition->getUser();
	
				$requisitionData[] = [
					'id' => $requisition->getId(),
					'currentDate' => $requisition->getCurrentdate() ? $requisition->getCurrentdate()->format('Y-m-d') : NULL,
					'type_requisition' => $requisition->getTypeRequisition(),
					'type_contract' => $requisition->getTypeContract(),
					'type_anotherIF' => $requisition->getTypeAnotherif(),
					'names_charge' => $requisition->getNamesCharge(),
					'justification' => $requisition->getJustification(),
					'approbation_directive' => $requisition->getApprobationDirective(),
					'approbation_rector' => $requisition->getApprobationRector(),
					'number_act' => $requisition->getNumberAct(),
					'state' => $requisition->getState(),
					'history' => $requisition->getHistory(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification()
				];
			}
		}
		return new JsonResponse(['status' => true, 'requisition_data' => $requisitionData]);
	}
	
	#[Route('contract/all-user-in-requisition', name:'app_contract_all_user_in_requisition')]
	public function allUserInRequisition(ManagerRegistry $doctrine, Request $request): JsonResponse
	{
		$token = $request->query->get('token');
		$UserInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findAll();
		$entityManager = $doctrine->getManager();
	
		if($token === false){
			return new JsonResponse(['ERROR'=>'Token no válido']);
		}else{
			if (empty($UserInRequisitions)) {
				return new JsonResponse(['status' => false, 'message' => 'No se encontraron usuarios en requisiciones.']);
			}
	
			$requisitionUser = [];
			foreach ($UserInRequisitions as $userInRequisition) {
				$user = $userInRequisition->getUser();
				$requisition = $userInRequisition->getRequisition();
				$state = $userInRequisition->getState();
	
				$existingDirectContract = $entityManager->getRepository(DirectContract::class)->findOneBy([
					'requisition' => $requisition
				]);
	
				if ($user) {
					$userData = [
						'user' => $user->getNames().' '.$user->getLastNames(),
						'userId' => $user->getId(),
						'email' => $user->getEmail(),
						'phone' => $user->getPhone(),
						'typeIdentification' => $user->getTypeIdentification(),
						'identification' => $user->getIdentification(),
						'stateUsersInRequisition' => $state,
					];
	
					if ($existingDirectContract) {
						$userData = array_merge($userData, [
							'id' => $existingDirectContract->getId(),
							'work_dedication' => $existingDirectContract->getWorkDedication(),
							'hours' => $existingDirectContract->getHours(),
							'initial_date' => $existingDirectContract->getInitialDate()->format('Y-m-d'),
							'final_date' => $existingDirectContract->getFinalDate()->format('Y-m-d'),
							'duration' => $existingDirectContract->getDurationContract(),
							'specific_functions' => $existingDirectContract->getSpecificFunctions(),
							'salary' => $existingDirectContract->getSalary(),
							'solicitude_date' => $existingDirectContract->getSolicitudeDate()->format('Y-m-d'),
							'state' => $existingDirectContract->getState(),
							'history' => json_decode($existingDirectContract->getHistory(), true),
							'chargeId' => $existingDirectContract->getCharge()->getId(),
							'chargeName' => $existingDirectContract->getCharge()->getName(),
							'typeEmployee' => $existingDirectContract->getCharge()->getTypeEmployee(),
							'profileId' => $existingDirectContract->getProfile()->getId(),
							'profileName' => $existingDirectContract->getProfile()->getName(),
							'type_requisition' => $requisition->getTypeRequisition(),
							'type_contract' => $requisition->getTypeContract(),
							'type_anotherIF' => $requisition->getTypeAnotherif(),
							'names_charge' => $requisition->getNamesCharge(),
							'justification' => $requisition->getJustification(),
						]);
					}
	
					$requisitionUser[] = $userData;
				}
			}
	
			return new JsonResponse(['status' => true, 'requisition_data' => $requisitionUser]);
		}
	}

	#[Route('contract/list-user-in-requisition/{id}', name:'app_contract_list_user_in_requisition')]
	public function listUserInRequisition(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
	{
		$token = $request->query->get('token');
		$user = $doctrine->getRepository(User::class)->find($id);
		$entityManager = $doctrine->getManager();

		if($token === false){
			return new JsonResponse(['ERROR'=>'Token no válido']);
		}else{
			if(!$user){
				return new JsonResponse(['status'=>false, 'message'=>'No se encontró el usuario']);
			}
			$usersInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findBy(['user' => $user]);
			if(empty($usersInRequisitions)){
				return new JsonResponse(['status'=>false,'message'=>'No se encontró usuarios en requisiciones']);
			}else{
				foreach ($usersInRequisitions as $userInRequisition) {
					$user = $userInRequisition->getUser();
					$requisition = $userInRequisition->getRequisition();
					$state = $userInRequisition->getState();
	
					
					$existingDirectContract = $entityManager->getRepository(DirectContract::class)->findOneBy([
						'requisition' => $requisition
					]);
	
					if ($user) {
						$requisitionUser = [
							'user' => $user->getNames().' '.$user->getLastNames(),
							'userId' => $user->getId(),
							'email' => $user->getEmail(),
							'phone' => $user->getPhone(),
							'typeIdentification' => $user->getTypeIdentification(),
							'identification' => $user->getIdentification(),

							'id' => $existingDirectContract->getId(),
							'work_dedication' => $existingDirectContract->getWorkDedication(),
							'hours' => $existingDirectContract->getHours(),
							'initial_date' => $existingDirectContract->getInitialDate()->format('Y-m-d'),
							'final_date' => $existingDirectContract->getFinalDate()->format('Y-m-d'),
							'duration' => $existingDirectContract->getDurationContract(),
							'specific_functions' => $existingDirectContract->getSpecificFunctions(),
							'salary' => $existingDirectContract->getSalary(),
							'solicitude_date' => $existingDirectContract->getSolicitudeDate()->format('Y-m-d'),
							'state' => $existingDirectContract->getState(),
							'history' => json_decode($existingDirectContract->getHistory(),true),
							'chargeId' => $existingDirectContract->getCharge()->getId(),
							'chargeName' => $existingDirectContract->getCharge()->getName(),
							'typeEmployee' => $existingDirectContract->getCharge()->getTypeEmployee(),
							'profileId' => $existingDirectContract->getProfile()->getId(),
							'profileName' => $existingDirectContract->getProfile()->getName(),
							'type_requisition' => $requisition->getTypeRequisition(),
							'type_contract' => $requisition->getTypeContract(),
							'type_anotherIF' => $requisition->getTypeAnotherif(),
							'names_charge' => $requisition->getNamesCharge(),
							'justification' => $requisition->getJustification(),
							'stateUsersInRequisition' => $state,
						];
					}
				}
			}
		}
		return new JsonResponse(['status' => true, 'requisition_data' => $requisitionUser]);
	}
	//-------------------------------------------------------------------------------------------
	//-----------------------------Reemployment-------------------------------------------------
	#[Route('/contract/create-reemployment', name:'app_contract_create_reemployment')]
	public function createReemploymen(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token= $request->query->get('token');
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		$userLogueado = $vToken->getUserIdFromToken($token);
		$specialUser = $userLogueado->getSpecialUser();

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException('No user found for id' . $data['id']);
			}
			$chargeId = $entityManager->getRepository(ContractCharges::class)->find($data['chargeId']);
			if(!$chargeId){
				throw $this->createNotFoundException('No charge found for id' . $data['id']);
			}
			$profileId = $entityManager->getRepository(Profile::class)->find($data['profileId']);
			if(!$profileId){
				throw $this->createNotFoundException('No profile found for id' . $data['id']);
			}

			$initialDate = $data['initial_date'];
			$finalDate = $data['final_date'];
	
			$reemployment = new Reemployment();
			$solicitudeDate = new DateTime();
			$reemployment -> setSolicitudeDate($solicitudeDate);

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$initialDate)){
				$dateTimeInitial = new DateTime($initialDate);
				$reemployment -> setInitialDate($dateTimeInitial);
			}	

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finalDate)){
				$dateTimeFinal = new DateTime($finalDate);
 				$reemployment -> setFinalDate($dateTimeFinal);
			}

			$reemployment -> setWorkDedication($data['workDedication']);
			$reemployment -> setSalary($data['salary']);
			$reemployment -> setHours($data['hours']);
			$reemployment -> setState(0);
			$reemployment -> setStateUser(3);
			$reemployment -> setStateContract(0);

			$currentYear = date('Y');
			$currentMonth = date('m');

			if ($currentMonth >= '1' && $currentMonth <= '6') {
				
				$reemployment->setPeriod('A' . $currentYear);
			} elseif ($currentMonth >= '7' && $currentMonth <= '12') {
				$reemployment->setPeriod('B' . $currentYear);
			}

			//$reemployment -> setPeriod('A2024');
			$reemployment -> setCharges($chargeId);
			$reemployment -> setProfile($profileId);
			$reemployment -> setUser($user);

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $userLogueado->getId(),
				'responsible' => $userLogueado->getSpecialUser(),
				'state' => $specialUser === 'VF' ? 1 : 0,
				'message' => 'La solicitud de revinculación del personal fue solicitado por '.$userLogueado->getNames()." ".$userLogueado->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$reemployment->setHistory($addToHistory);

			$entityManager->persist($reemployment);
			$entityManager->flush();
			
			return new JsonResponse(['message'=>'Revinculación solicitada con exito.'],200,[]);

		}
	}

	#[Route('/contract/update-reemployment', name:'app_contract_reemployment_update')]
	public function updateReemployment(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken) : JsonResponse
	{
		$token = $request->query->get('token');
		$entityManager = $doctrine->getManager();
        $data = $request->request->all();
		$userLogueado = $vToken->getUserIdFromToken($token);
		$specialUser = $userLogueado->getSpecialUser();

		$reemployment = $entityManager->getRepository(Reemployment::class)->find($data['id']);

		if(!$reemployment){
			throw $this->createNotFoundException(
				'No reemployment found for id'.$data['id']
			);
		}

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException('No user found for id' . $data['id']);
			}
			$chargeId = $entityManager->getRepository(ContractCharges::class)->find($data['chargeId']);
			if(!$chargeId){
				throw $this->createNotFoundException('No level found for id' . $data['id']);
			}
			$profileId = $entityManager->getRepository(Profile::class)->find($data['profileId']);
			if(!$profileId){
				throw $this->createNotFoundException('No profile found for id' . $data['id']);
			}

			$initialDate = $data['initial_date'];
			$finalDate = $data['final_date'];

			$solicitudeDate = new DateTime();
			$reemployment -> setSolicitudeDate($solicitudeDate);

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$initialDate)){
				$dateTimeInitial = new DateTime($initialDate);
				$reemployment -> setInitialDate($dateTimeInitial);
			}	

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finalDate)){
				$dateTimeFinal = new DateTime($finalDate);
 				$reemployment -> setFinalDate($dateTimeFinal);
			}

			$reemployment -> setWorkDedication($data['workDedication']);
			$reemployment -> setSalary($data['salary']);
			$reemployment -> setHours($data['hours']);
			$reemployment -> setState(0);
			$reemployment -> setStateUser(3);
			$reemployment -> setStateContract(0);
			$reemployment -> setPeriod('A2024');
			$reemployment -> setCharges($chargeId);
			$reemployment -> setProfile($profileId);
			$reemployment -> setUser($user);

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $userLogueado->getId(),
				'responsible' => $userLogueado->getSpecialUser(),
				'state' =>  $specialUser === 'VF' ? 1 : 0,
				'message' => 'Actualización de revinculación del personal fue solicitado por '.$userLogueado->getNames()." ".$userLogueado->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$reemployment->setHistory($addToHistory);

			$entityManager->persist($reemployment);
			$entityManager->flush();
			
			return new JsonResponse(['message'=>'Actualización de revinculación exitosa.'],200,[]);

		}
	}
	#[Route('/contract/delete-reemployment/{id}', name:'app_contract_reemployment_delete')]
	public function deleteReemployment(ManagerRegistry $doctrine,Request $request, int $id): JsonResponse
	{
		$token = $request->query->get('token');
		$entityManager = $doctrine->getManager();
		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$reemployment = $entityManager->getRepository(Reemployment::class)->find($id);

			if(!$reemployment){
				throw $this->createNotFoundException(
					'No reemployment found for id'.$id['id']
				);	
			}

			$entityManager->remove($reemployment);
			$entityManager->flush();
		}

		return new JsonResponse(['status' => 'Success', 'code' => '200', 'message' => 'Usuario revinculado eliminado']);
	}
	#[Route('/contract/list-reemployment', name:'app_contract_reemployment_list')]
	public function listReemployment(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken ) : JsonResponse
	{
		$token = $request->query->get('token');
		$typeReemployment = $request->query->get('typeReemployment');

		$userLogueado = $vToken->	getUserIdFromToken($token);
		$specialUser = $userLogueado->getSpecialUser();

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{

		$queryUser = $doctrine->getManager()->createQueryBuilder();
		$queryUser
			->select('r')
			->from(Reemployment::class, 'r')
			->leftJoin('r.charges', 'cc') 
			->where('r.finalDate >= :expirationPeriod')
			->setParameters([
				'expirationPeriod' => date('Y-m-d')
			]);
		// if( $typeReemployment === 'CTH') {
		// 	$queryUser->leftjoin('r.user', 'u')
		// 		->leftjoin(Contract::class, 'co', 'WITH', 'co.user = u.id')
		// 		->andWhere('co.state = 0')
		// 		->andWhere('co.expirationContract >= :expirationPeriod ');
		// }
		$reemploymentsUsers = $queryUser->getQuery()->getResult();

		if (empty($reemploymentsUsers)) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró una lista de revinculación']);
		}

		foreach($reemploymentsUsers as $reemployment){
			$user = $reemployment->getUser();
			$workDedication = $reemployment->getWorkDedication();

			$response[] = [
				'id' => $reemployment->getId(),
				'period' => $reemployment->getPeriod(),
				'solicitude_date' => $reemployment->getSolicitudeDate()->format('Y-m-d'),
				'initial_date' => $reemployment->getInitialDate()->format('Y-m-d'),
				'final_date' => $reemployment->getFinalDate()->format('Y-m-d'),
				'hours' => $reemployment->getHours(),
				'salary' => $reemployment->getSalary(),
				'state' => $reemployment->getState(),
				'stateUser' => $reemployment->getStateUser(),
				'stateContract' => $reemployment->getStateContract(),
				'chargeId' => $reemployment->getCharges()->getId(),
				'chargeName' => $reemployment->getCharges()->getName(),
				'chargeWorkDedication' => $workDedication,
				'chargeSalary' => $reemployment->getCharges()->getSalary(),
				'typeEmployee' => $reemployment->getCharges()->getTypeEmployee(),
				'profileId' => $reemployment->getProfile()->getId(),
				'profileName' => $reemployment->getProfile()->getName(),
				'user' => $user->getNames().' '.$user->getLastNames(),
				'userId' => $user->getId(),
				'specialUser' => $user->getSpecialUser(),
				'identification' => $user->getIdentification(),
				'email' => $user->getEmail(),
				'phone' => $user->getPhone(),
				'history' => json_decode($reemployment->getHistory(), true)
			];
		}
		$filtered =  array_filter($response, function($var) use($typeReemployment){
			switch($typeReemployment){
				case 'VF':
					return $var['typeEmployee'] === 'AD';
				case 'DIRENF':
					return $var['typeEmployee'] === 'PR' && $var['history'][0]['responsible'] === 'DIRENF';
				case 'DIRASS':
					return $var['typeEmployee'] === 'PR' && $var['history'][0]['responsible'] === 'DIRASS';
				case 'VAE':
					return $var['typeEmployee'] === 'PR' && $var['history'][0]['responsible'] === 'VAE';
				case 'CTH':
					return $var['state'] === 1;
			}
		});
	}
		return new JsonResponse(['status' => true, 'reemployment' => array_values($filtered)]);
	}

	//LISTA SIN FILTROS DE LA ENTIDAD REEMPLOYMENT.
	#[Route('/contract/allReemployments', name:'app_contract_all_reemployments')]
	public function listAllReemploymenst(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$userLogueado = $vToken->getUserIdFromToken($token);
		$specialUser = $userLogueado->getSpecialUser();

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{

			$queryUser = $doctrine->getManager()->createQueryBuilder();
			$queryUser
				->select('r')
				->from(Reemployment::class, 'r')
				->leftJoin('r.charges', 'cc') 
				->where('r.finalDate >= :expirationPeriod')
				->setParameters([
					'expirationPeriod' => date('Y-m-d')
				]);

			$reemploymentsUsers = $queryUser->getQuery()->getResult();

			if(empty($reemploymentsUsers)){
				return new JsonResponse(['status'=>false, 'message'=>'No existen registros de revinculación']);
			}
			foreach($reemploymentsUsers as $reemployment){
				$user = $reemployment->getUser();
				$workDedication = $reemployment->getWorkDedication();

				$reemploymentData[] = [
					'id' => $reemployment->getId(),
					'period' => $reemployment->getPeriod(),
					'solicitude_date' => $reemployment->getSolicitudeDate()->format('Y-m-d'),
					'initial_date' => $reemployment->getInitialDate()->format('Y-m-d'),
					'final_date' => $reemployment->getFinalDate()->format('Y-m-d'),
					'hours' => $reemployment->getHours(),
					'salary' => $reemployment->getSalary(),
					'state' => $reemployment->getState(),
					'stateUser' => $reemployment->getStateUser(),
					'stateContract' => $reemployment->getStateContract(),
					'chargeId' => $reemployment->getCharges()->getId(),
					'chargeName' => $reemployment->getCharges()->getName(),
					'chargeWorkDedication' => $workDedication,
					'chargeSalary' => $reemployment->getCharges()->getSalary(),
					'typeEmployee' => $reemployment->getCharges()->getTypeEmployee(),
					'profileId' => $reemployment->getProfile()->getId(),
					'profileName' => $reemployment->getProfile()->getName(),
					'user' => $user->getNames().' '.$user->getLastNames(),
					'userId' => $user->getId(),
					'specialUser' => $user->getSpecialUser(),
					'identification' => $user->getIdentification(),
					'email' => $user->getEmail(),
					'phone' => $user->getPhone(),
					'history' => json_decode($reemployment->getHistory(), true)
	
				];
			}
		}
		return new JsonResponse(['status'=>true, 'allReemployments' => $reemploymentData]);
	}

	//LISTAR DATOS DE REVINCULACIÓN DE UN USUARIO CON EL ID DEL USUARIO
	#[Route('/contract/list-reemployment/{id}', name:'app_contract_reemployment_listUser')]
	public function listReemploymentUser(ManagerRegistry $doctrine, Request $request, int $id ) : JsonResponse
	{
		$token = $request->query->get('token');
		$user = $doctrine->getRepository(User::class)->find($id);
		
		if(!$user){
			return new JsonResponse(['status' => false, 'message' => 'No se encontró el usuario']);
		}

		$query = $doctrine->getManager()->createQueryBuilder();
		$query
			->select('c')
			->from('App\Entity\Reemployment', 'c')
			->where('c.finalDate >= :expirationPeriod')
			->andWhere('c.user = :user')
			->setParameters(array('expirationPeriod'=>date('Y-m-d'), 'user'=>$user));
		$reemployment = $query->getQuery()->getResult();
		
		if (empty($reemployment)){
			return new JsonResponse(['status'=>false,'message'=>'No tiene revinculación activa']);
		}

		$reemployments = $doctrine->getRepository(Reemployment::class)->findBy(['user'=>$user]);
		if(empty($reemployments)){
			return new JsonResponse(['status'=>false,'message'=>'No se encontró solicitud de revinculación']);
		}

		// $queryContract = $doctrine->getManager()->createQueryBuilder();
		// $queryContract
		// 	->select('c')
		// 	->from('App\Entity\Contract', 'c')
		// 	->where('c.expirationContract >= :expirationDate')
		// 	->andWhere('c.user = :user')
		// 	->setParameters(array('expirationDate'=> date('Y-m-d'), 'user' => $user));
		// $contract = $queryContract->getQuery()->getResult();
        // if ($contract) {
		// 	$activedReemployment = $doctrine->getRepository(Reemployment::class)->findOneBy(['user' => $user]);
		// 	$activedReemployment->setState(2);
		// 	$doctrine->getManager()->persist($activedReemployment);
		// 	$doctrine->getManager()->flush();
        // }

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$response = [];

			foreach($reemployments as $reemployment){
				$user = $reemployment->getUser();
				$workDedication = $reemployment->getWorkDedication();
				// if($workDedication === 'TC'){$workDedication = 'Tiempo Completo';}
				// else{$workDedication = 'Medio Tiempo';}

				$response = [
					'id' => $reemployment->getId(),
					'period' => $reemployment->getPeriod(),
					'solicitude_date' => $reemployment->getSolicitudeDate()->format('Y-m-d'),
					'initial_date' => $reemployment->getInitialDate(),
					'final_date' => $reemployment->getFinalDate(),
					'hours' => $reemployment->getHours(),
					'salary' => $reemployment->getSalary(),
					'state' => $reemployment->getState(),
					'stateUser' => $reemployment->getStateUser(),
					'stateContract' => $reemployment->getStateContract(),
					'history' => json_decode($reemployment->getHistory(), true),
					'chargeId' => $reemployment->getCharges()->getId(),
					'chargeName' => $reemployment->getCharges()->getName(),
					'typeEmployee' => $reemployment->getCharges()->getTypeEmployee(),
					'chargeWorkDedication' => $workDedication,
					'chargeSalary' => $reemployment->getCharges()->getSalary(),
					'profileId' => $reemployment->getProfile()->getId(),
					'profileName' => $reemployment->getProfile()->getName(),
					'user' => $user->getNames().' '.$user->getLastNames(),
					'userId' => $user->getId(),
					'identification' => $user->getIdentification()
				];
			}
		}
		return new JsonResponse(['status' => true, 'reemployment' => $response]);
	}
	#[Route('/contract/saveReemployments', name:'app_save_reemployments')]
	public function saveUserReemployments(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request) : JsonResponse
	{
		$token = $request->query->get('token');
		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$entityManager = $doctrine->getManager();
		$idsToChange = json_decode($request->getContent(), true);
		
		foreach ($idsToChange as $id) {
			$reemployment = $doctrine->getRepository(Reemployment::class)->find($id);
			//TODO: Revisar
			if (!$reemployment) {
				return new JsonResponse(['status' => false, 'message' => 'No se encontró solicitud de revinculación para el id ' . $id]);
			}
			$reemployment->setState($specialUser === 'VF' ? 1 : 0 );
		}

		//TODO: Revisar
		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$newNotification = new Notification();
			$newNotification->setSeen(0);
			$relatedEntity = array(
				'applicantId'=>$user->getId(),
				'applicantName'=>$user->getNames()." ".$user->getLastNames(),
				'entity'=>'reemployment'
			);
			$newNotification->setRelatedEntity(json_encode($relatedEntity));

			switch($specialUser){
				case 'VF':
					$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
					$newNotification->setUser($userForNotification);
					$newNotification->setMessage('Listado de revinculación personal administrativo.');							
					break;
				case 'DIRENF':
				case 'DIRASS':
				case 'VAE':
					$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF','userType' => 1]);
					$newNotification->setUser($userForNotification);
					$newNotification->setMessage('Listado de revinculación personal docente.');
					break;					
				}

			$entityManager->persist($newNotification);
		}
		$entityManager->flush();
		return new JsonResponse(['status'=>'Success','message'=>'Lista de revinculación enviada con exito']);
	}
	#[Route('/contract/approve-reemploymentsTeachers', name:'app_approve_reemploymentsTeachers')]
	public function approveReemploymentsTeachers(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
	{
		$token = $request->query->get('token');
		$notificationId = $request->query->get('notificationId');
		$applicantId = $request->query->get('applicantId');
		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$idReemployment = json_decode($request->getContent(), true);

		$reemployment = $doctrine->getRepository(Reemployment::class)->find($idReemployment);
		$userSelected = $reemployment->getUser();
		$namesUserSelected = $userSelected->getNames().''.$userSelected->getLastNames();
	
		if (!$reemployment) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró solicitud de revinculación para el id ' . $idReemployment]);
		}
		$reemployment->setState(1);
		$reemployment->setStateUser(3);

		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicantId);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'applicantId'=>$applicantId,
			'applicantName'=>$userNames,
			'entity'=>'reemployment'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'DIRENF':
			case 'DIRASS':
			case 'VAE':
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF','userType'=>1]);
				$newNotification->setUser($userForNotification);
				$newNotification->setMessage('Solicita la aprobación del profesor.'. $namesUserSelected);
				break;
			case 'VF':
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType'=>8]);
				$newNotification->setUser($userForNotification);
				$newNotification->setMessage('Aprobación del usuario.'. $namesUserSelected);
				break;
			return new JsonResponse(['message'=>'Usuario no autorizado'],403,[]);
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);
		$history = $reemployment->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => 1, //Aprobado por VF
			'message' => 'La revinculación del trabajador fue aprobado por '.$user->getNames()." ".$user->getLastNames(),
			'date' => date('Y-m-d H:i:s'),
		));
		$newHistory = rtrim($history, ']').','.$addToHistory.']';
		$reemployment->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
		$entityManager->flush();
		return new JsonResponse(['message'=>'Se ha aprobado la revinculación exitosamente']);

	}
	#[Route('contract/reject-reemploymentsTeachers', name:'app_reject_reemploymentsTeachers')]
	public function rejectReemploymentsTeachers(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$notificationId = $request->query->get('notificationId');
		$applicantId = $request->query->get('applicantId');
		$rejectText = $request->request->get('rejectText');

		$user = $vToken->getUserIdFromToken($token);
		$specialUser = $user->getSpecialUser();
		$idRejected = $request->request->get('idRejected');

		$reemployment = $doctrine->getRepository(Reemployment::class)->find($idRejected);
	
		if (!$reemployment) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró solicitud de revinculación para el id ' . $idRejected]);
		}

		$reemployment->setState(3); //Rechazado por VF
		$newNotification = new Notification();
		$newNotification->setSeen(0);
		$userNames = $doctrine->getRepository(User::class)->find($applicantId);
	
		$userNames= $userNames->getNames();
		$relatedEntity = array(
			'applicantId'=>$applicantId,
			'applicantName'=>$userNames,
			'entity'=>'reemployment'
		);
		$newNotification->setRelatedEntity(json_encode($relatedEntity));
		switch($specialUser){
			case 'VF':
				$userForNotification = $doctrine->getRepository(User::class)->find($applicantId);
				$newNotification->setUser($userForNotification);
				$newNotification->setMessage('Revinculación del trabajador.'.$reemployment->getUser()->getNames().' rechazada');
				break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(0);
		$history = $reemployment->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => 3, //Rechazado por VF
			'message' => 'La revinculación del trabajador fue rechazada por '.$user->getNames()." ".$user->getLastNames(),
			'userInput'=> $rejectText,
			'date' => date('Y-m-d H:i:s'),
		));
		$newHistory = rtrim($history, ']').','.$addToHistory.']';
		$reemployment->setHistory($newHistory);
		$entityManager = $doctrine->getManager();
		$entityManager->persist($newNotification);
		$entityManager->flush();
		return new JsonResponse(['message'=>'Se ha rechazado la revinculación del personal docente']);
	}

	#[Route('/contract/listWorkers/{typeId}', name: 'app_list_teachers')]
	public function workers(int $typeId,ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$user = $vToken->getUserIdFromToken($token);
		$userLogueado = $user->getSpecialUser();

		$users = $doctrine->getRepository(User::class)->findBy(['userType' => $typeId]);
	
		$userData = [];
		foreach ($users as $user) {
			$userData[] = [ 
				'id' => $user->getId(),
				'names' => $user->getNames(),
				'last_names' => $user->getLastNames(),
				'type_identification' => $user->getTypeIdentification(),
				'identification' => $user->getIdentification(),
				'email' => $user->getEmail(),
				'phone' => $user->getPhone(),
				'url_photo' => $user->getUrlPhoto(),
				'user_type' => $user->getUserType(),
				'special_user' => $user->getSpecialUser()
			];
		}
		return new JsonResponse($userData);
	}

	//----------------------------------------------------------------------------------------
	//----------------------------------------------------------------------------------------
	// DIRECT-CONTRACT
	#[Route('/contract/create-direct-contract', name:'app_create_direct_contract')]
	public function createDirectContract(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken) : JsonResponse
	{ 
		$token = $request->query->get('token');
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

    	$userLogueado = $vToken->getUserIdFromToken($token);
		$specialUser = $userLogueado->getSpecialUser();

		if($token === false){
			return new JsonResponse(['error' => 'Token no válido']);
		}else{

			$chargeId = $entityManager->getRepository(ContractCharges::class)->find($data['chargeId']);
			if(!$chargeId){
				throw $this->createNotFoundException('No level found for id' . $data['id']);
			}
			$profileId = $entityManager->getRepository(Profile::class)->find($data['profileId']);
			if(!$profileId){
				throw $this->createNotFoundException('No profile found for id' . $data['id']);
			}

			$requisitionId = $data['requisitionId'];
			$requisition = $entityManager->getRepository(Requisition::class)->find($requisitionId);

			$requisition->setState(3); //requisición efectuada

			$directContract = new DirectContract();
			$currentDate = new DateTime();

			$initialDate = $data['initial_date'];
			$finalDate = $data['final_date'];

			$solicitudeDate = new DateTime();
			$directContract -> setSolicitudeDate($solicitudeDate);

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$initialDate)){
				$dateTimeInitial = new DateTime($initialDate);
				$directContract -> setInitialDate($dateTimeInitial ?? NULL);
			}	

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finalDate)){
				$dateTimeFinal = new DateTime($finalDate);
 				$directContract -> setFinalDate($dateTimeFinal ?? NULL);
			}

			$directContract->setSolicitudeDate($currentDate);
			$directContract->setWorkDedication($data['work_dedication']);
			$directContract->setHours($data['hours']);
			$directContract->setDurationContract($data['duration']);
			$directContract->setSalary($data['salary']);
			$directContract->setSpecificFunctions($data['specific_functions'] ?? NULL);
			$directContract->setUser($userLogueado);
			$directContract->setRequisition($requisition);
			$directContract->setCharge($chargeId);
			$directContract->setProfile($profileId);

			// Verificamos si el usuario especial es 'VF'
			if ($specialUser === 'VF') {
				$directContract->setState(1); //Activa
				$message = 'La contratación directa fue aprobada por Vicerrectoria Financiera y solicitada por ' . $userLogueado->getNames() . ' ' . $userLogueado->getLastNames();
			} else {
				$directContract->setState(0); //Pendiente aprobación por VF
				$message = 'La contratación directa fue solicitada por ' . $userLogueado->getNames() . ' ' . $userLogueado->getLastNames();
			}

      		date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $userLogueado->getId(),
				'responsible' => $userLogueado->getSpecialUser(),
				'state' => $specialUser === 'VF' ? 1 : 0,
				'message' => $message,
				'date' => date('Y-m-d H:i:s'),
			)));
			$directContract->setHistory($addToHistory);

			$entityManager->persist($directContract);
			$entityManager->flush();

			$newNotification = new Notification();
			$newNotification->setSeen(0);
			$relatedEntity = array(
				'id' => $directContract->getId(),
				'applicantId' => $userLogueado->getId(),
				'applicantName'=>$userLogueado->getNames()." ".$userLogueado->getLastNames(),
				'entity'=>'directContract'
			);
			$newNotification->setRelatedEntity(json_encode($relatedEntity));
			switch($specialUser){
				case 'CPSI':
				case 'REC':
				case 'CPB':
				case 'DIRENF':
				case 'DIRASS':
				case 'CRCAD':
				case 'AOASIC':
				case 'CTH':
				case 'VPSB':
				case 'VAE':
				case 'VII':
				case 'ASIAC':
					$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF','userType' => 1]);
					$newNotification->setUser($userForNotification);
					$newNotification->setMessage('Solicita la aprobación de una solicitud de contratación directa.');
					break;
				case 'VF':
					$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType'=>8]);
					$newNotification->setUser($userForNotification);
					$newNotification->setMessage('Solicitud de contratación directa APROBADA - Revisar módulo de contratación');
					break;
			}

			$entityManager->persist($newNotification);

			// Obtenemos la requisición y el usuario
			$dataUserRequisition = $entityManager->getRepository(User::class)->find($data['user']);
			$existingUserInRequisition = $entityManager->getRepository(UsersInRequisition::class)->findOneBy([
				'requisition' => $requisition
			]);

			// Si existe la relación, actualizamos el usuario; si no, lanzamos una excepción
			if ($existingUserInRequisition) {
				$existingUserInRequisition->setUser($dataUserRequisition);
				if ($specialUser === 'VF') {
					$existingUserInRequisition->setState(1);
				}
				$entityManager->persist($existingUserInRequisition);
			} else {
				throw $this->createNotFoundException('No se encontró UsersInRequisition para la requisición dada.');
			}

			$entityManager->flush();

		}
    	return new JsonResponse(['status'=>'Success','message'=>'Contratación directa creada con éxito']);
	}

	//LISTAR DATOS DE CONTRATACIÓN DIRECTA CON EL ID DEL USUARIO
	#[Route('/contract/list-direct-contract/{id}', name:'app_contract_list_direct_contract')]
	public function listDirectContract(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
	{
		$token = $request->query->get('token');
		$user = $doctrine->getRepository(User::class)->find($id);

		if($token === false){
			return new JsonResponse(['ERROR'=>'Token no válido']);
		}else{
			if(!$user){
				return new JsonResponse(['status'=>false, 'message'=>'No se encontró el usuario']);
			}
			$directContract = $doctrine->getRepository(DirectContract::class)->findBy(['user'=>$user]);
			if(empty($directContract)){
				return new JsonResponse(['status'=>false, 'message'=>'No se encontró la contratación directa']);
			}else{ 	
				foreach($directContract as $directContract){
					$requisition = $directContract->getRequisition();
					$user = $directContract->getUser();

					$directContract = [
						'id' => $directContract->getId(),
						'work_dedication' => $directContract->getWorkDedication(),
						'hours' => $directContract->getHours(),
						'initial_date' => $directContract->getInitialDate()->format('Y-m-d'),
						'final_date' => $directContract->getFinalDate()->format('Y-m-d'),
						'duration' => $directContract->getDurationContract(),
						'specific_functions' => $directContract->getSpecificFunctions(),
						'salary' => $directContract->getSalary(),
						'solicitude_date' => $directContract->getSolicitudeDate()->format('Y-m-d'),
						'state' => $directContract->getState(),
						'history' => $directContract->getHistory(),
						'chargeId' => $directContract->getCharge()->getId(),
						'chargeName' => $directContract->getCharge()->getName(),
						'typeEmployee' => $directContract->getCharge()->getTypeEmployee(),
						'profileId' => $directContract->getProfile()->getId(),
						'profileName' => $directContract->getProfile()->getName(),

						'type_requisition' => $requisition->getTypeRequisition(),
						'type_contract' => $requisition->getTypeContract(),
						'type_anotherIF' => $requisition->getTypeAnotherif(),
						'names_charge' => $requisition->getNamesCharge(),
						'justification' => $requisition->getJustification(),

						'user' => $user->getNames().' '.$user->getLastNames(),
						'typeIdentification' => $user->getTypeIdentification(),
						'identification' => $user->getIdentification(),
						'userId' => $user->getId(),
						'email' => $user->getEmail(),
						'phone' => $user->getPhone()
					];
				}
			}
			return new JsonResponse(['status' => true, 'directContract_data' => $directContract]);
		}
	}

	//LISTAR DATOS DE CONTRATACIÓN DIRECTA CON EL ID DE LA CONTRATACIÓN DIRECTA
	#[Route('/contract/get-direct-contract/{id}', name:'app_contract_get_direct_contract')]
	public function getDirectContract(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$directContract = $doctrine->getRepository(DirectContract::class)->find($id);
		if (!$directContract) {
				return new JsonResponse(['status' => false, 'message' => 'No se encontró la contratación directa']);
			}
		$requisition = $directContract->getRequisition();
		$user = $directContract->getUser();

		$directContractData = [
			'id' => $directContract->getId(),
			'work_dedication' => $directContract->getWorkDedication(),
			'hours' => $directContract->getHours(),
			'initial_date' => $directContract->getInitialDate()->format('Y-m-d'),
			'final_date' => $directContract->getFinalDate()->format('Y-m-d'),
			'duration' => $directContract->getDurationContract(),
			'specific_functions' => $directContract->getSpecificFunctions(),
			'salary' => $directContract->getSalary(),
			'solicitude_date' => $directContract->getSolicitudeDate()->format('Y-m-d'),
			'state' => $directContract->getState(),
			'history' => $directContract->getHistory(),
			'chargeId' => $directContract->getCharge()->getId(),
			'chargeName' => $directContract->getCharge()->getName(),
			'typeEmployee' => $directContract->getCharge()->getTypeEmployee(),
			'profileId' => $directContract->getProfile()->getId(),
			'profileName' => $directContract->getProfile()->getName(),

			'type_requisition' => $requisition->getTypeRequisition(),
			'type_contract' => $requisition->getTypeContract(),
			'type_anotherIF' => $requisition->getTypeAnotherif(),
			'names_charge' => $requisition->getNamesCharge(),
			'justification' => $requisition->getJustification(),

			'user' => $user->getNames().' '.$user->getLastNames(),
			'typeIdentification' => $user->getTypeIdentification(),
			'identification' => $user->getIdentification(),
			'userId' => $user->getId(),
			'email' => $user->getEmail(),
			'phone' => $user->getPhone()
		];
		return new JsonResponse(['status' => true, 'directContract_data' => $directContractData]);
	}

  #[Route('/contract/allDirectContract', name:'app_contract_all_directContract')]
  public function listAllDirectContract(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
  {
	$token = $request->query->get('token');
	$userLogueado = $vToken->getUserIdFromToken($token);
	$entityManager = $doctrine->getManager();

	if($token === false){
		return new JsonResponse(['ERROR' => 'Token no Válido']);
	}else{
		$allDirectContracts = $doctrine->getRepository(DirectContract::class)->findAll();
		if(empty($allDirectContracts)){
			return new JsonResponse(['status'=>false, 'message'=>'No se encontraron contrataciones directas']);
		}
		foreach($allDirectContracts as $allDirectContract){
			$requisition = $allDirectContract->getRequisition();
    		$user = $allDirectContract->getUser();

			$existingUserInRequisition = $entityManager->getRepository(UsersInRequisition::class)->findOneBy([
				'requisition' => $requisition
			]);

			$stateUsersInRequisition = $existingUserInRequisition->getState();
			$usersInRequisition = $existingUserInRequisition->getUser();

   			 $directContractData[] = [
				'id' => $allDirectContract->getId(),
				'work_dedication' => $allDirectContract->getWorkDedication(),
				'hours' => $allDirectContract->getHours(),
				'initial_date' => $allDirectContract->getInitialDate()->format('Y-m-d'),
				'final_date' => $allDirectContract->getFinalDate()->format('Y-m-d'),
				'duration' => $allDirectContract->getDurationContract(),
				'specific_functions' => $allDirectContract->getSpecificFunctions(),
				'salary' => $allDirectContract->getSalary(),
				'solicitude_date' => $allDirectContract->getSolicitudeDate()->format('Y-m-d'),
				'state' => $allDirectContract->getState(),
				'history' => json_decode($allDirectContract->getHistory(),true),
				'chargeId' => $allDirectContract->getCharge()->getId(),
				'chargeName' => $allDirectContract->getCharge()->getName(),
				'typeEmployee' => $allDirectContract->getCharge()->getTypeEmployee(),
				'profileId' => $allDirectContract->getProfile()->getId(),
				'profileName' => $allDirectContract->getProfile()->getName(),
				'type_requisition' => $requisition->getTypeRequisition(),
				'type_contract' => $requisition->getTypeContract(),
				'type_anotherIF' => $requisition->getTypeAnotherif(),
				'names_charge' => $requisition->getNamesCharge(),
				'justification' => $requisition->getJustification(),
				'usersInRequisition' => $usersInRequisition->getId(),
				'stateUsersInRequisition' => $stateUsersInRequisition,

				'user' => $user->getNames().' '.$user->getLastNames(),
				'typeIdentification' => $user->getTypeIdentification(),
				'identification' => $user->getIdentification(),
				'userId' => $user->getId(),
				'email' => $user->getEmail(),
				'phone' => $user->getPhone()
			];
		}
	}
	return new JsonResponse(['status'=>true, 'allDirectContracts' => $directContractData]);
  }

  #[Route('/contract/approve-directContract', name:'app_approve_directContract')]
  public function approveDirectContract(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
  {
	$token = $request->query->get('token');
	$notificationId = $request->query->get('notificationId');
	$applicantId = $request->query->get('applicantId');
	$user = $vToken->getUserIdFromToken($token);
	$specialUser = $user->getSpecialUser();
	$idDirectContract = json_decode($request->getContent(), true);

	$entityManager = $doctrine->getManager();

	$directContract = $doctrine->getRepository(DirectContract::class)->find($idDirectContract);
	$userSelected = $directContract->getUser();
	$idRequisition = $directContract->getRequisition();
	$requisition = $entityManager->getRepository(Requisition::class)->find($idRequisition);
	$namesUserSelected = $userSelected->getNames().''.$userSelected->getLastNames();

	if(!$directContract){
		return new JsonResponse(['status'=>false, 'message'=>'No se encontró solicitud de contratación directa']);
	}

	$directContract->setState(1);

	$existingUserInRequisition = $entityManager->getRepository(UsersInRequisition::class)->findOneBy([
		'requisition' => $requisition
	]);

	// Si existe la relación, actualizamos el usuario; si no, lanzamos una excepción
	if ($existingUserInRequisition) {
		$existingUserInRequisition->setState(1);
		$entityManager->persist($existingUserInRequisition);
	} else {
		throw $this->createNotFoundException('No se encontró UsersInRequisition para la requisición dada.');
	}

	$newNotification = new Notification();
	$newNotification->setSeen(0);
	$userApplicant = $doctrine->getRepository(User::class)->find($applicantId);

	$userNames = $userApplicant->getNames();
	$relatedEntity = array(
		'applicantId' => $applicantId,
		'applicantName' => $userNames,
		'entity'=>'directContract'
	);
	$newNotification->setRelatedEntity(json_encode($relatedEntity));
	switch($specialUser){
		case 'DIRENF':
		case 'DIRASS':
		case 'VAE':
		case 'AOASIC':
		case 'ASIAC':
			$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF', 'userType'=>1]);
			$newNotification->setUser($userForNotification);
			$newNotification->setMessage('Solicita la aprobación de una contratación directa de '.$namesUserSelected);
			break;
		case 'VF':
			$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType'=>8]);
			$newNotification->setUser($userForNotification);
			$newNotification->setMessage('Aprobación del usuario.'. $namesUserSelected);
			break;
	}
	$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
	$notification->setSeen(1);
	$history = $directContract->getHistory();
	date_default_timezone_set('America/Bogota');
	$addToHistory = json_encode(array(
		'user' => $user->getId(),
		'responsible' => $user->getSpecialUser(),
		'state' => 1, //Aprobado por VF
		'message' => 'La solicitud de contratación directa del trabajador fue aprobado por '.$user->getNames()." ".$user->getLastNames(),
		'date' => date('Y-m-d H:i:s'),
	));
	$newHistory = rtrim($history, ']').','.$addToHistory.']';
	$directContract->setHistory($newHistory);
	$entityManager->persist($newNotification);
	$entityManager->flush();
	return new JsonResponse(['message'=>'Se ha aprobado la solicitud de contratación directa exitosamente']);
  }
  #[Route('contract/reject-directContract', name:'app_reject_directContract')]
  public function rejectDirectContract(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
  {
	$token = $request->query->get('token');
	$directContractId = $request->query->get('directContractId');
	$notificationId = $request->query->get('notificationId');
	$applicantId = $request->query->get('applicantId');
	$rejectText = $request->request->get('rejectText');

	$entityManager = $doctrine->getManager();

	$user = $vToken->getUserIdFromToken($token);
	$specialUser = $user->getSpecialUser();

	$directContract = $doctrine->getRepository(DirectContract::class)->find($directContractId);
	$idRequisition = $directContract->getRequisition();
	$requisition = $entityManager->getRepository(Requisition::class)->find($idRequisition);
	$userSelected = $directContract->getUser();
	$namesUserSelected = $userSelected->getNames().''.$userSelected->getLastNames();

	$existingUserInRequisition = $entityManager->getRepository(UsersInRequisition::class)->findOneBy([
		'requisition' => $requisition
	]);

	// Si existe la relación, actualizamos el usuario; si no, lanzamos una excepción
	if ($existingUserInRequisition) {
		$existingUserInRequisition->setState(3);
		$entityManager->persist($existingUserInRequisition);
	} else {
		throw $this->createNotFoundException('No se encontró UsersInRequisition para la requisición dada.');
	}

	if(!$directContract){
		return new JsonResponse(['status'=>false,'message'=>'No se encontró solicitud de contratación directa']);
	}
	$directContract->setState(4); //Rechazado por VF
	$newNotification = new Notification();
	$newNotification->setSeen(0);
	$userNames = $doctrine->getRepository(User::class)->find($applicantId);

	$userNames= $userNames->getNames();
	$relatedEntity = array(
		'id'=>$directContractId,
		'applicantId'=>$applicantId,
		'applicantName'=>$userNames,
		'entity'=>'directContract'
	);
	$newNotification->setRelatedEntity(json_encode($relatedEntity));
	switch($specialUser){
		case 'VF':
			$userForNotification = $doctrine->getRepository(User::class)->find($applicantId);
			$newNotification->setUser($userForNotification);
			$newNotification->setMessage('Contratación directa del trabajador.'.$namesUserSelected.' rechazada');
			break;
	}
	$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
	$notification->setSeen(0);
	$history = $directContract->getHistory();
	date_default_timezone_set('America/Bogota');
	$addToHistory = json_encode(array(
		'user' => $user->getId(),
		'responsible' => $user->getSpecialUser(),
		'state' => 3, //Rechazado por VF
		'message' => 'La solicitud de contratación directa del trabajador fue rechazada por '.$user->getNames()." ".$user->getLastNames(),
		'userInput'=> $rejectText,
		'date' => date('Y-m-d H:i:s'),
	));
	$newHistory = rtrim($history, ']').','.$addToHistory.']';
	$directContract->setHistory($newHistory);
	$entityManager->persist($newNotification);
	$entityManager->flush();
	return new JsonResponse(['message'=>'Se ha rechazado la solicitud de contratación directa de '.$namesUserSelected]);
  }
}
