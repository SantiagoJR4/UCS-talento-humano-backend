<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * WorkExperience
 *
 * @ORM\Table(name="work_experience", indexes={@ORM\Index(name="fk_workExp_user", columns={"user_id"})})
 * @ORM\Entity
 */
class WorkExperience
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
     * @ORM\Column(name="company_name", type="string", length=255, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="dependence", type="string", length=255, nullable=false)
     */
    private $dependence;

    /**
     * @var string
     *
     * @ORM\Column(name="department", type="string", length=255, nullable=false)
     */
    private $department;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality", type="string", length=5, nullable=false)
     */
    private $municipality;

    /**
     * @var string
     *
     * @ORM\Column(name="company_address", type="string", length=255, nullable=false)
     */
    private $companyAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="boss_name", type="string", length=255, nullable=false)
     */
    private $bossName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=false)
     */
    private $phone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="admission_date", type="date", nullable=false)
     */
    private $admissionDate;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_working", type="boolean", nullable=true)
     */
    private $isWorking;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="retirement_date", type="date", nullable=true)
     */
    private $retirementDate;

    /**
     * @var string
     *
     * @ORM\Column(name="time_worked", type="text", length=0, nullable=false)
     */
    private $timeWorked;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certified_pdf", type="text", length=65535, nullable=true)
     */
    private $certifiedPdf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="history", type="text", length=0, nullable=true)
     */
    private $history;

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

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDependence(): ?string
    {
        return $this->dependence;
    }

    public function setDependence(string $dependence): self
    {
        $this->dependence = $dependence;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getMunicipality(): ?string
    {
        return $this->municipality;
    }

    public function setMunicipality(string $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(string $companyAddress): self
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function getBossName(): ?string
    {
        return $this->bossName;
    }

    public function setBossName(string $bossName): self
    {
        $this->bossName = $bossName;

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

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admissionDate;
    }

    public function setAdmissionDate(\DateTimeInterface $admissionDate): self
    {
        $this->admissionDate = $admissionDate;

        return $this;
    }

    public function isIsWorking(): ?bool
    {
        return $this->isWorking;
    }

    public function setIsWorking(?bool $isWorking): self
    {
        $this->isWorking = $isWorking;

        return $this;
    }

    public function getRetirementDate(): ?\DateTimeInterface
    {
        return $this->retirementDate;
    }

    public function setRetirementDate(?\DateTimeInterface $retirementDate): self
    {
        $this->retirementDate = $retirementDate;

        return $this;
    }

    public function getTimeWorked(): ?string
    {
        return $this->timeWorked;
    }

    public function setTimeWorked(string $timeWorked): self
    {
        $this->timeWorked = $timeWorked;

        return $this;
    }

    public function getCertifiedPdf(): ?string
    {
        return $this->certifiedPdf;
    }

    public function setCertifiedPdf(?string $certifiedPdf): self
    {
        $this->certifiedPdf = $certifiedPdf;

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): self
    {
        $this->history = $history;

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
