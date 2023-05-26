<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractCharges
 *
 * @ORM\Table(name="contract_charges")
 * @ORM\Entity
 */
class ContractCharges
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
     * @ORM\Column(name="type_employee", type="string", length=1, nullable=false, options={"fixed"=true,"comment"="A-Administrativos
D-Docentes"})
     */
    private $typeEmployee;

    /**
     * @var string
     *
     * @ORM\Column(name="charge", type="string", length=255, nullable=false)
     */
    private $charge;

    /**
     * @var string
     *
     * @ORM\Column(name="work_dedication", type="string", length=255, nullable=false)
     */
    private $workDedication;

    /**
     * @var int
     *
     * @ORM\Column(name="salary", type="integer", nullable=false)
     */
    private $salary;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeEmployee(): ?string
    {
        return $this->typeEmployee;
    }

    public function setTypeEmployee(string $typeEmployee): self
    {
        $this->typeEmployee = $typeEmployee;

        return $this;
    }

    public function getCharge(): ?string
    {
        return $this->charge;
    }

    public function setCharge(string $charge): self
    {
        $this->charge = $charge;

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


}
