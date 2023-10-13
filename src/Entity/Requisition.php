<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Requisition
 *
 * @ORM\Table(name="requisition", indexes={@ORM\Index(name="fk_requisition_user", columns={"user_id"}), @ORM\Index(name="fk_requisition_profile", columns={"profile_id"})})
 * @ORM\Entity
 */
class Requisition
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
     * @ORM\Column(name="currentDate", type="date", nullable=false)
     */
    private $currentdate;

    /**
     * @var string
     *
     * @ORM\Column(name="type_requisition", type="string", length=1, nullable=false, options={"fixed"=true,"comment"="C:contrato, O:otro si"})
     */
    private $typeRequisition;

    /**
     * @var string
     *
     * @ORM\Column(name="object_contract", type="text", length=0, nullable=false)
     */
    private $objectContract;

    /**
     * @var string
     *
     * @ORM\Column(name="work_dedication", type="string", length=255, nullable=false)
     */
    private $workDedication;

    /**
     * @var string
     *
     * @ORM\Column(name="initial_contract", type="string", length=255, nullable=false)
     */
    private $initialContract;

    /**
     * @var string
     *
     * @ORM\Column(name="specific_functions", type="text", length=0, nullable=false)
     */
    private $specificFunctions;

    /**
     * @var int
     *
     * @ORM\Column(name="salary", type="integer", nullable=false)
     */
    private $salary;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"comment"="0:creada
1:aprobada JI
2:aprobada VF
3:aprobada R
4:rechazada
5:aceptada"})
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

    /**
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * })
     */
    private $profile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrentdate(): ?\DateTimeInterface
    {
        return $this->currentdate;
    }

    public function setCurrentdate(\DateTimeInterface $currentdate): self
    {
        $this->currentdate = $currentdate;

        return $this;
    }

    public function getTypeRequisition(): ?string
    {
        return $this->typeRequisition;
    }

    public function setTypeRequisition(string $typeRequisition): self
    {
        $this->typeRequisition = $typeRequisition;

        return $this;
    }

    public function getObjectContract(): ?string
    {
        return $this->objectContract;
    }

    public function setObjectContract(string $objectContract): self
    {
        $this->objectContract = $objectContract;

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

    public function getInitialContract(): ?string
    {
        return $this->initialContract;
    }

    public function setInitialContract(string $initialContract): self
    {
        $this->initialContract = $initialContract;

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

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): self
    {
        $this->salary = $salary;

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

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }


}
