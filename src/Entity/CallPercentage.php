<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * CallPercentage
 *
 * @ORM\Table(name="call_percentage", indexes={@ORM\Index(name="fk_call_percentage_tbl_call", columns={"call_id"})})
 * @ORM\Entity
 */
class CallPercentage
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
     * @ORM\Column(name="curriculum_vitae", type="integer", nullable=true)
     */
    private $curriculumVitae;

    /**
     * @var int|null
     * @var int|null
     *
     * @ORM\Column(name="knowledge_test", type="integer", nullable=true)
     * @ORM\Column(name="knowledge_test", type="integer", nullable=true)
     */
    private $knowledgeTest;

    /**
     * @var int|null
     * @var int|null
     *
     * @ORM\Column(name="psycho_test", type="integer", nullable=true)
     * @ORM\Column(name="psycho_test", type="integer", nullable=true)
     */
    private $psychoTest;

    /**
     * @var int|null
     * @var int|null
     *
     * @ORM\Column(name="interview", type="integer", nullable=true)
     * @ORM\Column(name="interview", type="integer", nullable=true)
     */
    private $interview;

    /**
     * @var int|null
     *
     * @ORM\Column(name="class", type="integer", nullable=true)
     */
    private $class;

    /**
     * @var int|null
     *
     * @ORM\Column(name="under_graduate_training", type="integer", nullable=true)
     */
    private $underGraduateTraining;

    /**
     * @var int|null
     *
     * @ORM\Column(name="post_graduate_training", type="integer", nullable=true)
     */
    private $postGraduateTraining;

    /**
     * @var int|null
     *
     * @ORM\Column(name="previous_experience", type="integer", nullable=true)
     */
    private $previousExperience;

    /**
     * @var int|null
     *
     * @ORM\Column(name="further_training", type="integer", nullable=true)
     */
    private $furtherTraining;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hv_score", type="text", length=0, nullable=true)
     */
    private $hvScore;

    /**
     * @var \TblCall
     *
     * @ORM\ManyToOne(targetEntity="TblCall")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="call_id", referencedColumnName="id")
     * })
     */
    private $call;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurriculumVitae(): ?int
    {
        return $this->curriculumVitae;
    }

    public function setCurriculumVitae(?int $curriculumVitae): static
    {
        $this->curriculumVitae = $curriculumVitae;

        return $this;
    }

    public function getKnowledgeTest(): ?int
    {
        return $this->knowledgeTest;
    }

<<<<<<< HEAD
    public function setKnowledgeTest(?int $knowledgeTest): static
=======
    public function setKnowledgeTest(?int $knowledgeTest): self
>>>>>>> fixedHv-Backend
    {
        $this->knowledgeTest = $knowledgeTest;

        return $this;
    }

    public function getPsychoTest(): ?int
    {
        return $this->psychoTest;
    }

<<<<<<< HEAD
    public function setPsychoTest(?int $psychoTest): static
=======
    public function setPsychoTest(?int $psychoTest): self
>>>>>>> fixedHv-Backend
    {
        $this->psychoTest = $psychoTest;

        return $this;
    }

    public function getInterview(): ?int
    {
        return $this->interview;
    }

<<<<<<< HEAD
    public function setInterview(?int $interview): static
=======
    public function setInterview(?int $interview): self
>>>>>>> fixedHv-Backend
    {
        $this->interview = $interview;

        return $this;
    }

    public function getClass(): ?int
    {
        return $this->class;
    }

    public function setClass(?int $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getUnderGraduateTraining(): ?int
    {
        return $this->underGraduateTraining;
    }

    public function setUnderGraduateTraining(?int $underGraduateTraining): static
    {
        $this->underGraduateTraining = $underGraduateTraining;

        return $this;
    }

    public function getPostGraduateTraining(): ?int
    {
        return $this->postGraduateTraining;
    }

    public function setPostGraduateTraining(?int $postGraduateTraining): static
    {
        $this->postGraduateTraining = $postGraduateTraining;

        return $this;
    }

    public function getPreviousExperience(): ?int
    {
        return $this->previousExperience;
    }

    public function setPreviousExperience(?int $previousExperience): static
    {
        $this->previousExperience = $previousExperience;

        return $this;
    }

    public function getFurtherTraining(): ?int
    {
        return $this->furtherTraining;
    }

    public function setFurtherTraining(?int $furtherTraining): static
    {
        $this->furtherTraining = $furtherTraining;

        return $this;
    }

    public function getHvScore(): ?string
    {
        return $this->hvScore;
    }

    public function setHvScore(?string $hvScore): static
    {
        $this->hvScore = $hvScore;

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


}
