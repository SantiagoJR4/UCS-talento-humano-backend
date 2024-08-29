<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            $decodedHistory = json_decode($value['history']);
            $value['history'] = end($decodedHistory);
        }
        return new JsonResponse($employees, 200, []);
    }
}
