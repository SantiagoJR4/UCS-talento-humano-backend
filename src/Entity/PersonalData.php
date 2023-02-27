<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * PersonalData
 *
 * @ORM\Table(name="personal_data", uniqueConstraints={@ORM\UniqueConstraint(name="sub", columns={"user_id"})})
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
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResidenceAddress(): ?string
    {
        return $this->residenceAddress;
    }

    public function setResidenceAddress(string $residenceAddress): self
    {
        $this->residenceAddress = $residenceAddress;

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

    public function getDateIssue(): ?\DateTimeInterface
    {
        return $this->dateIssue;
    }

    public function setDateIssue(\DateTimeInterface $dateIssue): self
    {
        $this->dateIssue = $dateIssue;

        return $this;
    }

    public function getPlaceIssue(): ?string
    {
        return $this->placeIssue;
    }

    public function setPlaceIssue(string $placeIssue): self
    {
        $this->placeIssue = $placeIssue;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(string $birthplace): self
    {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    public function setBloodType(string $bloodType): self
    {
        $this->bloodType = $bloodType;

        return $this;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(string $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

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

    public function getEps(): ?string
    {
        return $this->eps;
    }

    public function setEps(string $eps): self
    {
        $this->eps = $eps;

        return $this;
    }

    public function getPension(): ?string
    {
        return $this->pension;
    }

    public function setPension(string $pension): self
    {
        $this->pension = $pension;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }


}
