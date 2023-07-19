<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractAssignment
 *
 * @ORM\Table(name="contract_assignment")
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
     * @var int
     *
     * @ORM\Column(name="contract_id", type="integer", nullable=false)
     */
    private $contractId;

    /**
     * @var int
     *
     * @ORM\Column(name="profile_id", type="integer", nullable=false)
     */
    private $profileId;

    /**
     * @var int
     *
     * @ORM\Column(name="charge_id", type="integer", nullable=false)
     */
    private $chargeId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContractId(): ?int
    {
        return $this->contractId;
    }

    public function setContractId(int $contractId): self
    {
        $this->contractId = $contractId;

        return $this;
    }

    public function getProfileId(): ?int
    {
        return $this->profileId;
    }

    public function setProfileId(int $profileId): self
    {
        $this->profileId = $profileId;

        return $this;
    }

    public function getChargeId(): ?int
    {
        return $this->chargeId;
    }

    public function setChargeId(int $chargeId): self
    {
        $this->chargeId = $chargeId;

        return $this;
    }


}
