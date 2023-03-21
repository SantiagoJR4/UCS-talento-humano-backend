<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ValidateToken
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getUserIdFromToken(string $token): User
    {
        $jwtTime = new \Firebase\JWT\JWT;
        $jwtTime::$leeway=5; //TODO: what's this???
        $jwtKey = 'Un1c4t0l1c4';
        $decodedToken = JWT::decode(trim($token, '"'), new Key($jwtKey, 'HS256'));
        $sub= $decodedToken->sub;
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['sub' => $sub]);
        return $user;
    }
};