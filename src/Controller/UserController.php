<?php

namespace App\Controller;

use App\Service\Helpers;
use App\Entity\User;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/listarUser/{id}', name: 'app_user')]
    public function user(ManagerRegistry $doctrine, Helpers $helpers, int $id): Response
    {
        $datosUser = $doctrine->getRepository(User::class)->find($id);

        $json = $helpers->serializador($datosUser);
        return $json;
    }

    #[Route('/login', name:'user_login')]
    public function loginUser(ManagerRegistry $doctrine, Helpers $helpers, Request $request): Response{
        $parameters = json_decode($request->getContent(), true);
        $user = $doctrine->getRepository(User::class)->findOneBy([
            'username' => $parameters['username'],
            'contraseña' => $parameters['password']
        ]);

        $json = $helpers->serializador($user);
        return $json;

    }
}
