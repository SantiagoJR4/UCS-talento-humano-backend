<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        return new JsonResponse($employees, 200, []);
    }
}
