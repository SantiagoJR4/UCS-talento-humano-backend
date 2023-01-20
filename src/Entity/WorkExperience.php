<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * WorkExperience
 *
 * @ORM\Table(name="work_experience")
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
     * @ORM\Column(name="companyName", type="string", length=255, nullable=false)
     */
    private $companyname;

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
     * @ORM\Column(name="companyAddress", type="string", length=255, nullable=false)
     */
    private $companyaddress;

    /**
     * @var string
     *
     * @ORM\Column(name="bossName", type="string", length=255, nullable=false)
     */
    private $bossname;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=false)
     */
    private $phone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="admissionDate", type="date", nullable=false)
     */
    private $admissiondate;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="isWorking", type="boolean", nullable=true)
     */
    private $isworking;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="retirementDate", type="date", nullable=true)
     */
    private $retirementdate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certifiedPdf", type="text", length=65535, nullable=true)
     */
    private $certifiedpdf;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyname(): ?string
    {
        return $this->companyname;
    }

    public function setCompanyname(string $companyname): self
    {
        $this->companyname = $companyname;

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

    public function getCompanyaddress(): ?string
    {
        return $this->companyaddress;
    }

    public function setCompanyaddress(string $companyaddress): self
    {
        $this->companyaddress = $companyaddress;

        return $this;
    }

    public function getBossname(): ?string
    {
        return $this->bossname;
    }

    public function setBossname(string $bossname): self
    {
        $this->bossname = $bossname;

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

    public function getAdmissiondate(): ?\DateTimeInterface
    {
        return $this->admissiondate;
    }

    public function setAdmissiondate(\DateTimeInterface $admissiondate): self
    {
        $this->admissiondate = $admissiondate;

        return $this;
    }

    public function isIsworking(): ?bool
    {
        return $this->isworking;
    }

    public function setIsworking(?bool $isworking): self
    {
        $this->isworking = $isworking;

        return $this;
    }

    public function getRetirementdate(): ?\DateTimeInterface
    {
        return $this->retirementdate;
    }

    public function setRetirementdate(?\DateTimeInterface $retirementdate): self
    {
        $this->retirementdate = $retirementdate;

        return $this;
    }

    public function getCertifiedpdf(): ?string
    {
        return $this->certifiedpdf;
    }

    public function setCertifiedpdf(?string $certifiedpdf): self
    {
        $this->certifiedpdf = $certifiedpdf;

        return $this;
    }


}
