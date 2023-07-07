<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * PersonalData
 *
 * @ORM\Table(name="personal_data", uniqueConstraints={@ORM\UniqueConstraint(name="userid_curriculumVitae", columns={"user_id"})})
 * @ORM\Entity
 */
class PersonalData
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
     * @ORM\Column(name="residence_address", type="string", length=255, nullable=false)
     */
    private $residenceAddress;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_issue", type="date", nullable=false)
     */
    private $dateIssue;

    /**
     * @var string
     *
     * @ORM\Column(name="place_issue", type="string", length=255, nullable=false)
     */
    private $placeIssue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthdate", type="date", nullable=false)
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="birthplace", type="string", length=255, nullable=false)
     */
    private $birthplace;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=false, options={"fixed"=true})
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="blood_type", type="string", length=3, nullable=false)
     */
    private $bloodType;

    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="string", length=1, nullable=false, options={"fixed"=true})
     */
    private $maritalStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_photo", type="text", length=65535, nullable=true)
     */
    private $urlPhoto;

    /**
     * @var string
     *
     * @ORM\Column(name="eps", type="string", length=255, nullable=false)
     */
    private $eps;

    /**
     * @var string
     *
     * @ORM\Column(name="pension", type="string", length=255, nullable=false)
     */
    private $pension;

    /**
     * @var string
     *
     * @ORM\Column(name="identification_pdf", type="text", length=65535, nullable=false)
     */
    private $identificationPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="eps_pdf", type="text", length=65535, nullable=false)
     */
    private $epsPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="pension_pdf", type="text", length=65535, nullable=false)
     */
    private $pensionPdf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_cvlac", type="string", length=255, nullable=true)
     */
    private $urlCvlac;

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

    public function getResidenceAddress(): ?string
    {
        return $this->residenceAddress;
    }

    public function setResidenceAddress(string $residenceAddress): static
    {
        $this->residenceAddress = $residenceAddress;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getMunicipality(): ?string
    {
        return $this->municipality;
    }

    public function setMunicipality(string $municipality): static
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function getDateIssue(): ?\DateTimeInterface
    {
        return $this->dateIssue;
    }

    public function setDateIssue(\DateTimeInterface $dateIssue): static
    {
        $this->dateIssue = $dateIssue;

        return $this;
    }

    public function getPlaceIssue(): ?string
    {
        return $this->placeIssue;
    }

    public function setPlaceIssue(string $placeIssue): static
    {
        $this->placeIssue = $placeIssue;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(string $birthplace): static
    {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    public function setBloodType(string $bloodType): static
    {
        $this->bloodType = $bloodType;

        return $this;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(string $maritalStatus): static
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): static
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    public function getEps(): ?string
    {
        return $this->eps;
    }

    public function setEps(string $eps): static
    {
        $this->eps = $eps;

        return $this;
    }

    public function getPension(): ?string
    {
        return $this->pension;
    }

    public function setPension(string $pension): static
    {
        $this->pension = $pension;

        return $this;
    }

    public function getIdentificationPdf(): ?string
    {
        return $this->identificationPdf;
    }

    public function setIdentificationPdf(string $identificationPdf): static
    {
        $this->identificationPdf = $identificationPdf;

        return $this;
    }

    public function getEpsPdf(): ?string
    {
        return $this->epsPdf;
    }

    public function setEpsPdf(string $epsPdf): static
    {
        $this->epsPdf = $epsPdf;

        return $this;
    }

    public function getPensionPdf(): ?string
    {
        return $this->pensionPdf;
    }

    public function setPensionPdf(string $pensionPdf): static
    {
        $this->pensionPdf = $pensionPdf;

        return $this;
    }

    public function getUrlCvlac(): ?string
    {
        return $this->urlCvlac;
    }

    public function setUrlCvlac(?string $urlCvlac): static
    {
        $this->urlCvlac = $urlCvlac;

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): static
    {
        $this->history = $history;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }


}
