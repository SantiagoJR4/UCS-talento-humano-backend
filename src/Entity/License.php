<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * License
 *
 * @ORM\Table(name="license", indexes={@ORM\Index(name="fk_licence_user", columns={"user_id"})})
 * @ORM\Entity
 */
class License
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
     * @var string
     *
     * @ORM\Column(name="type_compensation", type="string", length=2, nullable=false, options={"fixed"=true,"comment"="R: remunerado, NR: no remunerado	"})
     */
    private $typeCompensation;

    /**
     * @var string
     *
     * @ORM\Column(name="type_license", type="string", length=255, nullable=false)
     */
    private $typeLicense;

    /**
     * @var string|null
     *
     * @ORM\Column(name="othertype_license", type="string", length=255, nullable=true)
     */
    private $othertypeLicense;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", length=65535, nullable=false)
     */
    private $reason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initial_date", type="date", nullable=false)
     */
    private $initialDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="final_date", type="date", nullable=false)
     */
    private $finalDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="support_pdf", type="text", length=0, nullable=true)
     */
    private $supportPdf;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"comment"="0: creada 1: aprobada JI 2: aprobada TH 3: rechazada 4: aceptada	"})
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

    public function getTypeCompensation(): ?string
    {
        return $this->typeCompensation;
    }

    public function setTypeCompensation(string $typeCompensation): self
    {
        $this->typeCompensation = $typeCompensation;

        return $this;
    }

    public function getTypeLicense(): ?string
    {
        return $this->typeLicense;
    }

    public function setTypeLicense(string $typeLicense): self
    {
        $this->typeLicense = $typeLicense;

        return $this;
    }

    public function getOthertypeLicense(): ?string
    {
        return $this->othertypeLicense;
    }

    public function setOthertypeLicense(?string $othertypeLicense): self
    {
        $this->othertypeLicense = $othertypeLicense;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getInitialDate(): ?\DateTimeInterface
    {
        return $this->initialDate;
    }

    public function setInitialDate(\DateTimeInterface $initialDate): self
    {
        $this->initialDate = $initialDate;

        return $this;
    }

    public function getFinalDate(): ?\DateTimeInterface
    {
        return $this->finalDate;
    }

    public function setFinalDate(\DateTimeInterface $finalDate): self
    {
        $this->finalDate = $finalDate;

        return $this;
    }

    public function getSupportPdf(): ?string
    {
        return $this->supportPdf;
    }

    public function setSupportPdf(?string $supportPdf): self
    {
        $this->supportPdf = $supportPdf;

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
