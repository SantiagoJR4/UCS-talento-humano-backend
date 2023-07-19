<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity
 */
class Profile
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="area", type="string", length=3, nullable=false)
     */
    private $area;

    /**
     * @var string|null
     *
     * @ORM\Column(name="charge", type="text", length=0, nullable=true)
     */
    private $charge;

    /**
     * @var string
     *
     * @ORM\Column(name="under_graduate_training", type="string", length=255, nullable=false)
     */
    private $underGraduateTraining;

    /**
     * @var string
     *
     * @ORM\Column(name="post_graduate_training", type="string", length=255, nullable=false)
     */
    private $postGraduateTraining;

    /**
     * @var string
     *
     * @ORM\Column(name="previous_experience", type="string", length=255, nullable=false)
     */
    private $previousExperience;

    /**
     * @var string
     *
     * @ORM\Column(name="further_training", type="string", length=255, nullable=false)
     */
    private $furtherTraining;

    /**
     * @var string
     *
     * @ORM\Column(name="special_requirements", type="text", length=65535, nullable=false)
     */
    private $specialRequirements;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", length=0, nullable=false)
     */
    private $functions;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(string $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getCharge(): ?string
    {
        return $this->charge;
    }

    public function setCharge(?string $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getUnderGraduateTraining(): ?string
    {
        return $this->underGraduateTraining;
    }

    public function setUnderGraduateTraining(string $underGraduateTraining): static
    {
        $this->underGraduateTraining = $underGraduateTraining;

        return $this;
    }

    public function getPostGraduateTraining(): ?string
    {
        return $this->postGraduateTraining;
    }

    public function setPostGraduateTraining(string $postGraduateTraining): static
    {
        $this->postGraduateTraining = $postGraduateTraining;

        return $this;
    }

    public function getPreviousExperience(): ?string
    {
        return $this->previousExperience;
    }

    public function setPreviousExperience(string $previousExperience): static
    {
        $this->previousExperience = $previousExperience;

        return $this;
    }

    public function getFurtherTraining(): ?string
    {
        return $this->furtherTraining;
    }

    public function setFurtherTraining(string $furtherTraining): static
    {
        $this->furtherTraining = $furtherTraining;

        return $this;
    }

    public function getSpecialRequirements(): ?string
    {
        return $this->specialRequirements;
    }

    public function setSpecialRequirements(string $specialRequirements): static
    {
        $this->specialRequirements = $specialRequirements;

        return $this;
    }

    public function getFunctions(): ?string
    {
        return $this->functions;
    }

    public function setFunctions(string $functions): static
    {
        $this->functions = $functions;

        return $this;
    }


}
