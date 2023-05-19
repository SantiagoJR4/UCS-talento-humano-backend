<?php

namespace App\Controller;

use App\Entity\CurriculumVitae;
use App\Service\Helpers;
use App\Service\UserService;
use App\Entity\User;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

function createJwtResponse($user) {
    $jwtKey = 'Un1c4t0l1c4'; //TODO: move this to .env
    $resp = [
        'names' => $user->getNames(),
        'lastNames' => $user->getLastNames(),
        // 'phone' => $user->getPhone(),
        'email' => $user->getEmail(),
        'identification' => $user->getIdentification(),
        'typeIdentification' => $user->getTypeIdentification()
    ];
    $payload = [
        'sub' => $user->getSub(),
        'userType' => $user->getUserType(),
        'iat' => time(),
        'exp' => time() + 604800
    ];
    $token = JWT::encode($payload, $jwtKey, 'HS256');
    return new JsonResponse(['token'=>$token, 'user'=>$resp]);
}


class UserController extends AbstractController
{
    #[Route('/listUserData/{id}', name: 'app_list_user')]
    public function user(ManagerRegistry $doctrine, Helpers $helpers, int $id, Request $request): Response 
    {
        $token = $request->query->get('token');
        $dataUser = $doctrine->getRepository(User::class)->find($id);

        $data[] = [
            'id' => $dataUser->getId(),
            'names' => $dataUser->getNames(),
            'lastNames' => $dataUser->getLastNames(),
            'type_identification' => $dataUser->getTypeIdentification(),
            'identification' => $dataUser->getIdentification(),
            'email' => $dataUser->getEmail(),
            'phone' => $dataUser->getPhone()
        ];

        $json = $helpers->serializador($data);
        return $json;
    }
    #[Route('/listUsers', name: 'app_user')]
    public function userAll(ManagerRegistry $doctrine, Helpers $helpers, Request $request ,ValidateToken $vToken ): Response 
    {
        $token = $request->query->get('token');
        $user = $vToken->getUserIdFromToken($token);
        $users = $doctrine->getRepository(User::class)->findAll();

        foreach($users as $user){
            $userData[]=[
                'id' => $user->getId(),
                'names' => $user->getNames(),
                'last_names' => $user->getLastNames(),
                'type_identification' => $user->getTypeIdentification(),
                'identification' => $user->getIdentification(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'url_photo' => $user->getUrlPhoto()

            ];
        }

        $json = $helpers->serializador($userData);
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

    #[Route('/login', name: 'login')]
    public function loginJwt(Request $request, ManagerRegistry $doctrine, UserService $userService): JsonResponse
    {
        $jwtKey = 'Un1c4t0l1c4'; //TODO: move this to .env
        $data = json_decode($request->request->get('json'), true);
        $passHash = hash('sha256', $data['password']);
        $user = $doctrine->getRepository(User::class)->findOneBy([
            'typeIdentification' => $data['tipoIdentificacion'],
            'identification' => $data['numero'],
            'password' => $passHash
        ]);

        if ($user !== NULL) {
            return createJwtResponse($user);
        }
        $client = HttpClient::create();
        $responseIctus = $client->request('POST', 'https://ictus.unicatolicadelsur.edu.co/unicat/web/login', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query(['json' => json_encode($data)])
        ]);
        if ($responseIctus->getStatusCode() === 200) {
            $content = $responseIctus->getContent();
            $verifyError = json_decode($content, true);
            if (isset($verifyError['status']) && $verifyError['status'] === 'error') {
                return new JsonResponse(['status' => $verifyError['status'], 'data' => $verifyError['data']]);
            }
            $decodedToken = JWT::decode(trim($content, '"'), new Key($jwtKey, 'HS256'));
            $json = json_encode($decodedToken);
            $array = json_decode($json, true);
            $registerUser = $userService->createUser($array);
            return createJwtResponse($registerUser);
        }
    }

    #[Route('/validate-token', name: 'validate-token')]
    public function validateToken(Request $request): JsonResponse
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
        return new JsonResponse(['isValid' => $isTokenValid, 'userType' => $decodedToken->userType]);
    }

    #[Route('/saludo', name: 'saludo')]
    public function saludo(Request $request): JsonResponse
    {
        return new JsonResponse(['Hola que dice']);
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
