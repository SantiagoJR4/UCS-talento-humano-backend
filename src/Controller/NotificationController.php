<?php

namespace App\Controller;

use App\Service\ValidateToken;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/get-notifications', name: 'app_get_notifications')]
    public function getNotifications(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
    {
        $token= $request->query->get('token');
        if($token === 'null' || $token === NULL || $token ==='')
        {
            return new JsonResponse([], 200, []);
        }
        $user =  $vToken->getUserIdFromToken($token);
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('n.id', 'n.message', 'n.seen', 'n.relatedEntity', 'u.names', 'u.lastNames')
            ->from('App\Entity\Notification', 'n')
            ->leftJoin('n.user', 'u')
            ->andWhere('n.user = :user')
            ->andWhere('n.seen = false')
            ->orderBy('n.id', 'DESC')
            ->setMaxResults(5)
            ->setParameter('user', $user);
        $allNotification = $query->getQuery()->getArrayResult();
        foreach($allNotification as &$value)
        {
            $value['relatedEntity'] = json_decode($value['relatedEntity'], true);
            $value['names'] = $value['names'] . ' ' . $value['lastNames'];
            unset($value['names']);
            unset($value['lastNames']);
        }
        return new JsonResponse($allNotification, 200, []);
    }

    #[Route('/get-single-notification', name: 'app_get_single_notification')]
    public function getSingleNotificationn(ManagerRegistry $doctrine, ValidateToken $vToken, Request $request): JsonResponse
    {
        $token= $request->query->get('token');
        if($token === 'null' || $token === NULL || $token ==='')
        {
            return new JsonResponse(['message' => 'There is not Token'], 403, []);
        }
        $notificationId = $request->query->get('notificationId');
        $user =  $vToken->getUserIdFromToken($token);
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('n.id', 'n.message', 'n.seen', 'n.relatedEntity', 'u.names', 'u.lastNames')
            ->from('App\Entity\Notification', 'n')
            ->leftJoin('n.user', 'u')
            ->where('n.user = :user AND n.id = :notificationId')
            ->setParameter('user',$user)
            ->setParameter('notificationId',$notificationId);
        try {
            $notification = $query->getQuery()->getSingleResult();
            $notification['relatedEntity'] = json_decode($notification['relatedEntity'], true);
            $notification['fullName'] = $notification['names'] . ' ' . $notification['lastNames'];
            unset($notification['names']);
            unset($notification['lastNames']);
            return new JsonResponse($notification, 200, []);
        } catch (NoResultException $e) {
            return new JsonResponse(['message' => 'Esta notificación no corresponde o no existe'], 400, []);
        }
       
    }
}
