<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="nombres", type="string", length=255, nullable=false)
     */
    private $nombres;

    /**
     * @var string
     *
     * @ORM\Column(name="apellidos", type="string", length=255, nullable=false)
     */
    private $apellidos;

    /**
     * @var string
     *
     * @ORM\Column(name="correo", type="string", length=255, nullable=false)
     */
    private $correo;

    /**
     * @var string
     *
     * @ORM\Column(name="correoAlterno", type="string", length=255, nullable=false)
     */
    private $correoalterno;

    /**
     * @var string
     *
     * @ORM\Column(name="celular", type="string", length=20, nullable=false)
     */
    private $celular;

    /**
     * @var bool
     *
     * @ORM\Column(name="estadoCorreo", type="boolean", nullable=false)
     */
    private $estadocorreo;

    /**
     * @var string
     *
     * @ORM\Column(name="contraseña", type="string", length=255, nullable=false)
     */
    private $contraseña;

    /**
     * @var string
     *
     * @ORM\Column(name="urlFoto", type="text", length=65535, nullable=false)
     */
    private $urlfoto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getNombres(): ?string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): self
    {
        $this->nombres = $nombres;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getCorreoalterno(): ?string
    {
        return $this->correoalterno;
    }

    public function setCorreoalterno(string $correoalterno): self
    {
        $this->correoalterno = $correoalterno;

        return $this;
    }

    public function getCelular(): ?string
    {
        return $this->celular;
    }

    public function setCelular(string $celular): self
    {
        $this->celular = $celular;

        return $this;
    }

    public function isEstadocorreo(): ?bool
    {
        return $this->estadocorreo;
    }

    public function setEstadocorreo(bool $estadocorreo): self
    {
        $this->estadocorreo = $estadocorreo;

        return $this;
    }

    public function getContraseña(): ?string
    {
        return $this->contraseña;
    }

    public function setContraseña(string $contraseña): self
    {
        $this->contraseña = $contraseña;

        return $this;
    }

    public function getUrlfoto(): ?string
    {
        return $this->urlfoto;
    }

    public function setUrlfoto(string $urlfoto): self
    {
        $this->urlfoto = $urlfoto;

        return $this;
    }


}
