<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Record
 *
 * @ORM\Table(name="record", indexes={@ORM\Index(name="fk_record_user", columns={"user_id"})})
 * @ORM\Entity
 */
class Record
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
     * @ORM\Column(name="taxRecord_pdf", type="text", length=65535, nullable=false)
     */
    private $taxrecordPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="judicialRecord_pdf", type="text", length=65535, nullable=false)
     */
    private $judicialrecordPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="disciplinaryRecord_pdf", type="text", length=65535, nullable=false)
     */
    private $disciplinaryrecordPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="correctiveMeasures_pdf", type="text", length=65535, nullable=false)
     */
    private $correctivemeasuresPdf;

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

    public function getTaxrecordPdf(): ?string
    {
        return $this->taxrecordPdf;
    }

    public function setTaxrecordPdf(string $taxrecordPdf): self
    {
        $this->taxrecordPdf = $taxrecordPdf;

        return $this;
    }

    public function getJudicialrecordPdf(): ?string
    {
        return $this->judicialrecordPdf;
    }

    public function setJudicialrecordPdf(string $judicialrecordPdf): self
    {
        $this->judicialrecordPdf = $judicialrecordPdf;

        return $this;
    }

    public function getDisciplinaryrecordPdf(): ?string
    {
        return $this->disciplinaryrecordPdf;
    }

    public function setDisciplinaryrecordPdf(string $disciplinaryrecordPdf): self
    {
        $this->disciplinaryrecordPdf = $disciplinaryrecordPdf;

        return $this;
    }

    public function getCorrectivemeasuresPdf(): ?string
    {
        return $this->correctivemeasuresPdf;
    }

    public function setCorrectivemeasuresPdf(string $correctivemeasuresPdf): self
    {
        $this->correctivemeasuresPdf = $correctivemeasuresPdf;

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
