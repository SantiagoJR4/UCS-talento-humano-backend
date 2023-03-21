<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="sub", columns={"sub"})})
 * @ORM\Entity
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="names", type="string", length=255, nullable=false)
     */
    private $names;

    /**
     * @var string
     *
     * @ORM\Column(name="last_names", type="string", length=255, nullable=false)
     */
    private $lastNames;

    /**
     * @var string
     *
     * @ORM\Column(name="type_identification", type="string", length=2, nullable=false, options={"fixed"=true})
     */
    private $typeIdentification;

    /**
     * @var string
     *
     * @ORM\Column(name="identification", type="string", length=12, nullable=false)
     */
    private $identification;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="alternate_email", type="string", length=255, nullable=true)
     */
    private $alternateEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=false)
     */
    private $phone;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="email_status", type="boolean", nullable=true)
     */
    private $emailStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_photo", type="text", length=65535, nullable=true)
     */
    private $urlPhoto;

    /**
     * @var int
     *
     * @ORM\Column(name="user_type", type="smallint", nullable=false)
     */
    private $userType;

    /**
     * @var int
     *
     * @ORM\Column(name="sub", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $sub;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNames(): ?string
    {
        return $this->names;
    }

    public function setNames(string $names): self
    {
        $this->names = $names;

        return $this;
    }

    public function getLastNames(): ?string
    {
        return $this->lastNames;
    }

    public function setLastNames(string $lastNames): self
    {
        $this->lastNames = $lastNames;

        return $this;
    }

    public function getTypeIdentification(): ?string
    {
        return $this->typeIdentification;
    }

    public function setTypeIdentification(string $typeIdentification): self
    {
        $this->typeIdentification = $typeIdentification;

        return $this;
    }

    public function getIdentification(): ?string
    {
        return $this->identification;
    }

    public function setIdentification(string $identification): self
    {
        $this->identification = $identification;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAlternateEmail(): ?string
    {
        return $this->alternateEmail;
    }

    public function setAlternateEmail(?string $alternateEmail): self
    {
        $this->alternateEmail = $alternateEmail;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function isEmailStatus(): ?bool
    {
        return $this->emailStatus;
    }

    public function setEmailStatus(?bool $emailStatus): self
    {
        $this->emailStatus = $emailStatus;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): self
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    public function getUserType(): ?int
    {
        return $this->userType;
    }

    public function setUserType(int $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getSub(): ?int
    {
        return $this->sub;
    }

    public function setSub(int $sub): self
    {
        $this->sub = $sub;

        return $this;
    }


}
