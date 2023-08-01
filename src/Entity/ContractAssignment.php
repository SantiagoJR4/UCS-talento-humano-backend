<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractAssignment
 *
 * @ORM\Table(name="contract_assignment", indexes={@ORM\Index(name="fk_contract_assignment_contract", columns={"contract_id"}), @ORM\Index(name="fk_contract_assignment_contract_charges", columns={"charge_id"}), @ORM\Index(name="fk_contract_assignment_profile", columns={"profile_id"})})
 * @ORM\Entity
 */
class ContractAssignment
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
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * })
     */
    private $profile;

    /**
     * @var \Contract
     *
     * @ORM\ManyToOne(targetEntity="Contract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     * })
     */
    private $contract;

    /**
     * @var \ContractCharges
     *
     * @ORM\ManyToOne(targetEntity="ContractCharges")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="charge_id", referencedColumnName="id")
     * })
     */
    private $charge;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getCharge(): ?ContractCharges
    {
        return $this->charge;
    }

    public function setCharge(?ContractCharges $charge): self
    {
        $this->charge = $charge;

        return $this;
    }


}
