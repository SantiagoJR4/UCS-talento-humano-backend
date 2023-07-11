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
     * @var int|null
     *
     * @ORM\Column(name="psycho_percentage", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $psychoPercentage;

    /**
     * @var int|null
     *
     * @ORM\Column(name="interview_percentage", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $interviewPercentage;

    /**
     * @var string|null
     *
     * @ORM\Column(name="competence_percentage", type="string", length=255, nullable=true)
     */
    private $competencePercentage;

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

    public function getPsychoPercentage(): ?int
    {
        return $this->psychoPercentage;
    }

    public function setPsychoPercentage(?int $psychoPercentage): static
    {
        $this->psychoPercentage = $psychoPercentage;

        return $this;
    }

    public function getInterviewPercentage(): ?int
    {
        return $this->interviewPercentage;
    }

    public function setInterviewPercentage(?int $interviewPercentage): static
    {
        $this->interviewPercentage = $interviewPercentage;

        return $this;
    }

    public function getCompetencePercentage(): ?string
    {
        return $this->competencePercentage;
    }

    public function setCompetencePercentage(?string $competencePercentage): static
    {
        $this->competencePercentage = $competencePercentage;

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

    public function getCompetenceProfile(): ?CompetenceProfile
    {
        return $this->competenceProfile;
    }

    public function setCompetenceProfile(?CompetenceProfile $competenceProfile): static
    {
        $this->competenceProfile = $competenceProfile;

        return $this;
    }


}
