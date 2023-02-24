<?php

namespace App\Controller;

use App\Service\Helpers;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/login', name:'user_login')]
    public function loginUser(ManagerRegistry $doctrine, Helpers $helpers, Request $request, JWTTokenManagerInterface $JWTManager): Response{
        $parameters = json_decode($request->getContent(), true);

        $passHash = hash('sha256', $parameters['password']);

        $user = $doctrine->getRepository(User::class)->findOneBy([
            'identification' => $parameters['identification'],
            'password' => $passHash
        ]);

        if($user === NULL){
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('El usuario y contraseña son incorrectos!!');
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }

        // $authorizationHeader = $request->headers->get('Authorization');
        // $token = str_replace('Bearer', '', $authorizationHeader);

        // $decodedToken = $JWTManager->decode($token);

        // $token = $JWTManager->create($user);
        
        // return new Response(json_encode([
        //     'token' => $token,
        // ]), 200,  ['content-Type' => 'application/json']);

        
        //meter validación si se necesita evaluar el estado 200
        //Si es necesario, no enviar la contraseña en la respuesta
        $response=new Response();
        $json = $helpers->serializador($response);
        $response->setContent(json_encode(['respuesta' => $json]));

        return $json;
    }

    #[Route('/register', name:'user_register')]
    public function registerUser(ManagerRegistry $doctrine): Response
    {
        $request = Request::createFromGlobals();
        $dataRegister = json_decode($request->getContent(), true);

        $data_db = $doctrine->getRepository(User::class)->findOneBy([
            'typeIdentification' => $dataRegister['typeIdentification'],
            'identification' => $dataRegister['identification']
        ]);
        if($data_db !== NULL){
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('El usuario ya existe!!');
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else{
            $userData = new User();
            $userData->setNames($dataRegister['names']);
            $userData->setLastNames($dataRegister['lastNames']);
            $userData->setTypeIdentification($dataRegister['typeIdentification']);
            $userData->setIdentification($dataRegister['identification']);
            $userData->setEmail($dataRegister['email']);
            $userData->setPhone($dataRegister['phone']);
            $userData->setPassword(hash('sha256',$dataRegister['password']));
    
            $entityManager=$doctrine->getManager();
            $entityManager->persist($userData);
            $entityManager->flush();
    
            $response= new Response();
            $response->setContent(json_encode(['respuesta' => 'Usuario registrado exitosamente']));
            $response->headers->set('Content-Type', 'application/json');
    
            return $response;
        }
    }

    //TODO : HACER VERIFICACIÓN DE CORREO

    // #[Route('/verifyEmail', name:'user_verifyemail')]
    // public function verifyEmail(Request $request, Swift_Mailer $mailer)
    // {
    //     $email = $request->request->get('email');
    //     $names = $request->request->get('names');

    //     $message = (new Swift_Message)
    // }

}
