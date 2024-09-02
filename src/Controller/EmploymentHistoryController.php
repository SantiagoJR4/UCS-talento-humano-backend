<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

function setTagEmployee($status){
    $statusMap = array(
        0 => array('severity' => 'info', 'icon' => 'refresh', 'value' => 'Novedades'),
        1 => array('severity' => '', 'icon' => 'check', 'value' => 'Terminado'),
        3 => array('severity' => 'warning', 'icon' => 'hourglass_top', 'value' => 'En espera')
    );
    return !!$status && isset($statusMap[$status])
        ? $statusMap[$status]
        : array('severity' => 'info', 'icon' => 'refresh', 'value' => 'Novedades');
}

class EmploymentHistoryController extends AbstractController
{
    #[Route('/get-employees', name: 'app_get_employees')]
    public function getEmployees(ManagerRegistry $doctrine): JsonResponse
    {
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('u.id', 'u.names', 'u.lastNames', 'u.identification', 'u.email', 'u.phone', 'u.userType', 'u.history')
        ->from('App\Entity\User', 'u')
        ->where('u.userType IN (:userType)')
        ->setParameter('userType', [1,2,8]);
        $employees = $query->getQuery()->getArrayResult();
        foreach ($employees as &$value) {
            // $value['userType'] = strval($value['userType']);
            $value['fullname'] = $value['names'] . ' ' . $value['lastNames'];
            unset($value['names']);
            unset($value['lastNames']);
            switch ($value['userType']) {
                case 1:
                    $value['userType'] = 'Administrativo';
                    break;
                case 2:
                    $value['userType'] = 'Profesor';
                    break;
                
                default:
                    $value['userType'] = 'Administrativo';
                    break;
            }
            $decodedHistory = json_decode($value['history'], true);
            $value['history'] = end($decodedHistory);
            $value['tag'] = setTagEmployee(end($decodedHistory) ? end($decodedHistory)['state'] : false);
        }
        usort($employees, function($a, $b){
            if (!isset($a['history']['date'])) return 1;
            if (!isset($b['history']['date'])) return -1;
            return $b['history']['date'] <=> $a['history']['date'];
        });
        return new JsonResponse($employees, 200, []);
    }

    #[Route('/get-single-employee', name: 'app_get_single_employee')]
    public function getSingleEmployee(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('u.id', 'u.names', 'u.lastNames', 'u.identification', 'u.email', 'u.phone', 'u.userType', 'u.history')
        ->from('App\Entity\User', 'u')
        ->where('u.id = :userId')
        ->setParameter('userId', $userId);
        $employee = $query->getQuery()->getSingleResult();
        $employee['fullname'] = $employee['names'] . ' ' . $employee['lastNames'];
        unset($employee['names']);
        unset($employee['lastNames']);
        switch ($employee['userType']) {
            case 1:
                $employee['userType'] = 'Administrativo';
                break;
            case 2:
                $employee['userType'] = 'Profesor';
                break;
            
            default:
                $employee['userType'] = 'Administrativo';
                break;
        }
        $decodedHistory = json_decode($employee['history'], true);
        $employee['history'] = end($decodedHistory);
        $employee['tag'] = setTagEmployee(end($decodedHistory) ? end($decodedHistory)['state'] : false);
        return new JsonResponse($employee, 200, []);
    }
}
