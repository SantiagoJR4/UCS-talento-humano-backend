<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\ContractAssignment;
use App\Entity\ContractCharges;
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
			$contract->setUser($user);

			$file = $request->files->get('file');
			$identificationUser = $data['identificationUser'];
			
			if ($file instanceof UploadedFile) {
				$folderDestination = $this->getParameter('contract')
											.'/'
											.$identificationUser;
				$fileName = 'contrato_'.$identificationUser.'_'.time().'.docx';
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

			return new JsonResponse(['status' => 'Success', 'Code' => '200', 'message' => 'Contrato y asignación generados con éxito']);
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

		$contractData = [];
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
                    // Agregar más campos del contrato según tu modelo
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
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException('No user found for id' . $data['id']);
			}
			$profileId = $data['profile'];
			$profile = $entityManager->getRepository(Profile::class)->find($profileId);

			$requisition = new Requisition();
			
			$currentDate= new DateTime();
			$requisition->setCurrentdate($currentDate);

			$requisition->setTypeRequisition($data['type_requisition']);
			$requisition->setObjectContract($data['object_contract']);
			$requisition->setWorkDedication($data['work_dedication']);
			$requisition->setInitialContract($data['initial_contract']);
			$requisition->setSpecificFunctions($data['specific_functions']);
			$requisition->setSalary($data['salary']);
			$requisition->setProfile($profile);
			$requisition->setUser($user);
			$requisition->setState(0);
			
			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $user->getId(),
				'responsible' => $user->getSpecialUser(),
				'state' => 0,
				'message' => 'La requisición fue solicitada por '.$user->getNames()." ".$user->getLastNames(),
				'date' => date('Y-m-d H:i:s'),
			)));
			$requisition->setHistory($addToHistory);

			$entityManager->persist($requisition);
			$entityManager->flush();

			// var_dump(json_decode($data['arrayImmediateBoss'],true));
			// $idVF = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'REC', 'userType'=>1])

			switch($specialUser){
				case 'VF':
					$inmediateBossEntity = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'REC', 'userType'=>1]);
					$immediateBossArray = [$inmediateBossEntity->getId()];
					break;
				case 'AOASIC':
				case 'CTH':
				case 'VPSB':
				case 'VAE':
				case 'VII':
				case 'ASIAC':
					$immediateBossArray = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF', 'userType'=>1]);
					$immediateBossArray = [$immediateBossArray->getId()];
					break;
				default:
					$immediateBossArray = json_decode($data['arrayImmediateBoss'],true);
					$immediateBossArray = array_map(function($boosId){
						return $boosId['id'];
					}, $immediateBossArray);
				break;
			}

			foreach($immediateBossArray as $boss){
				// $bossID = $boss['id'];
				$immediateBossUsers = $doctrine->getRepository(User::class)->find($boss);
				$newNotification = new Notification();
				$newNotification->setSeen(0);
				$newNotification->setUser($immediateBossUsers);
				$newNotification->setMessage('Solicita la aprobación de una requisición');
				
				$relatedEntity = array(
					'id'=>$requisition->getId(),
					'applicantId'=>$user->getId(),
					'applicantName'=>$user->getNames()." ".$user->getLastNames(),
					'entity' => 'requisition'
				);
				$newNotification->setRelatedEntity(json_encode($relatedEntity));
				$entityManager->persist($newNotification);
			}
			$entityManager->flush();

			//--------------------------------------------------------------------------------
			//----------------------- USERS IN REQUISITION
			$requisitionId = $requisition->getId();
			$requisitionEntity = $entityManager->getRepository(Requisition::class)->find($requisitionId);

			$dataUserRequisition = $entityManager->getRepository(User::class)->find($data['user_requisition']);
			
			$userInRequisition = new UsersInRequisition();
			$userInRequisition->setRequisition($requisitionEntity);
			$userInRequisition->setUser($dataUserRequisition);
			
			$entityManager->persist($userInRequisition);
			$entityManager->flush();
		}

		return new JsonResponse(['status'=>'Success','message'=>'Requisición creada con éxito']);
	}
	
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
			$requisitionUser = [];
			$userInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findBy(['requisition'=>$requisition->getId()]);
			foreach($userInRequisitions as $userInRequisition){
				$userName = $userInRequisition->getUser();
				if($userName){
					$requisitionUser[] = [
						'names' => $userName->getNames(),
						'lastNames' => $userName->getLastNames()
					];
				}
			}

			$profile = $requisition->getProfile();
			$user = $requisition->getUser();

			$requisitionData[] = [
				'requisition'=>[
					'id' => $requisition->getId(),
					'currentDate' => $requisition->getCurrentdate() ? $requisition->getCurrentdate()->format('Y-m-d') : NULL,
					'type_requisition' => $requisition->getTypeRequisition(),
					'object_contract' => $requisition->getObjectContract(),
					'work_dedication' => $requisition->getWorkDedication(),
					'initial_contract' => $requisition->getInitialContract(),
					'specific_functions' => $requisition->getSpecificFunctions(),
					'state' => $requisition->getState(),
					'history' => $requisition->getHistory(),
					'salary' => $requisition->getSalary(),
					'profileName' => $profile->getName(),
					'username' => $user->getNames().' '.$user->getLastNames(),
					'userIdentification' => $user->getIdentification()
				],
				'requisitionUser' => $requisitionUser
			];
		}

		return new JsonResponse(['status' => true, 'requisition_data' => $requisitionData]);
	}
	
	#[Route('contract/get-requisition/{id}', name:'app_contract_get_requisition')]
	public function getRequisition(ManagerRegistry $doctrine, int $id): JsonResponse
	{
		$requisition = $doctrine->getRepository(Requisition::class)->find($id);

		if (!$requisition) {
			return new JsonResponse(['status' => false, 'message' => 'No se encontró la requisición']);
		}

		$userInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findBy(['requisition' => $requisition]);

		$requisitionUser = [];

		foreach ($userInRequisitions as $userInRequisition) {
			$userName = $userInRequisition->getUser();
			if ($userName) {
				$requisitionUser[] = [
					'names' => $userName->getNames(),
					'lastNames' => $userName->getLastNames()
				];
			}
		}

		$profile = $requisition->getProfile();
		$user = $requisition->getUser();

		$requisitionData = [
			'requisition' => [
				'id' => $requisition->getId(),
				'currentDate' => $requisition->getCurrentdate() ? $requisition->getCurrentdate()->format('Y-m-d') : null,
				'type_requisition' => $requisition->getTypeRequisition(),
				'object_contract' => $requisition->getObjectContract(),
				'work_dedication' => $requisition->getWorkDedication(),
				'initial_contract' => $requisition->getInitialContract(),
				'specific_functions' => $requisition->getSpecificFunctions(),
				'state' => $requisition->getState(),
				'history' => $requisition->getHistory(),
				'salary' => $requisition->getSalary(),
				'profileName' => $profile->getName(),
				'username' => $user->getNames() . ' ' . $user->getLastNames(),
				'idUser' => $user->getId(),
				'userIdentification' => $user->getIdentification()
			],
			'requisitionUser' => $requisitionUser
		];

    	return new JsonResponse(['status' => true, 'requisition_data' => $requisitionData]);
	}

	#[Route('contract/approve-requisition', name:'app_approve_requisition')]
	public function approveRequisition(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request) : JsonResponse
	{
		$token = $request->query->get('token');
		$requisitionId = $request->query->get('requisitionId');
		$notificationId = $request->query->get('notificationId');
		$applicantId = $request->query->get('applicantId');

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
				$newStateForRequisition = 2;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'REC','userType' => 1]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('solicita la aprobación de una requisición por parte de Rectoría');
				break;
			case 'REC':
				$newStateForRequisition = 3;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
                $newNotification->setUser($userForNotification);
                $newNotification->setMessage('Finalización de requisición exitosa.');
				break;
			case 'CTH':
				$newStateForRequisition = 5;
				$userWhoMadeRequisition = $requisition->getUser();
				$newNotification->setUser($userWhoMadeRequisition);
				$newNotification->setMessage('Revisión de requisición finalizada.');
				break;
			default:
				$newStateForRequisition = 1;
				$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'VF', 'userType'=>1]);
				$newNotification->setUser($userForNotification);
				$newNotification->setMessage('solicita la aprobación de una requisición por parte de Vicerrectoría Financiera');
				break;
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
		$applicantId = $request->query->get('applicantId');
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
			case 'REC':
                $newNotification->setMessage('Requisición rechazada por Rectoría');
				$userWhoMadeLicense = $requisition->getUser();
				$newNotification->setUser($userWhoMadeLicense);
				break;
			case 'CTH':
                $newNotification->setMessage('Requisición rechazada por Talento humano');
				$userWhoMadeLicense = $requisition->getUser();
				$newNotification->setUser($userWhoMadeLicense);
				break;
			default:
				$newNotification->setMessage('Requisición rechazada por Jefe inmediato');
				$userWhoMadeRequisition = $requisition->getUser();
				$newNotification->setUser($userWhoMadeRequisition);
				break;
		}
		$notification = $doctrine->getRepository(Notification::class)->find($notificationId);
		$notification->setSeen(1);

		$requisition->setState(4);
		$history = $requisition->getHistory();
		date_default_timezone_set('America/Bogota');
		$addToHistory = json_encode(array(
			'user' => $user->getId(),
			'responsible' => $user->getSpecialUser(),
			'state' => 4,
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
	public function allRequisitions(ManagerRegistry $doctrine): JsonResponse
	{
		$requisitionData = [];
		$requisitions = $doctrine->getRepository(Requisition::class)->findAll();
		
		if (empty($requisitions)) {
				return new JsonResponse(['status' => false, 'message' => 'No se encontraron requisiciones.']);
		}

		foreach ($requisitions as $requisition) {
			$requisitionUser = [];
			$userInRequisitions = $doctrine->getRepository(UsersInRequisition::class)->findBy(['requisition' => $requisition->getId()]);

			foreach ($userInRequisitions as $userInRequisition) {
					$userName = $userInRequisition->getUser();

					if ($userName) {
							$requisitionUser[] = [
									'names' => $userName->getNames(),
									'lastNames' => $userName->getLastNames(),
							];
					}
			}

			$profile = $requisition->getProfile();
			$user = $requisition->getUser();

			$requisitionData[] = [
					'requisition' => [
							'id' => $requisition->getId(),
							'currentDate' => $requisition->getCurrentdate() ? $requisition->getCurrentdate()->format('Y-m-d') : null,
							'type_requisition' => $requisition->getTypeRequisition(),
							'object_contract' => $requisition->getObjectContract(),
							'work_dedication' => $requisition->getWorkDedication(),
							'initial_contract' => $requisition->getInitialContract(),
							'specific_functions' => $requisition->getSpecificFunctions(),
							'state' => $requisition->getState(),
							'history' => $requisition->getHistory(),
							'salary' => $requisition->getSalary(),
							'profileName' => $profile->getName(),
							'username' => $user->getNames() . ' ' . $user->getLastNames(),
							'userIdentification' => $user->getIdentification(),
					],
					'requisitionUser' => $requisitionUser,
			];
		}
		return new JsonResponse(['status' => true, 'requisition_data' => $requisitionData]);
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
			$reemployment -> setState(0);

			$reemployment -> setPeriod('A2024');
			$reemployment -> setCharges($chargeId);
			$reemployment -> setProfile($profileId);
			$reemployment -> setUser($user);

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $userLogueado->getId(),
				'responsible' => $userLogueado->getSpecialUser(),
				'state' => 1,
				'message' => 'La solicitud de revinculación de personal administrativo fue solicitado por '.$userLogueado->getNames()." ".$userLogueado->getLastNames(),
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
			$reemployment -> setState(0);
			$reemployment -> setPeriod('A2024');
			$reemployment -> setCharges($chargeId);
			$reemployment -> setProfile($profileId);
			$reemployment -> setUser($user);

			date_default_timezone_set('America/Bogota');
			$addToHistory = json_encode(array(array(
				'user' => $userLogueado->getId(),
				'responsible' => $userLogueado->getSpecialUser(),
				'state' => 1,
				'message' => 'Actualización de revinculación de personal administrativo fue solicitado por '.$userLogueado->getNames()." ".$userLogueado->getLastNames(),
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
		$entityManager = $doctrine->getManager();

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$reemployments = $entityManager->getRepository(Reemployment::class)->findAll();
			$response = [];

			$query = $doctrine->getManager()->createQueryBuilder();
			$query
				->select('c')
				->from('App\Entity\Reemployment', 'c')
				->where('c.finalDate >= :expirationPeriod')
				->setParameters(array('expirationPeriod'=>date('Y-m-d')));
			$reemployments = $query->getQuery()->getResult();

			foreach($reemployments as $reemployment){
				$user = $reemployment->getUser();
				$workDedication = $reemployment->getWorkDedication();

				$response[] = [
					'id' => $reemployment->getId(),
					'period' => $reemployment->getPeriod(),
					'solicitude_date' => $reemployment->getSolicitudeDate()->format('Y-m-d'),
					'initial_date' => $reemployment->getInitialDate()->format('Y-m-d'),
					'final_date' => $reemployment->getFinalDate()->format('Y-m-d'),
					'state' => $reemployment->getState(),
					'history' => $reemployment->getHistory(),
					'chargeId' => $reemployment->getCharges()->getId(),
					'chargeName' => $reemployment->getCharges()->getName(),
					'chargeWorkDedication' => $workDedication,
					'chargeSalary' => $reemployment->getSalary(),
					'typeEmployee' => $reemployment->getCharges()->getTypeEmployee(),
					'profileId' => $reemployment->getProfile()->getId(),
					'profileName' => $reemployment->getProfile()->getName(),
					'user' => $user->getNames().' '.$user->getLastNames(),
					'userId' => $user->getId(),
					'identification' => $user->getIdentification(),
					'email' => $user->getEmail(),
					'phone' => $user->getPhone()

				];
			}
			// $queryTeachers = $doctrine->getManager()->createQueryBuilder();
			// $queryTeachers 
			// 	->select('u.id, u.names, u.lastNames, u.identification, u.email, u.phone')
			// 	->from('App\Entity\User', 'u')
			// 	->where('u.userType = 2');
			// $teachers = $queryTeachers->getQuery()->getResult();
			// foreach ($teachers as $key => $value) {
			// 	$teachers[$key]['user'] = $value['names'] . ' ' . $value['lastNames'];
			// }
			// $workers = array_merge($response, $teachers);
		}
		return new JsonResponse(['status' => true, 'reemployment' => $response]);
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
					'initial_date' => $reemployment->getInitialDate()->format('Y-m-d'),
					'final_date' => $reemployment->getFinalDate()->format('Y-m-d'),
					'state' => $reemployment->getState(),
					'history' => $reemployment->getHistory(),
					'chargeId' => $reemployment->getCharges()->getId(),
					'chargeName' => $reemployment->getCharges()->getName(),
					'typeEmployee' => $reemployment->getCharges()->getTypeEmployee(),
					'chargeWorkDedication' => $workDedication,
					'chargeSalary' => $reemployment->getSalary(),
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
		$entityManager = $doctrine->getManager();
		$idsToChange = json_decode($request->getContent(), true);
		
		foreach ($idsToChange as $id) {
			$reemployment = $doctrine->getRepository(Reemployment::class)->find($id);
		
			if (!$reemployment) {
				return new JsonResponse(['status' => false, 'message' => 'No se encontró solicitud de revinculación para el id ' . $id]);
			}
			$reemployment->setState(1);
		}

		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$newNotification = new Notification();
			$newNotification->setSeen(0);
			$userForNotification = $doctrine->getRepository(User::class)->findOneBy(['specialUser'=>'CTH','userType' => 8]);
			$newNotification->setUser($userForNotification);
			$newNotification->setMessage('Listado de revinculación personal administrativo.');
			$relatedEntity = array(
				'applicantId'=>$user->getId(),
				'applicantName'=>$user->getNames()." ".$user->getLastNames(),
				'entity'=>'reemployment'
			);
			$newNotification->setRelatedEntity(json_encode($relatedEntity));
							
			$entityManager->persist($newNotification);
		}
		$entityManager->flush();
		return new JsonResponse(['status'=>'Success','message'=>'Lista enviada con exito']);

	}

	#[Route('/contract/listWorkers', name: 'app_list_workers')]
	public function workers(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$user = $vToken->getUserIdFromToken($token);
	
		// Filtra los usuarios por los tipos 1 y 2
		$users = $doctrine->getRepository(User::class)->findBy(['userType' => [1, 2]]);
	
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
	
		// Retorna la respuesta JSON
		return new JsonResponse($userData);
	}
}

