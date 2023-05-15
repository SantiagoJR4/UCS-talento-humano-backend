<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Medicaltest
 *
 * @ORM\Table(name="medicaltest", indexes={@ORM\Index(name="fk_medicalTest_user", columns={"user_id"})})
 * @ORM\Entity
 */
class Medicaltest
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
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     */
    private $city;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="medicalCenter", type="string", length=255, nullable=false)
     */
    private $medicalcenter;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="typeTest", type="string", length=255, nullable=false)
     */
    private $typetest;

    /**
     * @var string
     *
     * @ORM\Column(name="ocupationalMedicalTest", type="string", length=255, nullable=false)
     */
    private $ocupationalmedicaltest;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=1, nullable=false, options={"fixed"=true,"comment"="0-asignada
1-asitio
2-no asistio
3-re-agendada"})
     */
    private $state;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getMedicalcenter(): ?string
    {
        return $this->medicalcenter;
    }

    public function setMedicalcenter(string $medicalcenter): self
    {
        $this->medicalcenter = $medicalcenter;

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

    public function getTypetest(): ?string
    {
        return $this->typetest;
    }

    public function setTypetest(string $typetest): self
    {
        $this->typetest = $typetest;

        return $this;
    }

    public function getOcupationalmedicaltest(): ?string
    {
        return $this->ocupationalmedicaltest;
    }

    public function setOcupationalmedicaltest(string $ocupationalmedicaltest): self
    {
        $this->ocupationalmedicaltest = $ocupationalmedicaltest;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


}
