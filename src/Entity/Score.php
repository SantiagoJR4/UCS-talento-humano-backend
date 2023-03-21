<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Score
 *
 * @ORM\Table(name="score", indexes={@ORM\Index(name="fk_score_competence_profile", columns={"competence_profile_id"}), @ORM\Index(name="fk_score_factor_profile", columns={"factor_profile_id"})})
 * @ORM\Entity
 */
class Score
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
     * @var \FactorProfile
     *
     * @ORM\ManyToOne(targetEntity="FactorProfile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="factor_profile_id", referencedColumnName="id")
     * })
     */
    private $factorProfile;

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

    public function getFactorProfile(): ?FactorProfile
    {
        return $this->factorProfile;
    }

    public function setFactorProfile(?FactorProfile $factorProfile): self
    {
        $this->factorProfile = $factorProfile;

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
