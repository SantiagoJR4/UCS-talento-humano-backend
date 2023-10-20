<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 *
 * @ORM\Table(name="permission", indexes={@ORM\Index(name="fk_permission_user", columns={"user_id"})})
 * @ORM\Entity
 */
class Permission
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
     * @ORM\Column(name="type_permission", type="string", length=1, nullable=false, options={"fixed"=true,"comment"="P: personal, L:laboral, F: flexibilización de jornada	"})
     */
    private $typePermission;

    /**
     * @var string
     *
     * @ORM\Column(name="type_flexibility", type="string", length=2, nullable=false, options={"fixed"=true,"comment"="JC: Jornada continua, C: Compensada, TC: Trabajo desde casa	"})
     */
    private $typeFlexibility;

    /**
     * @var string
     *
     * @ORM\Column(name="type_compensation", type="string", length=2, nullable=false, options={"fixed"=true,"comment"="R: remunerado, NR: no remunerado	"})
     */
    private $typeCompensation;

    /**
     * @var string
     *
     * @ORM\Column(name="type_date_permission", type="string", length=1, nullable=false, options={"fixed"=true,"comment"="H: horas, D: días	"})
     */
    private $typeDatePermission;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", length=65535, nullable=false)
     */
    private $reason;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="initial_date", type="date", nullable=true)
     */
    private $initialDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="final_date", type="date", nullable=true)
     */
    private $finalDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_hour", type="time", nullable=true)
     */
    private $startHour;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="final_hour", type="time", nullable=true)
     */
    private $finalHour;

    /**
     * @var string|null
     *
     * @ORM\Column(name="support_pdf", type="text", length=0, nullable=true)
     */
    private $supportPdf;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"comment"="0: creada
1: aprobada JI
2: aprobada TH
3: rechazada
4: aceptada"})
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

    public function getTypePermission(): ?string
    {
        return $this->typePermission;
    }

    public function setTypePermission(string $typePermission): self
    {
        $this->typePermission = $typePermission;

        return $this;
    }

    public function getTypeFlexibility(): ?string
    {
        return $this->typeFlexibility;
    }

    public function setTypeFlexibility(string $typeFlexibility): self
    {
        $this->typeFlexibility = $typeFlexibility;

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

    public function getTypeDatePermission(): ?string
    {
        return $this->typeDatePermission;
    }

    public function setTypeDatePermission(string $typeDatePermission): self
    {
        $this->typeDatePermission = $typeDatePermission;

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

    public function setInitialDate(?\DateTimeInterface $initialDate): self
    {
        $this->initialDate = $initialDate;

        return $this;
    }

    public function getFinalDate(): ?\DateTimeInterface
    {
        return $this->finalDate;
    }

    public function setFinalDate(?\DateTimeInterface $finalDate): self
    {
        $this->finalDate = $finalDate;

        return $this;
    }

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(?\DateTimeInterface $startHour): self
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getFinalHour(): ?\DateTimeInterface
    {
        return $this->finalHour;
    }

    public function setFinalHour(?\DateTimeInterface $finalHour): self
    {
        $this->finalHour = $finalHour;

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
