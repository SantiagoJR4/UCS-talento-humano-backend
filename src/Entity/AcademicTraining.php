<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * AcademicTraining
 *
 * @ORM\Table(name="academic_training", indexes={@ORM\Index(name="fk_academicTraining_user", columns={"user_id"})})
 * @ORM\Entity
 */
class AcademicTraining
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
     * @ORM\Column(name="academic_modality", type="string", length=3, nullable=false)
     */
    private $academicModality;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="program_methodology", type="string", length=3, nullable=false, options={"fixed"=true})
     */
    private $programMethodology;

    /**
     * @var string
     *
     * @ORM\Column(name="title_name", type="string", length=255, nullable=false)
     */
    private $titleName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="snies", type="string", length=4, nullable=true)
     */
    private $snies;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_foreign_university", type="boolean", nullable=false)
     */
    private $isForeignUniversity;

    /**
     * @var string
     *
     * @ORM\Column(name="name_university", type="string", length=255, nullable=false)
     */
    private $nameUniversity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="degree_pdf", type="text", length=0, nullable=true)
     */
    private $degreePdf;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma_pdf", type="text", length=0, nullable=false)
     */
    private $diplomaPdf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certified_title_pdf", type="text", length=0, nullable=true)
     */
    private $certifiedTitlePdf;

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

    public function getAcademicModality(): ?string
    {
        return $this->academicModality;
    }

    public function setAcademicModality(string $academicModality): static
    {
        $this->academicModality = $academicModality;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getProgramMethodology(): ?string
    {
        return $this->programMethodology;
    }

    public function setProgramMethodology(string $programMethodology): static
    {
        $this->programMethodology = $programMethodology;

        return $this;
    }

    public function getTitleName(): ?string
    {
        return $this->titleName;
    }

    public function setTitleName(string $titleName): static
    {
        $this->titleName = $titleName;

        return $this;
    }

    public function getSnies(): ?string
    {
        return $this->snies;
    }

    public function setSnies(?string $snies): static
    {
        $this->snies = $snies;

        return $this;
    }

    public function isIsForeignUniversity(): ?bool
    {
        return $this->isForeignUniversity;
    }

    public function setIsForeignUniversity(bool $isForeignUniversity): static
    {
        $this->isForeignUniversity = $isForeignUniversity;

        return $this;
    }

    public function getNameUniversity(): ?string
    {
        return $this->nameUniversity;
    }

    public function setNameUniversity(string $nameUniversity): static
    {
        $this->nameUniversity = $nameUniversity;

        return $this;
    }

    public function getDegreePdf(): ?string
    {
        return $this->degreePdf;
    }

    public function setDegreePdf(?string $degreePdf): static
    {
        $this->degreePdf = $degreePdf;

        return $this;
    }

    public function getDiplomaPdf(): ?string
    {
        return $this->diplomaPdf;
    }

    public function setDiplomaPdf(string $diplomaPdf): static
    {
        $this->diplomaPdf = $diplomaPdf;

        return $this;
    }

    public function getCertifiedTitlePdf(): ?string
    {
        return $this->certifiedTitlePdf;
    }

    public function setCertifiedTitlePdf(?string $certifiedTitlePdf): static
    {
        $this->certifiedTitlePdf = $certifiedTitlePdf;

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
