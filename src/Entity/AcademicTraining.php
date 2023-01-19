<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * AcademicTraining
 *
 * @ORM\Table(name="academic_training")
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
     * @var bool
     *
     * @ORM\Column(name="certified_title", type="boolean", nullable=false)
     */
    private $certifiedTitle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certified_title_pdf", type="text", length=0, nullable=true)
     */
    private $certifiedTitlePdf;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAcademicModality(): ?string
    {
        return $this->academicModality;
    }

    public function setAcademicModality(string $academicModality): self
    {
        $this->academicModality = $academicModality;

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

    public function getTitleName(): ?string
    {
        return $this->titleName;
    }

    public function setTitleName(string $titleName): self
    {
        $this->titleName = $titleName;

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

    public function isIsForeignUniversity(): ?bool
    {
        return $this->isForeignUniversity;
    }

    public function setIsForeignUniversity(bool $isForeignUniversity): self
    {
        $this->isForeignUniversity = $isForeignUniversity;

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

    public function getDegreePdf(): ?string
    {
        return $this->degreePdf;
    }

    public function setDegreePdf(?string $degreePdf): self
    {
        $this->degreePdf = $degreePdf;

        return $this;
    }

    public function isCertifiedTitle(): ?bool
    {
        return $this->certifiedTitle;
    }

    public function setCertifiedTitle(bool $certifiedTitle): self
    {
        $this->certifiedTitle = $certifiedTitle;

        return $this;
    }

    public function getCertifiedTitlePdf(): ?string
    {
        return $this->certifiedTitlePdf;
    }

    public function setCertifiedTitlePdf(?string $certifiedTitlePdf): self
    {
        $this->certifiedTitlePdf = $certifiedTitlePdf;

        return $this;
    }


}
