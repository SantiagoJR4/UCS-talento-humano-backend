<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialProfile
 *
 * @ORM\Table(name="special_profile")
 * @ORM\Entity
 */
class SpecialProfile
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

    public function getId(): ?int
    {
        return $this->id;
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


}
