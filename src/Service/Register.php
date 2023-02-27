<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createUser(array $data): User
    {
        $user = new User();
        $user->setSub($data['sub']);
        $user->setTypeIdentification($data['tipo']);
        $user->setIdentification($data['numero']);
        $user->setNames($data['nombres']);
        $user->setLastNames($data['apellidos']);
        $user->setEmail(($data['correo'] !== NULL) ? $data['correo'] : 'No email' );
        $user->setPassword($data['password']);
        $user->setPhone('NoTelefono');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
