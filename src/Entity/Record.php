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
     * @ORM\Column(name="tax_record_pdf", type="text", length=65535, nullable=false)
     */
    private $taxRecordPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="judicial_record_pdf", type="text", length=65535, nullable=false)
     */
    private $judicialRecordPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="disciplinary_record_pdf", type="text", length=65535, nullable=false)
     */
    private $disciplinaryRecordPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="corrective_measures_pdf", type="text", length=65535, nullable=false)
     */
    private $correctiveMeasuresPdf;

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

    public function getTaxRecordPdf(): ?string
    {
        return $this->taxRecordPdf;
    }

    public function setTaxRecordPdf(string $taxRecordPdf): self
    {
        $this->taxRecordPdf = $taxRecordPdf;

        return $this;
    }

    public function getJudicialRecordPdf(): ?string
    {
        return $this->judicialRecordPdf;
    }

    public function setJudicialRecordPdf(string $judicialRecordPdf): self
    {
        $this->judicialRecordPdf = $judicialRecordPdf;

        return $this;
    }

    public function getDisciplinaryRecordPdf(): ?string
    {
        return $this->disciplinaryRecordPdf;
    }

    public function setDisciplinaryRecordPdf(string $disciplinaryRecordPdf): self
    {
        $this->disciplinaryRecordPdf = $disciplinaryRecordPdf;

        return $this;
    }

    public function getCorrectiveMeasuresPdf(): ?string
    {
        return $this->correctiveMeasuresPdf;
    }

    public function setCorrectiveMeasuresPdf(string $correctiveMeasuresPdf): self
    {
        $this->correctiveMeasuresPdf = $correctiveMeasuresPdf;

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): self
    {
        $this->history = $history;

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
