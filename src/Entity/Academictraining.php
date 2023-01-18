<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Academictraining
 *
 * @ORM\Table(name="academictraining")
 * @ORM\Entity
 */
class Academictraining
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
     * @ORM\Column(name="academicModality", type="string", length=3, nullable=false, options={"fixed"=true})
     */
    private $academicmodality;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="titleName", type="string", length=255, nullable=false)
     */
    private $titlename;

    /**
     * @var int
     *
     * @ORM\Column(name="snies", type="integer", nullable=false)
     */
    private $snies;

    /**
     * @var bool
     *
     * @ORM\Column(name="isForeignUniversity", type="boolean", nullable=false)
     */
    private $isforeignuniversity;

    /**
     * @var string
     *
     * @ORM\Column(name="nameUniversity", type="string", length=255, nullable=false)
     */
    private $nameuniversity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAcademicmodality(): ?string
    {
        return $this->academicmodality;
    }

    public function setAcademicmodality(string $academicmodality): self
    {
        $this->academicmodality = $academicmodality;

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

    public function getTitlename(): ?string
    {
        return $this->titlename;
    }

    public function setTitlename(string $titlename): self
    {
        $this->titlename = $titlename;

        return $this;
    }

    public function getSnies(): ?int
    {
        return $this->snies;
    }

    public function setSnies(int $snies): self
    {
        $this->snies = $snies;

        return $this;
    }

    public function isIsforeignuniversity(): ?bool
    {
        return $this->isforeignuniversity;
    }

    public function setIsforeignuniversity(bool $isforeignuniversity): self
    {
        $this->isforeignuniversity = $isforeignuniversity;

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


}
