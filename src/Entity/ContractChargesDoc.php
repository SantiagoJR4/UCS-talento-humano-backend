<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractChargesDoc
 *
 * @ORM\Table(name="contract_charges_doc", indexes={@ORM\Index(name="fk_contract_charges_doc_profile", columns={"profile_id"})})
 * @ORM\Entity
 */
class ContractChargesDoc
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
