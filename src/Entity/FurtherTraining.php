<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * FurtherTraining
 *
 * @ORM\Table(name="further_training")
 * @ORM\Entity
 */
class FurtherTraining
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
     * @ORM\Column(name="complementaryModality", type="string", length=2, nullable=false)
     */
    private $complementarymodality;

    /**
     * @var string
     *
     * @ORM\Column(name="titleName", type="string", length=255, nullable=false)
     */
    private $titlename;

    /**
     * @var string
     *
     * @ORM\Column(name="institution", type="string", length=255, nullable=false)
     */
    private $institution;

    /**
     * @var int
     *
     * @ORM\Column(name="hours", type="integer", nullable=false)
     */
    private $hours;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certifiedPdf", type="string", length=255, nullable=true)
     */
    private $certifiedpdf;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComplementarymodality(): ?string
    {
        return $this->complementarymodality;
    }

    public function setComplementarymodality(string $complementarymodality): self
    {
        $this->complementarymodality = $complementarymodality;

        return $this;
    }

    public function getTitlename(): ?string
    {
        return $this->titlename;
    }

    public function setTitlename(string $titlename): self
    {
        $this->titlename = $titlename;

        return $this;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getHours(): ?int
    {
        return $this->hours;
    }

    public function setHours(int $hours): self
    {
        $this->hours = $hours;

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
