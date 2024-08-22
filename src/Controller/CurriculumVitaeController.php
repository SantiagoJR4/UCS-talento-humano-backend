<?php

namespace App\Controller;

use App\Entity\AcademicTraining;
use App\Entity\FurtherTraining;
use App\Entity\IntellectualProduction;
use App\Entity\Language;
use App\Entity\Notification;
use App\Entity\PersonalData;
use App\Entity\Record;
use App\Entity\ReferencesData;
use App\Entity\TeachingExperience;
use App\Entity\User;
use App\Entity\WorkExperience;
use App\Service\PdfService;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Ordinary9843\Ghostscript;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

//TODO: Hacer global

function flattenArray($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, flattenArray($value, $prefix . $key . '-'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }
    $filteredArray = array_filter($result, function($value) {
        return $value !== null;
    });
    return $filteredArray;
}

function parseCvData($data) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = parseCvData($value);
        } elseif ($value instanceof \DateTime) {
            $data[$key] = $value->format('Y-m-d H:i:s');
        } else if($key === 'history'){
            $decodedValue = json_decode($value, true);
            $data[$key] = end($decodedValue);
            $data['tag'] = setTag(end($decodedValue)['state']);
        } elseif($key === 'timeWorked'){
            $decodedValue = json_decode($value, true);
            $data[$key] = formatTimeWorked($decodedValue);
        } elseif(in_array($key, ['placeOfResidence', 'placeOfBirth', 'placeOfExpedition'])){
            $data[$key] = json_decode($value, true);
        }
    }
    return $data;
}

function filesToChangeOrDelete($table) {
    switch ($table) {
        case 'AcademicTraining':
            return ['degreePdf', 'diplomaPdf', 'certifiedTitlePdf'];
        case 'FurtherTraining':
        case 'WorkExperience':
        case 'TeachingExperience':
        case 'language':
            return ['certifiedPdf'];
        default:
            return [];
    }
}

function setTag($status){
    $statusMap = array(
        0 => array('severity' => 'info', 'icon' => 'upload', 'value' => 'Subido'),
        1 => array('severity' => '', 'icon' => 'check', 'value' => 'Aprobado'),
        2 => array('severity' => 'danger', 'icon' => 'close', 'value' => 'Rechazado'),
        3 => array('severity' => 'warning', 'icon' => 'hourglass_top', 'value' => 'Pendiente'),
        4 => array('severity' => 'info', 'icon' => 'edit', 'value' => 'Editado'),
    );
    return isset($statusMap[$status])
        ? $statusMap[$status]
        : array('severity' => 'info', 'icon' => 'upload', 'value' => 'Subido');
}

function formatTimeWorked($timeWorked): string {
    $years = $timeWorked['years'] ?? 0;
    $months = $timeWorked['months'] ?? 0;
    $days = $timeWorked['days'] ?? 0;

    $timeParts = [];

    if($years>0){$timeParts[]="{$years} años";}
    if($months>0){$timeParts[]="{$months} meses";}
    if($days>0){$timeParts[]="{$days} días";}

    $formattedTime = implode(" ", $timeParts);

    return $formattedTime;
}

class CurriculumVitaeController extends AbstractController
{

    private $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    #[Route('/curriculum-vitae/read-cv', name: 'app_curriculum_vitae_read')]
    public function read(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken  ): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $userId = $user->getId();
        $qb = function($class, $id) use ($doctrine) {
            return $doctrine->getRepository($class)->createQueryBuilder('e')->andWhere('e.user = :user')->setParameter('user', $id)->getQuery()->getArrayResult();
        };
        return new JsonResponse([
            'user' => [$user->getUrlPhoto(), $userId],
            'personalData' => parseCvData($qb(PersonalData::class, $userId)),
            'academicTraining' => parseCvData($qb(AcademicTraining::class, $userId)),
            'furtherTraining' => parseCvData($qb(FurtherTraining::class, $userId)),
            'language' => parseCvData($qb(Language::class, $userId)),
            'workExperience' => parseCvData($qb(WorkExperience::class, $userId)),
            'teachingExperience' => parseCvData($qb(TeachingExperience::class, $userId)),
            'intellectualproduction' => parseCvData($qb(IntellectualProduction::class, $userId)),
            'references' => parseCvData($qb(ReferencesData::class, $userId)),
            'records' => parseCvData($qb(Record::class, $userId))
        ]);
    }

    #[Route('/curriculum-vitae/list-cv/{id}', name:'app_listar_curriculumVitae')]
    public function listCv(ManagerRegistry $doctrine, Request $request ,int $id, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token); // TODO: use this for validate (maybe)
        $user = $doctrine->getRepository(User::class)->find($id);
        $qb = function($class, $id) use ($doctrine) {
            return $doctrine->getRepository($class)->createQueryBuilder('e')->andWhere('e.user = :user')->setParameter('user', $id)->getQuery()->getArrayResult();
        };
        return new JsonResponse([
            'personalData' => parseCvData($qb(PersonalData::class, $user)),
            'academicTraining' => parseCvData($qb(AcademicTraining::class, $user)),
            'furtherTraining' => parseCvData($qb(FurtherTraining::class, $user)),
            'language' => parseCvData($qb(Language::class, $user)),
            'workExperience' => parseCvData($qb(WorkExperience::class, $user)),
            'teachingExperience' => parseCvData($qb(TeachingExperience::class, $user)),
            'intellectualproduction' => parseCvData($qb(IntellectualProduction::class, $user)),
            'references' => parseCvData($qb(ReferencesData::class, $user)),
            'records' => parseCvData($qb(Record::class, $user))
            // 'evaluationCV' => parseCvData($qb(EvaluationCv::class, $user))
        ]);

    }

    #[Route('/curriculum-vitae/delete-cv', name: 'app_curriculum_vitae_delete')]
    public function delete(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken, Filesystem $filesystem): JsonResponse
    {
        // TODO: delete also files
        $token = $request->query->get('token');
        $table = ucfirst($request->query->get('table'));
        $id = $request->query->get('id');
        $user =  $vToken->getUserIdFromToken($token);
        if(isSet($user)){
            $entityManager = $doctrine->getManager();
            $objectToDelete = $entityManager->getRepository('App\\Entity\\'.$table)->find($id);
            if (!$objectToDelete) {
                throw new NotFoundHttpException('No se encontró la entidad a borrar');
            }
            $history = $objectToDelete->getHistory();
            $historyArray = json_decode($history, true);
            if(end($historyArray)['state'] !== 0){
                throw new AccessDeniedException('No tiene autorización realizar este cambio');
            }
            $filesToDelete = filesToChangeOrDelete($table);
            $filesystem = new Filesystem();
            foreach ($filesToDelete as $key => $value) {
                $fileToDelete = $objectToDelete->{'get' . ucfirst($value)}();
                if( $fileToDelete && $filesystem->exists($this->getParameter('hv') . '/' . $fileToDelete)){
                    $filesystem->remove($this->getParameter('hv') . '/' . $fileToDelete);
                }
            }
            $entityManager->remove($objectToDelete);
            $entityManager->flush();
            return new JsonResponse(['response'=>'deleted '.$table.' with ID '.$id]);
        }
    }

    #[Route('/curriculum-vitae/testing-cv', name: 'app_curriculum_vitae_testing_cv')]
    public function testingCV(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken, Filesystem $filesystem): JsonResponse
    {
        $data=['academicTraining' => [['id' => 4, 'state' => 2, 'textReview' => 'Siempre puedo hacerlo, yo nunca me rindo'],['id' => 5555, 'state' => 1, 'textReview' => null]], 'furtherTraining' =>[['id' => 345, 'state' => 3, 'textReview' => 'Alakazan']]];
        $dataForEmail=['academicTraining' => [['id' => 4, 'surge' => false, 'text' => 'Aloha'],['id' => 5555, 'surge' => true, 'text' => 'faroles']], 'furtherTraining' =>[['id' => 345, 'turbo' => false, 'description' => 'Red is a color']]];
        $transformed = [];
        foreach ($data as $key => $value) {
            foreach ($value as $item) {
                if ($item['state']) {
                    $item['entity'] = $key;
                    $transformed[] = $item;
                }
            }
        }

        foreach($dataForEmail as &$array) {
            foreach($array as &$item ){
                $test = array_filter(
                $transformed,
                function($element) use ($item){
                    return $element['id'] === $item['id'];
                }
            );
            $test = array_pop($test);
            $item['textReview'] = $test['textReview'];
            $item['state'] = $test['state'] === 1 ? 'Aprobado' : ($test['state'] === 2 ? 'Rechazado' : 'Revisar');
            }
        }
        return new JsonResponse($dataForEmail,200,[]);
    }

    // TODO: add notifications
    #[Route('/curriculum-vitae/update-cv', name: 'app_curriculum_vitae_update')]
    public function update(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken, Filesystem $filesystem): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        $id = $request->query->get('id');
        $entity = 'App\\Entity\\'.ucFirst($request->query->get('entity'));
        $entityManager = $doctrine->getManager();
        $entityObj = $entityManager->getRepository($entity)->find($id);
        if (!$entityObj) {
            throw new NotFoundHttpException('No se encontró la entidad');
        }
        $initialHistory = $entityObj->getHistory();
        $initialHistoryArray = json_decode($initialHistory, true);
        if(!in_array(end($initialHistoryArray)['state'], [0,3,4]) && !($user->getSpecialUser() === 'CTH' && $user->getUserType() === 8)){
            throw new AccessDeniedException('No tiene autorización realizar este cambio, por favor comuniquese con Coordinación de talento humano');
        }
        $fieldsToUpdate = $request->request->all();
        $files = $request->files->all();
        $fieldsToUpdate = array_merge($fieldsToUpdate, $files);
        foreach ($fieldsToUpdate as $fieldName => $fieldValue) {
            $dateTime = '';
            if (property_exists($entity, $fieldName)) {
                if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $fieldValue)){
                    $dateTime = new DateTime($fieldValue);
                    $entityObj->{'set' . $fieldName}($dateTime);
                }
                elseif ($fieldValue instanceof UploadedFile) {
                    $filesystem = new Filesystem();
                    $fileToDelete = $entityObj->{'get' . ucfirst($fieldName)}();
                    if($fileToDelete && $filesystem->exists($this->getParameter('hv') . '/' . $fileToDelete)){
                        $filesystem->remove($this->getParameter('hv') . '/' . $fileToDelete);
                    }
                    $directory = $this->getParameter('hv')
                        . '/'
                        . $user->getTypeIdentification()
                        . $user->getIdentification();
                    if (!is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }
                    $fileName =
                        ucfirst($request->query->get('entity')) . '-'
                        . $fieldName . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $fieldValue->guessExtension();
                        $fieldValue->move( $directory, $fileName);
                        $fieldValue = $user->getTypeIdentification().$user->getIdentification().'/'.$fileName;
                    $entityObj->{'set' . $fieldName}($fieldValue);
                } elseif($fieldValue === 'false') {
                    $entityObj->{'set'.$fieldName}(false);
                } else {
                    $entityObj->{'set'.$fieldName}($fieldValue);
                }
            }
        }
        date_default_timezone_set('America/Bogota');
        $initialHistoryArray[] = [
            'state'=>$request->query->get('entity')!== 'ReferencesData' ? 4 : 1,
            'date'=>date('Y-m-d H:i:s'),
            'call'=> NULL];
        $newHistory = json_encode($initialHistoryArray);
        $entityObj->setHistory($newHistory);
        $entityManager->persist($entityObj);
        $entityManager->flush();
        return new JsonResponse(['token' => $token, 'entity' => $entity, 'id'=> $id, 'ftU' =>$user->getIdentification()]);
    }

    #[Route('/curriculum-vitae/create-cv', name: 'app_curriculum_vitae_create')]
    public function create(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $entity = 'App\\Entity\\'.ucFirst($request->query->get('entity'));
        $objEntity = new $entity;
        $user =  $vToken->getUserIdFromToken($token);
        $fieldsToUpdate = $request->request->all();
        $files = $request->files->all();
        $fieldsToUpdate = array_merge($fieldsToUpdate, $files);

        foreach ($fieldsToUpdate as $fieldName => $fieldValue) {
            $dateTime = '';
            if (property_exists($entity, $fieldName)) {
                if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $fieldValue)){
                    $dateTime = new DateTime($fieldValue);
                    $objEntity->{'set' . $fieldName}($dateTime);
                }
                elseif ($fieldValue instanceof UploadedFile) {
                    $directory = $this->getParameter('hv')
                        . '/'
                        . $user->getTypeIdentification()
                        . $user->getIdentification();
                    if (!is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }
                    $fileName =
                        ucfirst($request->query->get('entity')) . '-'
                        . $fieldName . '-'
                        . $user->getTypeIdentification()
                        . $user->getIdentification() . '-'
                        . time() . '.'
                        . $fieldValue->guessExtension();
                    $fieldValue->move( $directory, $fileName);
                    $fieldValue = $user->getTypeIdentification().$user->getIdentification().'/'.$fileName;
                    $objEntity->{'set' . $fieldName}($fieldValue);
                } elseif($fieldValue === 'false') {
                    $objEntity->{'set'.$fieldName}(false);
                } else {
                    $objEntity->{'set'.$fieldName}($fieldValue);
                }
            }
        }
        date_default_timezone_set('America/Bogota');
        $jsonHistory = json_encode([[
            'state'=>$request->query->get('entity')!== 'ReferencesData' ? 0 : 1,
            'date'=>date('Y-m-d H:i:s'),
            'call'=> NULL]]);
        $objEntity->setHistory($jsonHistory);
        $objEntity->setUser($user);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($objEntity);
        $entityManager->flush();
        return new JsonResponse(['status' => 'Success', 'code' => '200', 'message' => 'Nuevo Objeto Creado']);
    }

    #[Route('/test-controller', name: 'app_test_controller')]
    public function test(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        date_default_timezone_set('America/Bogota');
        $initial = "[{\"state\":0,\"date\":\"17-07-2014 17:13:58\",\"call\":null},{\"state\":1,\"date\":\"30-05-2023 17:13:58\",\"call\":3}]";
        $add = json_encode(['state'=>4,'date'=>date('d-m-Y H:i:s'), 'call'=> 7]);
        $result = rtrim($initial, ']').','.$add.']';
        $result = json_decode($result);
        // $result[] = $add;
        var_dump($result);
        return new JsonResponse($result);
    }
    //---------------------------------------------------------------------------------------
    //---------------------------------------------------------------------------------------

    #[Route('/curriculum-vitae/totalWorkedTimeWorkExperience', name:'app_curriculum_totalWorkedTime')]
    public function totalTimeWorkExperience(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);

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

        $workExperienceRepository = $doctrine->getRepository(WorkExperience::class);
        $workExperiences = $workExperienceRepository->findBy(['user'=>$user]);
    
        if (empty($workExperiences)) {
            $response = new JsonResponse('No hay tiempo de trabajo');
            return $response;
        }
    
        $fechas = [];
    
        foreach ($workExperiences as $workExperience) {
            $fechas[] = [
                'admission_date' => $workExperience->getAdmissionDate()->format('d/m/Y'),
                'retirement_date' => $workExperience->getRetirementDate() ? $workExperience->getRetirementDate()->format('d/m/Y') : date('d/m/Y')
            ];
        }
    
        usort($fechas, function ($a, $b) {
            $inicioA = DateTime::createFromFormat('d/m/Y', $a['admission_date']);
            $inicioB = DateTime::createFromFormat('d/m/Y', $b['admission_date']);
    
            return $inicioA <=> $inicioB;
        });
    
        $fechaInicio = DateTime::createFromFormat('d/m/Y', $fechas[0]['admission_date']);
        $fechaFin = DateTime::createFromFormat('d/m/Y', $fechas[count($fechas) - 1]['retirement_date']);
    
        // Calcular el número total de días trabajados
        $totalDiasTrabajados = (($fechaFin->format('Y') - $fechaInicio->format('Y')) * 360) +
                              (($fechaFin->format('n') - $fechaInicio->format('n')) * 30) +
                              ($fechaFin->format('j') - $fechaInicio->format('j'));
    
        // Calcular los espacios donde no se ha trabajado
        $diasSinTrabajar = 0;
        $fechaAnterior = $fechaInicio;
        foreach ($fechas as $fecha) {
            $inicio = DateTime::createFromFormat('d/m/Y', $fecha['admission_date']);
            $fin = DateTime::createFromFormat('d/m/Y', $fecha['retirement_date']);
    
            if ($inicio > $fechaAnterior) {
                $diasSinTrabajar += $fechaAnterior->diff($inicio)->days - 1;
            }
    
            $fechaAnterior = max($fechaAnterior, $fin);
        }
    
        if ($fechaAnterior < $fechaFin) {
            $diasSinTrabajar += $fechaAnterior->diff($fechaFin)->days;
        }
    
        // Restar los espacios sin trabajar al total de días trabajados
        $totalDiasTrabajados -= $diasSinTrabajar;

        if ($totalDiasTrabajados < 0) {
            $totalDiasTrabajados = 0;
        }
        // Obtener los valores de años, meses y días
        $años = floor($totalDiasTrabajados / 360);
        $meses = floor(($totalDiasTrabajados % 360) / 30);
        $dias = $totalDiasTrabajados % 30;

        // Formatear la salida en años, meses y días
        $formato = "";
        if ($años > 0) {
            $formato .= "$años " . ($años == 1 ? "año" : "años") . ", ";
        }
        if ($meses > 0) {
            $formato .= "$meses " . ($meses == 1 ? "mes" : "meses") . ", ";
        }
        if ($dias > 0) {
            $formato .= "$dias " . ($dias == 1 ? "día" : "días");
        }

        $data = [
            'totalTiempoTrabajado' => $formato
        ];
        
        return new JsonResponse($data);
    }
    
    
    //------------------------------------------------------------------------------
    //-- TEACHING EXPERIENCE

    #[Route('/curriculum-vitae/totalWorkedTimeTeachingExperience', name:'app_curriculum_totalWorkedTimeTeaching')]
    public function totalTimeTeachingExperience(ManagerRegistry $doctrine,Request $request,ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);

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

        $teachingExperienceRepository = $doctrine->getRepository(TeachingExperience::class);
        $teachingExperiences = $teachingExperienceRepository->findBy(['user'=>$user]);
    
        if (empty($teachingExperiences)) {
            $response = new JsonResponse('No hay tiempo de trabajo');
            return $response;
        }
    
        $fechas = [];
    
        foreach ($teachingExperiences as $teachingExperience) {
            $fechas[] = [
                'admission_date' => $teachingExperience->getAdmissionDate()->format('d/m/Y'),
                'retirement_date' => $teachingExperience->getRetirementDate() ? $teachingExperience->getRetirementDate()->format('d/m/Y') : date('d/m/Y')
            ];
        }
    
        // Ordenar los rangos de fechas por fecha de admisión
        usort($fechas, function ($a, $b) {
            $inicioA = DateTime::createFromFormat('d/m/Y', $a['admission_date']);
            $inicioB = DateTime::createFromFormat('d/m/Y', $b['admission_date']);
    
            return $inicioA <=> $inicioB;
        });
    
        $fechaInicio = DateTime::createFromFormat('d/m/Y', $fechas[0]['admission_date']);
        $fechaFin = null;
    
        // Buscar la fecha de retiro más reciente
        foreach ($fechas as $fecha) {
            $retirementDate = $fecha['retirement_date'];
            if ($retirementDate !== null) {
                $fin = DateTime::createFromFormat('d/m/Y', $retirementDate);
                if ($fechaFin === null || $fin > $fechaFin) {
                    $fechaFin = $fin;
                }
            }
        }
    
        // Verificar si la fecha de retiro es null
        if ($fechaFin === null) {
            // La persona sigue trabajando actualmente
            $fechaFin = new DateTime(); // Utiliza la fecha actual como fecha de retiro
        }
    
        // Calcular el número total de días trabajados
        $totalDiasTrabajados = (($fechaFin->format('Y') - $fechaInicio->format('Y')) * 360) +
                              (($fechaFin->format('n') - $fechaInicio->format('n')) * 30) +
                              ($fechaFin->format('j') - $fechaInicio->format('j'));
    
        // Calcular los espacios donde no se ha trabajado
        $diasSinTrabajar = 0;
        $fechaAnterior = $fechaInicio;
        foreach ($fechas as $fecha) {
            $inicio = DateTime::createFromFormat('d/m/Y', $fecha['admission_date']);
            $fin = DateTime::createFromFormat('d/m/Y', $fecha['retirement_date']);
    
            if ($inicio > $fechaAnterior) {
                $diasSinTrabajar += $fechaAnterior->diff($inicio)->days - 1;
            }
    
            $fechaAnterior = max($fechaAnterior, $fin);
        }
    
        if ($fechaAnterior < $fechaFin) {
            $diasSinTrabajar += $fechaAnterior->diff($fechaFin)->days;
        }
    
        // Restar los espacios sin trabajar al total de días trabajados
        $totalDiasTrabajados -= $diasSinTrabajar;
    
        if ($totalDiasTrabajados < 0) {
            $totalDiasTrabajados = 0;
        }
    
        // Obtener los valores de años, meses y días
        $años = floor($totalDiasTrabajados / 360);
        $meses = floor(($totalDiasTrabajados % 360) / 30);
        $dias = $totalDiasTrabajados % 30;
    
        // Formatear la salida en años, meses y días
        $formato = "";
        if ($años > 0) {
            $formato .= "$años " . ($años == 1 ? "año" : "años") . ", ";
        }
        if ($meses > 0) {
            $formato .= "$meses " . ($meses == 1 ? "mes" : "meses") . ", ";
        }
        if ($dias > 0) {
            $formato .= "$dias " . ($dias == 1 ? "día" : "días");
        }
    
        $data = [
            'totalTiempoTrabajado' => $formato
        ];
    
        return new JsonResponse($data);
    }

    #[Route('/curriculum-vitae/qualify-cv', name:'app_curriculum_vitae_qualify_cv')]
    public function qualifyCV(ManagerRegistry $doctrine,Request $request,ValidateToken $vToken,MailerInterface $mailer): JsonResponse
    {
        //TODO: I need to let CTH to modify directly on table, and that applies here too
        $token = $request->query->get('token');
        $userCTH =  $vToken->getUserIdFromToken($token);
        if(!$userCTH){
            throw new UserNotFoundException('Usuario no encontrado');
        }
        if($userCTH->getSpecialUser() !== 'CTH' && $userCTH->getUserType() !== 8){
            throw new AccessDeniedException('No tiene permisos para realizar esta acción');
        }
        date_default_timezone_set('America/Bogota');
        $data = json_decode($request->request->get('array'), true);
        $userID = $request->request->get('userID');
        $user = $doctrine->getRepository(User::class)->find($userID);
        $transformed = [];
        foreach ($data as $key => $value) {
            foreach ($value as $item) {
                if ($item['state']) {
                    $item['entity'] = $key;
                    $transformed[] = $item;
                }
            }
        }
        foreach ($transformed as $key => $value) {
            $id = $value['id'];
            // TODO: Fucking fix this in frontend
            if($value['entity'] === 'Records' || $value['entity'] === 'records'){
                $entity = 'App\\Entity\\Record';
            } else {
                $entity = 'App\\Entity\\'.ucFirst($value['entity']);
            }
            $entityManager = $doctrine->getManager();
            $entityObj = $entityManager->getRepository($entity)->find($id);
            $history = $entityObj->getHistory();
            $historyArray = json_decode($history, true);
            $historyArray[] = ['state'=>$value['state'], 'reviewText'=>$value['reviewText'], 'date'=> date('Y-m-d H:i:s'), 'call'=> NULL];
            $newHistory = json_encode($historyArray);
            $entityObj->setHistory($newHistory);
        }
        $qb = function($class, $ids) use ($doctrine) {
            return $doctrine->getRepository($class)
                ->createQueryBuilder('e')
                ->andWhere('e.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getArrayResult();
        };
        $dataForEmail = [
            'personalData' => $qb(PersonalData::class, array_column($data['personalData'], 'id')),
            'academicTraining' => $qb(AcademicTraining::class, array_column($data['academicTraining'], 'id')),
            'furtherTraining' => $qb(FurtherTraining::class, array_column($data['furtherTraining'], 'id')),
            'language' => $qb(Language::class, array_column($data['language'], 'id')),
            'workExperience' => $qb(WorkExperience::class, array_column($data['workExperience'], 'id')),
            'teachingExperience' => $qb(TeachingExperience::class, array_column($data['teachingExperience'], 'id')),
            'intellectualProduction' => $qb(IntellectualProduction::class, array_column($data['intellectualProduction'], 'id')),
            // 'references' => $qb(ReferencesData::class, array_column($data['references'], 'id')),
            'records' => $qb(Record::class, array_column($data['records'], 'id')),
        ];
        foreach($dataForEmail as &$array) {
            foreach($array as &$item ){
                $found = array_filter(
                $transformed,
                function($element) use ($item){
                    return $element['id'] === $item['id'];
                }
            );
            $found = array_pop($found);
            $item['reviewText'] = $found['reviewText'];
            $item['state'] = $found['state'] === 1 ? 'Aprobado' : ($found['state'] === 2 ? 'Rechazado' : 'Revisar');
            }
        }
        try{
            $email = (new TemplatedEmail())
                ->from('convocatorias@unicatolicadelsur.edu.co')
                ->to($user->getEmail())
                ->subject('Revisión')
                ->htmlTemplate('email/qualifyEmployeeCVEmail.html.twig')
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
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha enviado un correo a este trabajador']);
    }

    // TODO: chat says this is vulnerable to sql injection, so change this when posible
    #[Route('/curriculum-vitae/get-unqualified-cv', name:'app_curriculum_vitae_unqualified_cv')]
    public function getUnqualifiedCV(ManagerRegistry $doctrine,Request $request,ValidateToken $vToken): JsonResponse
    {
        $queryOneCV = function($class, $id) use ($doctrine){
            $sql = "
                SELECT *
                FROM $class
                WHERE (
                    user_id = $id
                    AND
                    JSON_EXTRACT(
                        JSON_EXTRACT(
                            history,
                            CONCAT('$[', JSON_LENGTH(history) - 1, ']'))
                    ,'$.state') IN (0, 4));
            ";
            $connectionCV = $doctrine->getManager()->getConnection();
            $resultSetCV = $connectionCV->executeQuery($sql);
            $results = $resultSetCV->fetchAllAssociative();
            $camelCaseResults = [];
            foreach ($results as $result) {
                $camelCaseResult = [];
                foreach ($result as $key => $value) {
                    // Convert snake_case to camelCase
                    $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
                    $camelCaseResult[$camelCaseKey] = $value;
                }
                $camelCaseResults[] = $camelCaseResult;
            }
        
            return $camelCaseResults;
        };
            
        $token = $request->query->get('token');
        $idOffset = $request->query->get('idOffset');
        $user =  $vToken->getUserIdFromToken($token);
        if(!$user){
            throw new UserNotFoundException('Usuario no encontrado');
        }
        if($user->getSpecialUser() !== 'CTH' && $user->getUserType() !== 8){
            throw new AccessDeniedException('No tiene permisos para realizar esta acción');
        }

        $sqlEmp = "
            SELECT 
                id,
                CONCAT(names, ' ', last_names) as fullname,
                type_identification as typeIdentification,
                identification,
                email,
                phone,
                user_type,
                CASE 
                    WHEN user_type = 1 THEN 'Administrador'
                    WHEN user_type = 2 THEN 'Profesor'
                    WHEN user_type = 8 THEN 'Administrador'
                    ELSE 'Otro'
                END AS userType
            FROM `user`
            WHERE user_type IN (1,2,8);
        ";

        $connectionEmp = $doctrine->getManager()->getConnection();
        $resultSetEmp = $connectionEmp->executeQuery($sqlEmp);
        $allEmployees = $resultSetEmp->fetchAllAssociative(); 

        $filteredEmployees = [];
        
        foreach ($allEmployees as &$employee) {
            $response = [
                'personalData' => parseCvData($queryOneCV('personal_data', $employee['id'])),
                'academicTraining' => parseCvData($queryOneCV('academic_training', $employee['id'])),
                'furtherTraining' => parseCvData($queryOneCV('further_training', $employee['id'])),
                'language' => parseCvData($queryOneCV('language', $employee['id'])),
                'workExperience' => parseCvData($queryOneCV('work_experience', $employee['id'])),
                'teachingExperience' => parseCvData($queryOneCV('teaching_experience', $employee['id'])),
                'intellectualProduction' => parseCvData($queryOneCV('intellectual_production', $employee['id'])),
                // 'references' => parseCvData($queryOneCV('references_data', $employee['id'])),
                'records' => parseCvData($queryOneCV('record', $employee['id']))
            ];

            $filteredResponse = array_filter($response, function ($value) {
                return !empty($value);
            });
            
            $employee['unqualifiedCV'] = $filteredResponse;

            if (!empty($employee['unqualifiedCV'])) {
                $filteredEmployees[] = $employee;
            }

            foreach($filteredEmployees as &$employee) {
                $totalLength = 0;
                foreach($employee['unqualifiedCV'] as $section) {
                    $totalLength += count($section);
                }
                $employee['uCVlength'] = $totalLength;
            }
        }
        
        return new JsonResponse($filteredEmployees);
        
    }

    //TODO: create a state 5 for CV

    #[Route('/curriculum-vitae/request-change-cv', name: 'app_curriculum_vitae_request_change_cv')]
    public function requestChangeCv(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken, MailerInterface $mailer): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        if (!$user) {
            throw new UserNotFoundException('Usuario no encontrado');
        }
        $data = $request->request->all();
        $newNotification = new Notification();
        $newNotification->setSeen(0);
        $newNotification->setUser($doctrine->getRepository(User::class)->findOneBy(['specialUser' => 'CTH', 'userType' => '8']));
        $relatedEntity = array(
            'id' => $data['id'],
            'applicantId' => $user->getId(),
            'applicantName' => $user->getNames() . ' ' . $user->getLastNames(),
            'entity' => 'requestCV',
            'cvSection' => $data['entity'],
            'message' => $data['message'] ?? NULL
        );
        try{
            $email = (new TemplatedEmail())
                ->from('talento.humano@unicatolicadelsur.edu.co')
                ->to('talento.humano@unicatolicadelsur.edu.co')
                ->subject('Revisión de Hoja de vida')
                ->htmlTemplate('email/requestForCVModificationEmail.html.twig')
                ->context([
                    'user' => $user,
                ]); 
            $mailer->send($email);
            $message = 'La revisión fue enviada con éxito';
        } catch (\Throwable $th){
            $message = 'Error al enviar el correo:'.$th->getMessage();
            return new JsonResponse(['status'=>'Error','message'=>$message]);
        }
        $newNotification->setRelatedEntity(json_encode($relatedEntity));
        $newNotification->setMessage('solicita acceso para cambiar su hoja de vida');
        // TODO: consider send email
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newNotification);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Se ha enviado una notificación a Coordinación de Talento humano'
        . ', quién decidirá el futuro de esta consulta.']);
    }

    #[Route('/curriculum-vitae/get-single-item-cv', name: 'app_curriculum_vitae_get_single_item_cv')]
    public function getSingleItemCV(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken): JsonResponse
    {
        $token = $request->query->get('token');
        $entityName = $request->query->get('entityName');
        $entityId = $request->query->get('entityId');
        $user =  $vToken->getUserIdFromToken($token);
        if (!$user) {
            throw new UserNotFoundException('Usuario no encontrado');
        }
        $query = $doctrine->getManager()->createQueryBuilder();
        $query
            ->select('e')
            ->from('App\Entity\\'.$entityName, 'e')
            ->where('e.id = :id')
            ->setParameter('id', $entityId);
        $item = $query->getQuery()->getArrayResult();
        $item = parseCvData($item);
        $item = $item[0];
        // $item['history'] = json_decode($item['history'], true);
        // $item['history'] = end($item['history']);
        return new JsonResponse($item);
    }

    #[Route('/curriculum-vitae/approve-change-in-cv', name: 'app_curriculum_vitae_approve_change_in_cv')]
    public function approveChangeInCV(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken, MailerInterface $mailer): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        if(!$user) {
            throw new UserNotFoundException('Usuario no encontrado');
        }
        if(!($user->getSpecialUser() === 'CTH' || $user->getUserType() === 8)){
            throw new AccessDeniedException('El usuario no tiene autorización para realizar este cambio');
        }
        $entityName = $request->request->get('entityName');
        $entityId = $request->request->get('entityId');
        $notificationId = $request->request->get('notificationId');
        $item = $doctrine->getRepository('App\Entity\\'.$entityName)->find($entityId);
        $targetUser = $item->getUser();
        try{
            $email = (new TemplatedEmail())
                ->from('talento.humano@unicatolicadelsur.edu.co')
                ->to('talento.humano@unicatolicadelsur.edu.co')
                ->subject('Revisión de Hoja de vida')
                ->htmlTemplate('email/requestForCVModificationEmail.html.twig')
                ->context([
                    'user' => $targetUser,
                    'entityName' => $entityName,
                    'item' => $item
                ]); 
            $mailer->send($email);
            $message = 'La revisión fue enviada con éxito';
        } catch (\Throwable $th){
            $message = 'Error al enviar el correo:'.$th->getMessage();
            return new JsonResponse(['status'=>'Error','message'=>$message]);
        }
        $notification = $doctrine->getRepository(Notification::class)->find($notificationId);
        $itemHistory = $item->getHistory();
        $itemHistory = json_decode($itemHistory, true);
        $itemHistory[] = [
            'state' => 3,
            'reviewText' => 'A la espera de un cambio',
            'date'=>date('Y-m-d H:i:s'),
            'call'=> NULL
        ];
        $item->setHistory(json_encode($itemHistory));
        $notification->setSeen(1);
        $entityManager = $doctrine->getManager();
        $entityManager->flush();       
        return new JsonResponse(['message' => 'El cambio de este ítem fue aprobado con éxito.']);
    }

    #[Route('/curriculum-vitae/reject-change-in-cv', name: 'app_curriculum_vitae_reject_change_in_cv')]
    public function rejectChangeInCV(ManagerRegistry $doctrine, Request $request, ValidateToken $vToken, MailerInterface $mailer): JsonResponse
    {
        $token = $request->query->get('token');
        $user =  $vToken->getUserIdFromToken($token);
        if(!$user) {
            throw new UserNotFoundException('Usuario no encontrado');
        }
        if(!($user->getSpecialUser() === 'CTH' || $user->getUserType() === 8)){
            throw new AccessDeniedException('El usuario no tiene autorización para realizar este cambio');
        }
        $entityName = $request->request->get('entityName');
        $entityId = $request->request->get('entityId');
        $notificationId = $request->request->get('notificationId');
        $item = $doctrine->getRepository('App\Entity\\'.$entityName)->find($entityId);
        $targetUser = $item->getUser();
        $notification = $doctrine->getRepository(Notification::class)->find($notificationId);
        $itemHistory = $item->getHistory();
        $itemHistory = json_decode($itemHistory, true);
        $itemHistory[] = [
            'state' => end($itemHistory)['state'],
            'reviewText' => 'Se ha rechazado el cambio de este ítem',
            'date'=>date('Y-m-d H:i:s'),
            'call'=> NULL
        ];
        $item->setHistory(json_encode($itemHistory));
        $notification->setSeen(1);
        $entityManager = $doctrine->getManager();
        $entityManager->flush();       
        return new JsonResponse(['message' => 'El cambio de este ítem fue rechazado por Coordinación de talento humano.']);
    }

    #[Route('/curriculum-vitae/download-cv', name: 'app_files_merge_pdfs')]
    public function mergePdfs(ManagerRegistry $doctrine , Request $request)
    {
        $queryCv =  function($class, $id) use ($doctrine){
            $sql = "SELECT *
                    FROM $class
                    WHERE (
                        user_id = $id
                        AND
                        JSON_EXTRACT(
                            JSON_EXTRACT(
                                history,
                                CONCAT('$[', JSON_LENGTH(history) - 1, ']'))
                        ,'$.state') = 1);";
            $connectionCV = $doctrine->getManager()->getConnection();
            $resultSetCV = $connectionCV->executeQuery($sql);
            $results = $resultSetCV->fetchAllAssociative();
            $camelCaseResults = [];
            foreach ($results as $result) {
                $camelCaseResult = [];
                foreach ($result as $key => $value) {
                    $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
                    $camelCaseResult[$camelCaseKey] = $value;
                }
                $camelCaseResults[] = $camelCaseResult;
            }
        
            return $camelCaseResults;

        };

        $userId = $request->query->get('userId');
        $user = $doctrine->getRepository(User::class)->find($userId);
        $cvData = [
            'user' => [
                'fullname' => $user->getNames() . ' ' . $user->getLastNames(),
                'fullidentification' => $user->getTypeIdentification() . ' ' . $user->getIdentification(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone()
            ],
            'personalData' => parseCvData($queryCv('personal_data', $userId)),
            'academicTraining' => parseCvData($queryCv('academic_training', $userId)),
            'furtherTraining' => parseCvData($queryCv('further_training', $userId)),
            'language' => parseCvData($queryCv('language', $userId)),
            'workExperience' => parseCvData($queryCv('work_experience', $userId)),
            'teachingExperience' => parseCvData($queryCv('teaching_experience', $userId)),
            'records' => parseCvData($queryCv('record', $userId))
        ];
        usort($cvData['academicTraining'], fn($a, $b) => $b['date'] <=> $a['date']);
        usort($cvData['furtherTraining'], fn($a, $b) => $b['date'] <=> $a['date']);
        usort($cvData['workExperience'], fn($a, $b) => $b['admissionDate'] <=> $a['admissionDate']);
        usort($cvData['teachingExperience'], fn($a, $b) => $b['admissionDate'] <=> $a['admissionDate']);
        $entitiesForAnnexes =
        [
            'PersonalData' => [
                'entity' => 'personal_data',
                'alias' => 'pd',
                'fields' => [
                    'identification_pdf', 'eps_pdf', 'pension_pdf',
                    'bank_account_pdf', 'rut_pdf', 'severance_fund_pdf'
                ],
            ],
            'AcademicTraining' => [
                'entity' => 'academic_training',
                'alias' => 'at',
                'fields' => [
                    'degree_pdf', 'diploma_pdf', 'certified_title_pdf'
                ],
            ],
            'FurtherTraining' => [
                'entity' => 'further_training',
                'alias' => 'ft',
                'fields' => ['certified_pdf'],
            ],
            'Language' => [
                'entity' => 'language',
                'alias' => 'lg',
                'fields' => ['certified_pdf'],
            ],
            'WorkExperience' => [
                'entity' => 'work_experience',
                'alias' => 'we',
                'fields' => ['certified_pdf'],
            ],
            'TeachingExperience' => [
                'entity' => 'teaching_experience',
                'alias' => 'te',
                'fields' => ['certified_pdf'],
            ],
            'Record' => [
                'entity' => 'record',
                'alias' => 'r',
                'fields' => [
                    'tax_record_pdf', 'judicial_record_pdf',
                    'disciplinary_record_pdf', 'corrective_measures_pdf'
                    ]
                ]
        ];

        $results = [];

        foreach ($entitiesForAnnexes as $key => $entityData) {
            $fields = implode(', ', array_map(fn($field) => "{$entityData['alias']}.$field", $entityData['fields']));
            $sql = "SELECT $fields
                    FROM {$entityData['entity']} {$entityData['alias']}
                    WHERE (
                        {$entityData['alias']}.user_id = $userId
                        AND
                        JSON_EXTRACT(
                            JSON_EXTRACT(
                                history,
                                CONCAT('$[', JSON_LENGTH(history) - 1, ']'))
                        ,'$.state') = 1)";

            $connectionCV = $doctrine->getManager()->getConnection();
            $stmt = $connectionCV->executeQuery($sql);
            $results[$key] = $stmt->fetchAllAssociative();
        }

        $htmlContent = $this->renderView('pdf/cv-template.html.twig', [
            'cvData' => $cvData
        ]);

        $pdfContent = $this->pdfService->generatePdf($htmlContent);
        
        $flattenAndFilterArray = flattenArray($results);
        array_walk_recursive($flattenAndFilterArray, function (&$value) {
            if ($value) {
                $value = $this->getParameter('hv') . '/' . $value;
            }
        });
        $binPath = $_ENV['GS_BIN_PATH'];
        if(!file_exists($binPath)){
            return new JsonResponse(['Message' => 'Ghostscript not found']);
        }
        $tmpPath = sys_get_temp_dir();
        $cvDataPath = tempnam($tmpPath, 'pdf_') . '.pdf';
        file_put_contents($cvDataPath, $pdfContent);
        $pdfName = 'hoja de vida de ' . $user->getNames() . ' ' . $user->getLastNames() . '.pdf';
        $outputFilePath = $tmpPath . '/' . $pdfName;
        try {
            $ghostscript = new Ghostscript($binPath, $tmpPath);
            $ghostscript->merge(
                $tmpPath,
                $pdfName,
                array_merge(['cvDataPath' => $cvDataPath], $flattenAndFilterArray)
            );
            return $this->file($outputFilePath, $pdfName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        } catch (\Throwable $th) {
            return new JsonResponse(['error' => $th->getMessage()], 500);
        } finally {
            $ghostscript->clearTmpFiles();
        }

        return new JsonResponse(['message' => $cvData]);
    }
}