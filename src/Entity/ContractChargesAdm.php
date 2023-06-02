<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractChargesAdm
 *
 * @ORM\Table(name="contract_charges_adm")
 * @ORM\Entity
 */
class ContractChargesAdm
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
