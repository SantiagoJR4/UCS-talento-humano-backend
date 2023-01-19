<?php

namespace App\Entity;

use App\Repository\AcademicTrainingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AcademicTrainingRepository::class)]
class AcademicTraining
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $academicModality = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $titleName = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $snies = null;

    #[ORM\Column]
    private ?bool $isForeignUniversity = null;

    #[ORM\Column(length: 255)]
    private ?string $nameUniversity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $degreePdf = null;

    #[ORM\Column]
    private ?bool $certifiedTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $certifiedTitlePdf = null;

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
