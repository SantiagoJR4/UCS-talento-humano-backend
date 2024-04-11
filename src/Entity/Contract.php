<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contract
 *
 * @ORM\Table(name="contract", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Contract
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
     * @ORM\Column(name="type_contract", type="string", length=255, nullable=false)
     */
    private $typeContract;

    /**
     * @var string
     *
     * @ORM\Column(name="period", type="string", length=255, nullable=false)
     */
    private $period;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="work_start", type="date", nullable=false)
     */
    private $workStart;

    /**
     * @var string
     *
     * @ORM\Column(name="initial_contract", type="string", length=255, nullable=false)
     */
    private $initialContract;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_contract", type="date", nullable=false)
     */
    private $expirationContract;

    /**
     * @var string
     *
     * @ORM\Column(name="work_dedication", type="text", length=0, nullable=false)
     */
    private $workDedication;

    /**
     * @var int
     *
     * @ORM\Column(name="salary", type="integer", nullable=false)
     */
    private $salary;

    /**
     * @var int
     *
     * @ORM\Column(name="weekly_hours", type="integer", nullable=false)
     */
    private $weeklyHours;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", length=0, nullable=false)
     */
    private $functions;

    /**
     * @var string
     *
     * @ORM\Column(name="specific_functions", type="text", length=0, nullable=false)
     */
    private $specificFunctions;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"comment"="0:Inactivo
1:Activo"})
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_file", type="text", length=0, nullable=false)
     */
    private $contractFile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="contract_file_pdf", type="text", length=0, nullable=true)
     */
    private $contractFilePdf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="workload", type="text", length=0, nullable=true)
     */
    private $workload;

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

    public function getTypeContract(): ?string
    {
        return $this->typeContract;
    }

    public function setTypeContract(string $typeContract): self
    {
        $this->typeContract = $typeContract;

        return $this;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function setPeriod(string $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getWorkStart(): ?\DateTimeInterface
    {
        return $this->workStart;
    }

    public function setWorkStart(\DateTimeInterface $workStart): self
    {
        $this->workStart = $workStart;

        return $this;
    }

    public function getInitialContract(): ?string
    {
        return $this->initialContract;
    }

    public function setInitialContract(string $initialContract): self
    {
        $this->initialContract = $initialContract;

        return $this;
    }

    public function getExpirationContract(): ?\DateTimeInterface
    {
        return $this->expirationContract;
    }

    public function setExpirationContract(\DateTimeInterface $expirationContract): self
    {
        $this->expirationContract = $expirationContract;

        return $this;
    }

    public function getWorkDedication(): ?string
    {
        return $this->workDedication;
    }

    public function setWorkDedication(string $workDedication): self
    {
        $this->workDedication = $workDedication;

        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getWeeklyHours(): ?int
    {
        return $this->weeklyHours;
    }

    public function setWeeklyHours(int $weeklyHours): self
    {
        $this->weeklyHours = $weeklyHours;

        return $this;
    }

    public function getFunctions(): ?string
    {
        return $this->functions;
    }

    public function setFunctions(string $functions): self
    {
        $this->functions = $functions;

        return $this;
    }

    public function getSpecificFunctions(): ?string
    {
        return $this->specificFunctions;
    }

    public function setSpecificFunctions(string $specificFunctions): self
    {
        $this->specificFunctions = $specificFunctions;

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

    public function getContractFile(): ?string
    {
        return $this->contractFile;
    }

    public function setContractFile(string $contractFile): self
    {
        $this->contractFile = $contractFile;

        return $this;
    }

    public function getContractFilePdf(): ?string
    {
        return $this->contractFilePdf;
    }

    public function setContractFilePdf(?string $contractFilePdf): self
    {
        $this->contractFilePdf = $contractFilePdf;

        return $this;
    }

    public function getWorkload(): ?string
    {
        return $this->workload;
    }

    public function setWorkload(?string $workload): self
    {
        $this->workload = $workload;

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
