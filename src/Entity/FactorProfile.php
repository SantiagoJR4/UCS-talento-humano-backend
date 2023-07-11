<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FactorProfile
 *
 * @ORM\Table(name="factor_profile", indexes={@ORM\Index(name="fk_factor_profile", columns={"call_id"}), @ORM\Index(name="fk_factor", columns={"factor_id"})})
 * @ORM\Entity
 */
class FactorProfile
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
     * @ORM\Column(name="crest", type="integer", nullable=false)
     */
    private $crest;

    /**
     * @var \TblCall
     *
     * @ORM\ManyToOne(targetEntity="TblCall")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="call_id", referencedColumnName="id")
     * })
     */
    private $call;

    /**
     * @var \Factor
     *
     * @ORM\ManyToOne(targetEntity="Factor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="factor_id", referencedColumnName="id")
     * })
     */
    private $factor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCrest(): ?int
    {
        return $this->crest;
    }

    public function setCrest(int $crest): self
    {
        $this->crest = $crest;

        return $this;
    }

    public function getCall(): ?TblCall
    {
        return $this->call;
    }

    public function setCall(?TblCall $call): static
    {
        $this->call = $call;

        return $this;
    }

    public function getFactor(): ?Factor
    {
        return $this->factor;
    }

    public function setFactor(?Factor $factor): self
    {
        $this->factor = $factor;

        return $this;
    }

    public function getCall(): ?TblCall
    {
        return $this->call;
    }

    public function setCall(?TblCall $call): self
    {
        $this->call = $call;

        return $this;
    }


}
