<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractAssignment
 *
 * @ORM\Table(name="contract_assignment", indexes={@ORM\Index(name="profile_id", columns={"profile_id"}), @ORM\Index(name="charge_id", columns={"charge_id"}), @ORM\Index(name="contract_id", columns={"contract_id"})})
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
     * @var \ContractCharges
     *
     * @ORM\ManyToOne(targetEntity="ContractCharges")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="charge_id", referencedColumnName="id")
     * })
     */
    private $charge;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharge(): ?ContractCharges
    {
        return $this->charge;
    }

    public function setCharge(?ContractCharges $charge): static
    {
        $this->charge = $charge;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): static
    {
        $this->contract = $contract;

        return $this;
    }


}
