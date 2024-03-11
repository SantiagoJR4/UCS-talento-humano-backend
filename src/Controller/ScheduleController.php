<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Entity\Period;
use App\Entity\Programas;
use App\Entity\Schedule;
use DateTime;
use DateTimeZone;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class ScheduleController extends AbstractController
{
    #[Route('/schedule/get-classrooms', name: 'app_schedule_get_classrooms')]
    public function getClassrooms(ManagerRegistry $doctrine): JsonResponse
    {
        $query = $doctrine->getManager()->createQueryBuilder();
        $query
            ->select('c')
            ->from('App\Entity\Classroom', 'c');
        $allClassrooms = $query->getQuery()->getArrayResult();
        return new JsonResponse($allClassrooms, 200, []);
    }

    #[Route('/schedule/get-programs', name: 'app_schedule_get_programs')]
    public function getPrograms(ManagerRegistry $doctrine): JsonResponse
    {
        $query = $doctrine->getManager()->createQueryBuilder();
        $query
            ->select('p.id', 'p.nombre as fullname', 'p.acronym as name', 'p.defaultColor', 'p.highlightColor')
            ->from('App\Entity\Programas', 'p');
        $allPrograms = $query->getQuery()->getArrayResult();
        return new JsonResponse($allPrograms, 200, []);
    }

    #[Route('/schedule/get-periods', name: 'app_schedule_get_periods')]
    public function getPeriods(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $date = new DateTime(date('Y-m-d'), new DateTimeZone('America/Bogota'));
        $query = $doctrine->getManager()->createQueryBuilder();
        $query
            ->select('p')
            ->from('App\Entity\Period', 'p')
            ->where(':date BETWEEN p.start AND p.end')
            ->andWhere('p.personalized = 0')
            ->setParameter('date', $date);
        $availablePeriods = $query->getQuery()->getArrayResult();
        return new JsonResponse($availablePeriods, 200, []);
    }

    #[Route('/schedule/get-schedules', name: 'app_schedule_get_schedules')]
    public function getsShedules(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $date = $request->query->get('date');
        $formattedDate = new DateTime($date, new DateTimeZone('America/Bogota'));
        $day = $formattedDate->format('w');
        $queryM = $doctrine->getManager()->createQueryBuilder();
        $queryM
            ->select('c')
            ->from('App\Entity\Classroom', 'c');
        $allClassrooms = $queryM->getQuery()->getArrayResult();
        $mappedSchedules = [];
        foreach($allClassrooms as $key => $classroom){
            $mappedSchedules[] = [
                'classroomId' => $classroom['id'],
                'classroomNumber' => $classroom['classroomNumber'],
                'classroomName' => $classroom['name'],
                'classroomMaximumCapacity' => $classroom['maximumCapacity'],
                'classroomRecommendedCapacity' => $classroom['recommendedCapacity'],
                'h_07' => null,
                'h_08' => null,
                'h_09' => null,
                'h_10' => null,
                'h_11' => null,
                'h_12' => null,
                'h_13' => null,
                'h_14' => null,
                'h_15' => null,
                'h_16' => null,
                'h_17' => null,
                'h_18' => null,
            ];
        }

        $queryS = $doctrine->getManager()->createQueryBuilder();
        $queryS
            ->select('s.id', 's.title', 'c.id as classroomId', 'pr.acronym as programAcronym',
                's.semester', 's.personInCharge', 's.start', 's.end', 'pe.start as periodStart', 'pe.end as periodEnd', 's.oneDay')
            ->from('App\Entity\Schedule', 's')
            ->join('s.classroom', 'c')
            ->join('s.program', 'pr')
            ->leftjoin('s.period', 'pe')
            ->where(':day = s.day')
            ->andWhere($queryS->expr()->orX(
                ':date = s.oneDay',
                ':date BETWEEN pe.start AND pe.end'
            ))
            ->setParameters([
                'date' => $formattedDate,
                'day' => $day
            ]);
        $allSchedules = $queryS->getQuery()->getArrayResult();

        foreach ($allSchedules as $keyS => $schedule) {
            $indexOfMappedSchedules = -1;
            foreach($mappedSchedules as $keyM => $mappedS ){
                if($mappedS['classroomId'] === $schedule['classroomId']){
                    $indexOfMappedSchedules = $keyM;
                    break;
                }
            }
            for($x = $schedule['start'];$x < $schedule['end']; $x++){
                $keyToChange = 'h_' . str_pad($x, 2, '0', STR_PAD_LEFT);
                $mappedSchedules[$indexOfMappedSchedules][$keyToChange] = [
                    'id' => $schedule['id'],
                    'name' => $schedule['title'],
                    'personInCharge' => $schedule['personInCharge'],
                    'program' => $schedule['programAcronym'],
                    'semester' => $schedule['semester'],
                    'hours' => $schedule['start'].':00-'.$schedule['end'].':00'
                ];
            }
        }

        return new JsonResponse($mappedSchedules, 200, []);
    }

    #[Route('schedule/new-schedule', name: 'app_schedule_new_schedule')]
    public function newSchedule(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = $request->request->all();
        $entityManager = $doctrine->getManager();
        $newSchedule =  new Schedule();
        $formattedDate = new DateTime($data['date'], new DateTimeZone('America/Bogota'));
        $day = $formattedDate->format('w');
        $newSchedule->setDay($day);
        if(json_decode($data['isRepeatable'], true)){
            if(json_decode($data['period'], true) === 0){
                $newPeriod = new Period();
                $newPeriod->setName('Personalized' . date("Y-m-d H:i:s"));
                $periodStart = new DateTime($data['newPeriodStart'], new DateTimeZone('America/Bogota'));
                $newPeriod->setStart($periodStart);
                $periodEnd = new DateTime($data['newPeriodEnd'], new DateTimeZone('America/Bogota'));
                $newPeriod->setEnd($periodEnd);
                $newPeriod->setPersonalized(1);
                $entityManager->persist($newPeriod);
                $entityManager->flush();
                $newSchedule->setPeriod($newPeriod);
            }
            else {
                $period = $doctrine->getRepository(Period::class)->find($data['period']);
                $newSchedule->setPeriod($period);
            }
        } else {
            $newSchedule->setOneDay($formattedDate);
        }
        $newSchedule->setStart($data['startHour']);
        $newSchedule->setEnd($data['endHour']);
        $classroom = $entityManager->getRepository(Classroom::class)->find($data['classroomId']);
        $newSchedule->setClassroom($classroom);
        $program = $entityManager->getRepository(Programas::class)->find($data['programId']);
        $newSchedule->setProgram($program);
        $newSchedule->setTitle($data['title']);
        $newSchedule->setPersonInCharge($data['personInCharge']);
        $newSchedule->setSemester($data['semester']);
        $entityManager->persist($newSchedule);
        $entityManager->flush();

        return new JsonResponse(['data' => 'Nuevo ítem de horario registrado'], 200, []);
    }

    #[Route('schedule/delete-schedule', name: 'app_schedule_delete_schedule')]
    public function deleteSchedule(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $scheduleId = $request->query->get('scheduleId');
        $entityManager = $doctrine->getManager();
        $scheduleToDelete = $doctrine->getRepository(Schedule::class)->find($scheduleId);
        $periodToDelete = $scheduleToDelete->getPeriod();
        if($periodToDelete && $periodToDelete->isPersonalized()){
            $entityManager->remove($periodToDelete);
            $entityManager->flush();
        }
        $entityManager->remove($scheduleToDelete);
        $entityManager->flush();
        return new JsonResponse(['status' => 'success' ,'data' => 'Horario borrado satsfactoriamente'],200,[]);
    }

    #[Route('schedule/update-schedule', name: 'app_schedule_update_schedule')]
    public function updateSchedule(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = $request->request->all();
        return new JsonResponse($data, 200, []);
    }

    #[Route('schedule/tests', name: 'app_schedule_new_tests')]
    public function tests(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $queryTest = $doctrine->getManager()->createQueryBuilder();
        $queryTest
            ->select('s.id', 's.title', 'c.id as classroomId', 'pr.acronym as programAcronym',
            's.semester', 's.personInCharge', 's.start', 's.end', 'pe.start as periodStart', 'pe.end as periodEnd', 's.oneDay')
            ->from('App\Entity\Schedule', 's')
            ->join('s.classroom', 'c')
            ->join('s.program', 'pr')
            ->leftjoin('s.period', 'pe');
            // ->where(':date = s.oneDay')
            // ->setParameter('date', $formattedDate);
        $test = $queryTest->getQuery()->getArrayResult();
        return new JsonResponse($test, 200, []);
    }
}
