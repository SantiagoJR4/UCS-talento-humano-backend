<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * WorkHistory
 *
 * @ORM\Table(name="work_history", indexes={@ORM\Index(name="fk_work_history_user", columns={"user_id"})})
 * @ORM\Entity
 */
class WorkHistory
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
     * @ORM\Column(name="type_document", type="string", length=255, nullable=false)
     */
    private $typeDocument;

    /**
     * @var string|null
     *
     * @ORM\Column(name="new_charge", type="string", length=255, nullable=true)
     */
    private $newCharge;

    /**
     * @var string|null
     *
     * @ORM\Column(name="new_profile", type="string", length=255, nullable=true)
     */
    private $newProfile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="new_work_dedication", type="string", length=255, nullable=true)
     */
    private $newWorkDedication;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_document_final", type="date", nullable=true)
     */
    private $dateDocumentFinal;

    /**
     * @var string|null
     *
     * @ORM\Column(name="new_duration", type="string", length=255, nullable=true)
     */
    private $newDuration;

    /**
     * @var int|null
     *
     * @ORM\Column(name="new_salary", type="integer", nullable=true)
     */
    private $newSalary;

    /**
     * @var int|null
     *
     * @ORM\Column(name="new_weekly_hours", type="integer", nullable=true)
     */
    private $newWeeklyHours;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="hour", type="time", nullable=true)
     */
    private $hour;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_document_initial", type="date", nullable=true)
     */
    private $dateDocumentInitial;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="document_pdf", type="text", length=0, nullable=false)
     */
    private $documentPdf;

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

    public function getTypeDocument(): ?string
    {
        return $this->typeDocument;
    }

    public function setTypeDocument(string $typeDocument): self
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    public function getNewCharge(): ?string
    {
        return $this->newCharge;
    }

<<<<<<< HEAD
    public function setDateDocument(\DateTimeInterface $dateDocument): self
=======
    public function setNewCharge(?string $newCharge): self
>>>>>>> d3380203620501b3756662959676da6bfe4c8764
    {
        $this->newCharge = $newCharge;

        return $this;
    }

    public function getNewProfile(): ?string
    {
        return $this->newProfile;
    }

    public function setNewProfile(?string $newProfile): self
    {
        $this->newProfile = $newProfile;

        return $this;
    }

    public function getNewWorkDedication(): ?string
    {
        return $this->newWorkDedication;
    }

    public function setNewWorkDedication(?string $newWorkDedication): self
    {
        $this->newWorkDedication = $newWorkDedication;

        return $this;
    }

    public function getDateDocumentFinal(): ?\DateTimeInterface
    {
        return $this->dateDocumentFinal;
    }

    public function setDateDocumentFinal(?\DateTimeInterface $dateDocumentFinal): self
    {
        $this->dateDocumentFinal = $dateDocumentFinal;

        return $this;
    }

    public function getNewDuration(): ?string
    {
        return $this->newDuration;
    }

    public function setNewDuration(?string $newDuration): self
    {
        $this->newDuration = $newDuration;

        return $this;
    }

    public function getNewSalary(): ?int
    {
        return $this->newSalary;
    }

    public function setNewSalary(?int $newSalary): self
    {
        $this->newSalary = $newSalary;

        return $this;
    }

    public function getNewWeeklyHours(): ?int
    {
        return $this->newWeeklyHours;
    }

    public function setNewWeeklyHours(?int $newWeeklyHours): self
    {
        $this->newWeeklyHours = $newWeeklyHours;

        return $this;
    }

    public function getHour(): ?\DateTimeInterface
    {
        return $this->hour;
    }

    public function setHour(?\DateTimeInterface $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getDateDocumentInitial(): ?\DateTimeInterface
    {
        return $this->dateDocumentInitial;
    }

    public function setDateDocumentInitial(?\DateTimeInterface $dateDocumentInitial): self
    {
        $this->dateDocumentInitial = $dateDocumentInitial;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

<<<<<<< HEAD
    public function getNewvalue(): ?string
    {
        return $this->newvalue;
    }

    public function setNewvalue(?string $newvalue): self
    {
        $this->newvalue = $newvalue;

        return $this;
    }

=======
>>>>>>> d3380203620501b3756662959676da6bfe4c8764
    public function getDocumentPdf(): ?string
    {
        return $this->documentPdf;
    }

    public function setDocumentPdf(string $documentPdf): self
    {
        $this->documentPdf = $documentPdf;

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
