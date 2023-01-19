<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * TeachingExperience
 *
 * @ORM\Table(name="teaching_experience")
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
     * @ORM\Column(name="isForeignUniversity", type="boolean", nullable=true)
     */
    private $isforeignuniversity;

    /**
     * @var string
     *
     * @ORM\Column(name="snies", type="string", length=4, nullable=false, options={"fixed"=true})
     */
    private $snies;

    /**
     * @var string
     *
     * @ORM\Column(name="nameUniversity", type="string", length=255, nullable=false)
     */
    private $nameuniversity;

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
     * @ORM\Column(name="dateAdmission", type="date", nullable=false)
     */
    private $dateadmission;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="isActive", type="boolean", nullable=true)
     */
    private $isactive;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="retirementDate", type="date", nullable=true)
     */
    private $retirementdate;

    /**
     * @var string
     *
     * @ORM\Column(name="contractModality", type="string", length=2, nullable=false, options={"fixed"=true})
     */
    private $contractmodality;

    /**
     * @var string
     *
     * @ORM\Column(name="courseLoad", type="text", length=0, nullable=false)
     */
    private $courseload;

    /**
     * @var string
     *
     * @ORM\Column(name="certifiedPdf", type="text", length=65535, nullable=false)
     */
    private $certifiedpdf;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsforeignuniversity(): ?bool
    {
        return $this->isforeignuniversity;
    }

    public function setIsforeignuniversity(?bool $isforeignuniversity): self
    {
        $this->isforeignuniversity = $isforeignuniversity;

        return $this;
    }

    public function getSnies(): ?string
    {
        return $this->snies;
    }

    public function setSnies(string $snies): self
    {
        $this->snies = $snies;

        return $this;
    }

    public function getNameuniversity(): ?string
    {
        return $this->nameuniversity;
    }

    public function setNameuniversity(string $nameuniversity): self
    {
        $this->nameuniversity = $nameuniversity;

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

    public function getDateadmission(): ?\DateTimeInterface
    {
        return $this->dateadmission;
    }

    public function setDateadmission(\DateTimeInterface $dateadmission): self
    {
        $this->dateadmission = $dateadmission;

        return $this;
    }

    public function isIsactive(): ?bool
    {
        return $this->isactive;
    }

    public function setIsactive(?bool $isactive): self
    {
        $this->isactive = $isactive;

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

    public function getContractmodality(): ?string
    {
        return $this->contractmodality;
    }

    public function setContractmodality(string $contractmodality): self
    {
        $this->contractmodality = $contractmodality;

        return $this;
    }

    public function getCourseload(): ?string
    {
        return $this->courseload;
    }

    public function setCourseload(string $courseload): self
    {
        $this->courseload = $courseload;

        return $this;
    }

    public function getCertifiedpdf(): ?string
    {
        return $this->certifiedpdf;
    }

    public function setCertifiedpdf(string $certifiedpdf): self
    {
        $this->certifiedpdf = $certifiedpdf;

        return $this;
    }


}
