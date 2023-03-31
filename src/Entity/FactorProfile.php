<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FactorProfile
 *
 * @ORM\Table(name="factor_profile", indexes={@ORM\Index(name="fk_factor", columns={"factor_id"}), @ORM\Index(name="fk_factor_profile", columns={"profile_id"})})
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
     * @var \Factor
     *
     * @ORM\ManyToOne(targetEntity="Factor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="factor_id", referencedColumnName="id")
     * })
     */
    private $factor;

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

    public function getCrest(): ?int
    {
        return $this->crest;
    }

    public function setCrest(int $crest): self
    {
        $this->crest = $crest;

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
