<?php

namespace App\Controller;

use App\Entity\CurriculumVitae;
use App\Entity\RecoveryEmail;
use App\Service\Helpers;
use App\Service\UserService;
use App\Entity\User;
use App\Service\ValidateToken;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

function createJwtResponse($user, $isUserInOpenCall) {
    $jwtKey = $_ENV['JWT_SECRET'];
    $resp = [
        'names' => $user->getNames(),
        'lastNames' => $user->getLastNames(),
        'phone' => $user->getPhone(),
        'email' => $user->getEmail(),
        'alternate_email' => $user->getAlternateEmail(),
        'identification' => $user->getIdentification(),
        'typeIdentification' => $user->getTypeIdentification(),
        'userPhoto' => $user->getUrlPhoto()
    ];
    $payload = [
        // 'sub' => $user->getSub(),
        'userID' => $user->getId(),
        'userType' => $user->getUserType(),
        'specialUser' => $user->getSpecialUser(),
        'isUserInOpenCall' => $isUserInOpenCall,
        'iat' => time(),
        'exp' => time() + 604800
    ];
    $token = JWT::encode($payload, $jwtKey, 'HS256');
    return new JsonResponse(['token'=>$token, 'user'=>$resp]);
}

function createAccountRecoveryLink($userID) {
    $jwtKey = $_ENV['JWT_SECRET'];
    $payload = [
        'id' =>  $userID,
        'iat' => time(),
        'exp' => time() + 86400
    ];
    $token = JWT::encode($payload, $jwtKey, 'HS256');
    //TODO: change to production
    return($token);
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
            'phone' => $dataUser->getPhone(),
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
    
    #[Route('/updateUser',name:'app_update_user')]
    public function update(ManagerRegistry $doctrine, Request $request, validateToken $vToken) : JsonResponse
    {
        $token = $request->query->get('token');
        $userToken = $vToken->getUserIdFromToken($token);
        $data = json_decode($request->getContent(),true);
        $userId = $data['userId'];
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('El usuario no fue encontrado.');
        }

        // Actualizar los datos del usuario según los parámetros recibidos
        $user->setNames($data['names']);
        $user->setLastNames($data['lastNames']);
        $user->setTypeIdentification($data['type_identification']);
        $user->setIdentification($data['identification']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status'=>'Success','code'=>'200','message'=>'Actualización de datos Correctamente']);
    }
    
    #[Route('/register', name:'app_user_register')]
    public function registerUser(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = $request->request->all();
        
        $data_db = $doctrine->getRepository(User::class)->findOneBy([
            'identification' => $data['identification']
        ]);
        if($data_db !== NULL){
            return new JsonResponse(['message' => 'Está identificación no está disponible.'], 404);
        }
        try {
            $newUser = new User();
            $newUser->setNames($data['names']);
            $newUser->setLastNames($data['lastNames']);
            $newUser->setTypeIdentification($data['typeIdentification']);
            $newUser->setIdentification($data['identification']);
            $newUser->setEmail($data['email']);
            $newUser->setPhone($data['phone']);
            $newUser->setPassword(hash('sha256',$data['password']));
            $newUser->setHistory('[]');
            $newUser->setUserType(6);
            
            $entityManager=$doctrine->getManager();
            $entityManager->persist($newUser);
            $entityManager->flush();
            
            return new JsonResponse(['message' => 'Usuario registrado exitosamente']);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Ha ocurrido un error, por favor intente de nuevo, si el error persiste por favor contactenos.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    #[Route('/login', name: 'app_login')]
    public function loginJwt(Request $request, ManagerRegistry $doctrine, UserService $userService): JsonResponse
    {
        $jwtKey = $_ENV['JWT_SECRET'];
        $data =  $request->request->all();
        // $data = json_decode($request->request->get('json'), true);
        $passHash = hash('sha256', $data['password']);
        try {
            $user = $doctrine->getRepository(User::class)->findOneBy([
                'typeIdentification' => $data['IDType'],
                'identification' => $data['number'],
                'password' => $passHash
            ]);
            if ($user === NULL) {
                return new JsonResponse(['message' => 'Usuario o contraseña invalidos'], 401);
            }
            $callOpenState = 4;
            $queryBuilder = $doctrine->getManager()->createQueryBuilder();
            $query = $queryBuilder
                ->select('c.id')
                ->from('App\Entity\UsersInCall', 'uc')
                ->join('uc.call', 'c')
                ->where($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('c.state',':callOpenState'),
                    $queryBuilder->expr()->eq('uc.user',':user'),
                ))
                ->setParameter('user', $user)
                ->setParameter('callOpenState', $callOpenState);
            $array = $query->getQuery()->getArrayResult();
            $isUserInOpenCall = !empty($array) ? true : false;
            return createJwtResponse($user, $isUserInOpenCall);
        } catch (ORMException $e) {
            return new JsonResponse(['message' => 'Ha ocurrido un error durante el login'], 500);
        }
        catch (\Throwable $th) {
            return new JsonResponse(['
            message' => 'Ha ocurrido un error, por favor intente de nuevo, si el error persiste por favor contactenos.',
            'data' => $th
        ], 404);
        }
    }

    #[Route('/validate-token', name: 'app_validate_token')]
    public function validateToken(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $jwtKey = $_ENV['JWT_SECRET'];
        $token = $request->query->get('token');
        try {
            $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return new JsonResponse(false);
        }
        $expirationTime = $decodedToken->exp;
        $isTokenValid = (new DateTime())->getTimestamp() < $expirationTime;
        $userID = $decodedToken->userID;
        $callOpenState = 4;
        $queryBuilder = $doctrine->getManager()->createQueryBuilder();
        $user = $doctrine->getRepository(User::class)->find($userID);
        $queryCall = $queryBuilder
            ->select('c.id, uc.status')
            ->from('App\Entity\UsersInCall', 'uc')
            ->join('uc.call', 'c')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('c.state',':callOpenState'),
                $queryBuilder->expr()->eq('uc.user',':user'),
            ))
            ->setParameter('user', $user)
            ->setParameter('callOpenState', $callOpenState);
        $array = $queryCall->getQuery()->getArrayResult();
        foreach ($array as &$item) {
            if (isset($item['status'])) {
                $item['status'] = json_decode($item['status'], true);
                if($item['status']['CVSTATUS'] === 4){
                    return new JsonResponse([
                        'isValid' => $isTokenValid,
                        'userType' => $decodedToken->userType,
                        'specialUser' => $decodedToken->specialUser,
                        'isUserInOpenCall' => false
                    ]);
                }
            }
        }
        return new JsonResponse([
            'isValid' => $isTokenValid,
            'userType' => $decodedToken->userType,
            'specialUser' => $decodedToken->specialUser,
            'isUserInOpenCall' => $decodedToken->isUserInOpenCall
        ]);
    }

    #[Route('/email-to-recover', name: 'app_email_to_recover')]
    public function emailToRecover(ManagerRegistry $doctrine, Request $request, MailerInterface $mailer): JsonResponse
    {
        $userIDType = $request->query->get('IDType');
        $userNumber = $request->query->get('number');
        $user = $doctrine->getRepository(User::class)->findOneBy([
            'typeIdentification' => $userIDType,
            'identification' => $userNumber,
        ]);
        if( $user )
        {
            try
            {
                $tokenForRecoverAccount = createAccountRecoveryLink($user->getId());
                //var_dump($user);
                $newRecoveryEmail = new RecoveryEmail();
                $newRecoveryEmail->setToken($tokenForRecoverAccount);
                $newRecoveryEmail->setUsed(false);
                $newRecoveryEmail->setUser($user);
                //TODO: change to production
                $linkForRecover = 'http://yeshua.unicatolicadelsur.edu.co:4200/ucs-talento-humano/#/auth/recover-account/' . $tokenForRecoverAccount; 
                $email = (new TemplatedEmail())
                    ->from('convocatorias@unicatolicadelsur.edu.co') //correo oficina oasic
                    ->to($user->getEmail()) //correo talento humano
                    ->subject('Recuperación de cuenta')
                    ->htmlTemplate('email/recoverAccountEmail.html.twig')
                    ->context([
                        'fullname' => $user->getNames().' '.$user->getLastNames(),
                        'emailToRecover' => $linkForRecover,
                    ]);
                $mailer->send($email);
                $entityManager = $doctrine->getManager();
                $entityManager->persist($newRecoveryEmail);
                $entityManager->flush();
            } catch (\Throwable $th)
            {
                $message = 'Error al enviar el correo:'.$th->getMessage();
                return new JsonResponse($th->getMessage(), 404, []);
            }
            return new JsonResponse('Se han enviado instrucciones al correo registrado con esta cuenta.', 200, []);
        }
        else
        {
            return new JsonResponse('La información proporcionada no coincide con ninguna cuenta registrada.', 404, []);
        }
    }

    #[Route('/validate-for-new-password', name: 'app_validate_for_new_password')]
    public function validateForNewPassword(ManagerRegistry $doctrine, Request $request, MailerInterface $mailer): JsonResponse
    {
        $jwtKey = $_ENV['JWT_SECRET'];
        $token = $request->query->get('token');
        try {
            $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return new JsonResponse(false, 404,[]);
        }
        $recoveryEmail = $doctrine->getRepository(RecoveryEmail::class)->findOneBy(['token' => $token]);
        $isUsed = $recoveryEmail->isUsed();
        return new JsonResponse($isUsed ? false : true, 200, []);
    }

    #[Route('/change-password', name: 'app_change-password')]
    public function changePassword(ManagerRegistry $doctrine, Request $request, MailerInterface $mailer): JsonResponse
    {
        $jwtKey = $_ENV['JWT_SECRET'];
        $token = $request->query->get('token');
        $password = $request->query->get('password');
        $passHash = hash('sha256', $password);
        try {
            $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
            $userID = $decodedToken->id;
            $user = $doctrine->getRepository(User::class)->find($userID);
            $user->setPassword($passHash);
            $recoveryEmail = $doctrine->getRepository(RecoveryEmail::class)->findOneBy(['token' => $token]);
            $recoveryEmail->setUsed(true);
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
        } catch (\Throwable $th) {
            return new JsonResponse('Error al cambiar la contraseña, por favor repita el proceso, en caso de persistir comuniquese con nosotros', 404, []);
        }
        return new JsonResponse('Cambio de contraseña realizado!', 200, []);
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
