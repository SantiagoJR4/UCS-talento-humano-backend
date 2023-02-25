<?php

namespace App\Controller;

use App\Entity\CurriculumVitae;
use App\Service\Helpers;
use App\Entity\User;

use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
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

    #[Route('/listarCurriculmVitae/{id}', name:'app_listar_curriculumVitae')]
    public function listCurriculumVitae(ManagerRegistry $doctrine, Helpers $helpers,int $id): Response
    {
        $curriculumVitaeData = $doctrine->getRepository(CurriculumVitae::class)->find($id);

        $json = $helpers->serializador($curriculumVitaeData);
        return $json;
    }

    #[Route('/login', name:'user_login')]
    public function loginUser(ManagerRegistry $doctrine, Helpers $helpers, Request $request): Response{
        $parameters = json_decode($request->getContent(), true);

        $passHash = hash('sha256', $parameters['password']);

        $user = $doctrine->getRepository(User::class)->findOneBy([
            'typeIdentification' => $parameters['tipo'],
            'identification' => $parameters['numero'],
            'password' => $passHash
        ]);

        if($user === NULL){
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('El usuario y contraseña son incorrectos!!');
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }

        $response=new Response();
        $json = $helpers->serializador($user);
        //meter validación si se necesita evaluar el estado 200
        //Si es necesario, no enviar la contraseña en la respuesta
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

    #[Route('/login-jwt', name:'login-jwt')]
    public function loginJwt(Request $request, ManagerRegistry $doctrine): Response
    {
        $data = json_decode($request->request->get('json'), true);
        // $response = new Response();
        // $response->setContent(json_encode(['json'=>$array]));
        // return $response;
        $client = HttpClient::create();
        $responseIctus = $client->request('POST', 'https://ictus.unicatolicadelsur.edu.co/unicat/web/login',[
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query(['json' => json_encode($data)])
        ]);
        //if ($responseIctus->getStatusCode() === 200) {
            $content = $responseIctus->getContent();
            $response = new Response();
            $response->setContent($content);
            return $response;
            // do something with the response content
        //}
        //return new Response('f');
        // $passHash = hash('sha256', $parameters['password']);

        // $user = $doctrine->getRepository(User::class)->findOneBy([
        //     'type_identification' => $parameters[ 'tipoIdentificacion'],
        //     'identification' => $parameters['identification'],
        //     'password' => $passHash
        // ]);
        // if($user !== NULL){

        // }
        // works start
        // $jwtToken = $request->request->get('jwt_token');
        // $decodedToken = JWT::decode($jwtToken, new Key('Un1c4t0l1c4', 'HS256'));
        // $response = new Response();
        // $response->setContent(json_encode(['test' => $decodedToken]));
        // return $response;
        // works end
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
