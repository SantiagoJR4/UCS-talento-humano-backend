<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contract
 *
 * @ORM\Table(name="contract", indexes={@ORM\Index(name="fk_contract_user", columns={"user_id"}), @ORM\Index(name="fk_contract_contract_charges", columns={"contract_charges_id"})})
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
     * @ORM\Column(name="type_contract", type="string", length=255, nullable=false, options={"comment"="Contrato a Término Fijo.
Contrato a término indefinido.
Contrato de Obra o labor.
Contrato civil por prestación de servicios.
Contrato de aprendizaje.
Contrato ocasional de trabajo.
"})
     */
    private $typeContract;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="work_start", type="date", nullable=false, options={"comment"="Fecha iniciación de labores"})
     */
    private $workStart;

    /**
     * @var string
     *
     * @ORM\Column(name="initial_contract", type="string", length=255, nullable=false, options={"comment"="Término inicial del contrato"})
     */
    private $initialContract;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_contract", type="date", nullable=false, options={"comment"="Vence el día"})
     */
    private $expirationContract;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", length=0, nullable=false)
     */
    private $functions;

    /**
     * @var \ContractCharges
     *
     * @ORM\ManyToOne(targetEntity="ContractCharges")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_charges_id", referencedColumnName="id")
     * })
     */
    private $contractCharges;

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

    public function getFunctions(): ?string
    {
        return $this->functions;
    }

    public function setFunctions(string $functions): self
    {
        $this->functions = $functions;

        return $this;
    }

    public function getContractCharges(): ?ContractCharges
    {
        return $this->contractCharges;
    }

    public function setContractCharges(?ContractCharges $contractCharges): self
    {
        $this->contractCharges = $contractCharges;

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
