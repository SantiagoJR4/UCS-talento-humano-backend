<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Incapacity
 *
 * @ORM\Table(name="incapacity", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Incapacity
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
     * @var \DateTime
     *
     * @ORM\Column(name="solicitude_date", type="date", nullable=false)
     */
    private $solicitudeDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="incapacity_date", type="date", nullable=false)
     */
    private $incapacityDate;

    /**
     * @var int
     *
     * @ORM\Column(name="number_days_incapacity", type="integer", nullable=false)
     */
    private $numberDaysIncapacity;

    /**
     * @var string
     *
     * @ORM\Column(name="origin_incapacity", type="string", length=2, nullable=false, options={"fixed"=true})
     */
    private $originIncapacity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="medical_support_pdf", type="text", length=0, nullable=true)
     */
    private $medicalSupportPdf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="eps_support_pdf", type="text", length=0, nullable=true)
     */
    private $epsSupportPdf;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false)
     */
    private $state;

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

    public function getSolicitudeDate(): ?\DateTimeInterface
    {
        return $this->solicitudeDate;
    }

    public function setSolicitudeDate(\DateTimeInterface $solicitudeDate): self
    {
        $this->solicitudeDate = $solicitudeDate;

        return $this;
    }

    public function getIncapacityDate(): ?\DateTimeInterface
    {
        return $this->incapacityDate;
    }

    public function setIncapacityDate(\DateTimeInterface $incapacityDate): self
    {
        $this->incapacityDate = $incapacityDate;

        return $this;
    }

    public function getNumberDaysIncapacity(): ?int
    {
        return $this->numberDaysIncapacity;
    }

    public function setNumberDaysIncapacity(int $numberDaysIncapacity): self
    {
        $this->numberDaysIncapacity = $numberDaysIncapacity;

        return $this;
    }

    public function getOriginIncapacity(): ?string
    {
        return $this->originIncapacity;
    }

    public function setOriginIncapacity(string $originIncapacity): self
    {
        $this->originIncapacity = $originIncapacity;

        return $this;
    }

    public function getMedicalSupportPdf(): ?string
    {
        return $this->medicalSupportPdf;
    }

    public function setMedicalSupportPdf(?string $medicalSupportPdf): self
    {
        $this->medicalSupportPdf = $medicalSupportPdf;

        return $this;
    }

    public function getEpsSupportPdf(): ?string
    {
        return $this->epsSupportPdf;
    }

    public function setEpsSupportPdf(?string $epsSupportPdf): self
    {
        $this->epsSupportPdf = $epsSupportPdf;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

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
