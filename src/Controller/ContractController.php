<?php

	namespace App\Controller;

	use App\Entity\Contract;
	use App\Entity\ContractAssignment;
	use App\Entity\ContractCharges;
	use App\Entity\Medicaltest;
use App\Entity\PermissionsAndLicences;
use App\Entity\Profile;
use App\Entity\Requisition;
use App\Entity\User;
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
			$contract = new Contract();
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
				$fileName = 'contrato_' . preg_replace('/[^A-Za-z0-9\-_]+/', '_', $identificationUser) . '.docx';
									
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

        // Obtener todos los contratos asociados al usuario
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
				$fileName = $identificationUser.'_'.$nameFile;

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
		if(empty($charges)){ return new JsonResponse(['status'=>false,'message'=>'No se encontró lista de cargos']);}
		return new JsonResponse($serializerAllCharges,200,[],true);
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
	#[Route('/contract/permissions-licences', name:'app_contract_permissions_licences')]
	public function createPermissionsLicences( ManagerRegistry $doctrine,Request $request, ValidateToken $vToken): JsonResponse
	{
		$token = $request->query->get('token');
		$entityManager = $doctrine->getManager();
		$user = $vToken->getUserIdFromToken($token);
		$data = $request->request->all();

		$solicitudeDate = $data['solicitude_date'];
		$initialDate = $data['initial_date'] ?? NULL;
		$finalDate = $data['final_date'] ?? NULL;


		if($token === false){
			return new JsonResponse(['ERROR' => 'Token no válido']);
		}else{
			$permissionsAndLicences = new PermissionsAndLicences();

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$solicitudeDate)){
				$dateTimeSolicitudeDate = new DateTime($solicitudeDate);
				$permissionsAndLicences -> setSolicitudeDate($dateTimeSolicitudeDate);
			}

			$permissionsAndLicences -> setTypeSolicitude($data['type_solicitude']);
			$permissionsAndLicences -> setTypePermission($data['type_permission'] ?? NULL);
			$permissionsAndLicences -> setTypeFlexibility($data['type_flexibility'] ?? NULL);
			$permissionsAndLicences -> setTypeCompensation($data['type_compensation'] ?? NULL);
			$permissionsAndLicences -> setTypeDatePermission($data['type_date_permission'] ?? NULL);
			$permissionsAndLicences -> setReason($data['reason']);
			
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$initialDate)){
				$dateTimeInitial = new DateTime($initialDate);
				$permissionsAndLicences -> setInitialDate($dateTimeInitial);
			}

			if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finalDate)){
				$dateTimeFinal = new DateTime($finalDate);
 				$permissionsAndLicences -> setFinalDate($dateTimeFinal);
			}
			
			if(isset($data['start_hour'])){
				$startHourDateTime = DateTime::createFromFormat('H:i',$data['start_hour']);
				$permissionsAndLicences -> setStartHour($startHourDateTime);
			}else{
				$permissionsAndLicences -> setStartHour(NULL);
			}

			if(isset($data['final_hour'])){
				$finalHourDateTime = DateTime::createFromFormat('H:i', $data['final_hour']);
				$permissionsAndLicences -> setFinalHour($finalHourDateTime);

			}else{
				$permissionsAndLicences -> setFinalHour(NULL);
			}
			
			$permissionsAndLicences -> setTypeLicense($data['type_license'] ?? NULL);
			$permissionsAndLicences -> setLicense($data['license'] ?? NULL);
			$permissionsAndLicences -> setUser($user);

			$file = $request->files->get('support_pdf');
			$nameFile = $data['fileName'];
			$identificationUser = $data['identificationUser'];

			if($file instanceof UploadedFile){
				$folderDestination = $this->getParameter('contract').'/'.$identificationUser;
				$fileName = $identificationUser.'_'.$nameFile;

				try{
					$file->move($folderDestination,$fileName);
					$permissionsAndLicences->setSupportPdf($fileName);
				}catch(\Exception $e){
					return new JsonResponse(['error' => 'Error al crear el permiso o la licencia']);
				}
			}

			$entityManager->persist($permissionsAndLicences);
			$entityManager->flush();
		}

		return new JsonResponse(['status'=>'Success','message'=>'Permiso o licencia creada con éxito']);
	}
	///------------------------------------------------------------------------------------------
	//---- REQUISITON
	#[Route('contract/create-requisition', name:'app_contract_create_requisition')]
	public function createRequisition(ManagerRegistry $doctrine, Request $request): JsonResponse
	{
		$isTokenValid = $this->validateTokenSuper($request)->getContent();
		$entityManager = $doctrine->getManager();
		$data = $request->request->all();

		if($isTokenValid === false){
			return new JsonResponse(['error' => 'Token no válido']);
		}else{
			$user = $entityManager->getRepository(User::class)->find($data['user']);
			if(!$user){
				throw $this->createNotFoundException('No user found for id' . $data['id']);
			}
			$profileId = $data['profile'];
			$profile = $entityManager->getRepository(Profile::class)->find($profileId);

			$requisition = new Requisition();
			$requisition->setTypeRequisition($data['type_requisition']);
			$requisition->setObjectContract($data['object_contract']);
			$requisition->setWorkDedication($data['work_dedication']);
			$requisition->setInitialContract($data['initial_contract']);
			$requisition->setSpecificFunctions($data['specific_functions']);
			$requisition->setSalary($data['salary']);
			$requisition->setProfile($profile);
			$requisition->setUser($user);
			
			$entityManager->persist($requisition);
			$entityManager->flush();
		}

		return new JsonResponse(['status'=>'Success','message'=>'Requisición creada con éxito']);
	}
}