<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetencePercentage
 *
 * @ORM\Table(name="competence_percentage", indexes={@ORM\Index(name="fk_competence_percentage_call", columns={"call_id"}), @ORM\Index(name="fk_competence_percentage_competence_profile", columns={"competence_profile_id"})})
 * @ORM\Entity
 */
class CompetencePercentage
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
     * @ORM\Column(name="percentage", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $percentage;

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
     * @var \CompetenceProfile
     *
     * @ORM\ManyToOne(targetEntity="CompetenceProfile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competence_profile_id", referencedColumnName="id")
     * })
     */
    private $competenceProfile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPercentage(): ?int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): self
    {
        $this->percentage = $percentage;

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

    public function getCompetenceProfile(): ?CompetenceProfile
    {
        return $this->competenceProfile;
    }

    public function setCompetenceProfile(?CompetenceProfile $competenceProfile): self
    {
        $this->competenceProfile = $competenceProfile;

        return $this;
    }


}
