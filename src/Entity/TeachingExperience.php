<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * TeachingExperience
 *
 * @ORM\Table(name="teaching_experience", indexes={@ORM\Index(name="fk_teachingExp_user", columns={"user_id"})})
 * @ORM\Entity
 */
class TeachingExperience
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
     * @var bool|null
     *
     * @ORM\Column(name="is_foreign_university", type="boolean", nullable=true)
     */
    private $isForeignUniversity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="snies", type="string", length=4, nullable=true, options={"fixed"=true})
     */
    private $snies;

    /**
     * @var string
     *
     * @ORM\Column(name="name_university", type="string", length=255, nullable=false)
     */
    private $nameUniversity;

    /**
     * @var string
     *
     * @ORM\Column(name="faculty", type="string", length=255, nullable=false)
     */
    private $faculty;

    /**
     * @var string
     *
     * @ORM\Column(name="program", type="string", length=255, nullable=false)
     */
    private $program;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_admission", type="date", nullable=false)
     */
    private $dateAdmission;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="retirement_date", type="date", nullable=true)
     */
    private $retirementDate;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_modality", type="string", length=2, nullable=false)
     */
    private $contractModality;

    /**
     * @var string
     *
     * @ORM\Column(name="course_load", type="text", length=0, nullable=false)
     */
    private $courseLoad;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certified_pdf", type="text", length=65535, nullable=true)
     */
    private $certifiedPdf;

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

    public function isIsForeignUniversity(): ?bool
    {
        return $this->isForeignUniversity;
    }

    public function setIsForeignUniversity(?bool $isForeignUniversity): self
    {
        $this->isForeignUniversity = $isForeignUniversity;

        return $this;
    }

    public function getSnies(): ?string
    {
        return $this->snies;
    }

    public function setSnies(?string $snies): self
    {
        $this->snies = $snies;

        return $this;
    }

    public function getNameUniversity(): ?string
    {
        return $this->nameUniversity;
    }

    public function setNameUniversity(string $nameUniversity): self
    {
        $this->nameUniversity = $nameUniversity;

        return $this;
    }

    public function getFaculty(): ?string
    {
        return $this->faculty;
    }

    public function setFaculty(string $faculty): self
    {
        $this->faculty = $faculty;

        return $this;
    }

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function setProgram(string $program): self
    {
        $this->program = $program;

        return $this;
    }

    public function getDateAdmission(): ?\DateTimeInterface
    {
        return $this->dateAdmission;
    }

    public function setDateAdmission(\DateTimeInterface $dateAdmission): self
    {
        $this->dateAdmission = $dateAdmission;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getContractModality(): ?string
    {
        return $this->contractModality;
    }

    public function setContractModality(string $contractModality): self
    {
        $this->contractModality = $contractModality;

        return $this;
    }

    public function getCourseLoad(): ?string
    {
        return $this->courseLoad;
    }

    public function setCourseLoad(string $courseLoad): self
    {
        $this->courseLoad = $courseLoad;

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
