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
     *
     * @ORM\Column(name="knowledge_test", type="integer", nullable=true)
     */
    private $knowledgeTest;

    /**
     * @var int|null
     *
     * @ORM\Column(name="psycho_test", type="integer", nullable=true)
     */
    private $psychoTest;

    /**
     * @var int|null
     *
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
     * @ORM\Column(name="work_experience", type="integer", nullable=true)
     */
    private $workExperience;

    /**
     * @var int|null
     *
     * @ORM\Column(name="teaching_experience", type="integer", nullable=true)
     */
    private $teachingExperience;

    /**
     * @var int|null
     *
     * @ORM\Column(name="intellectual_production", type="integer", nullable=true)
     */
    private $intellectualProduction;

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

    public function setCurriculumVitae(?int $curriculumVitae): self
    {
        $this->curriculumVitae = $curriculumVitae;

        return $this;
    }

    public function getKnowledgeTest(): ?int
    {
        return $this->knowledgeTest;
    }

    public function setKnowledgeTest(?int $knowledgeTest): self
    {
        $this->knowledgeTest = $knowledgeTest;

        return $this;
    }

    public function getPsychoTest(): ?int
    {
        return $this->psychoTest;
    }

    public function setPsychoTest(?int $psychoTest): self
    {
        $this->psychoTest = $psychoTest;

        return $this;
    }

    public function getInterview(): ?int
    {
        return $this->interview;
    }

    public function setInterview(?int $interview): self
    {
        $this->interview = $interview;

        return $this;
    }

    public function getClass(): ?int
    {
        return $this->class;
    }

    public function setClass(?int $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getUnderGraduateTraining(): ?int
    {
        return $this->underGraduateTraining;
    }

    public function setUnderGraduateTraining(?int $underGraduateTraining): self
    {
        $this->underGraduateTraining = $underGraduateTraining;

        return $this;
    }

    public function getPostGraduateTraining(): ?int
    {
        return $this->postGraduateTraining;
    }

    public function setPostGraduateTraining(?int $postGraduateTraining): self
    {
        $this->postGraduateTraining = $postGraduateTraining;

        return $this;
    }

    public function getWorkExperience(): ?int
    {
        return $this->workExperience;
    }

    public function setWorkExperience(?int $workExperience): self
    {
        $this->workExperience = $workExperience;

        return $this;
    }

    public function getTeachingExperience(): ?int
    {
        return $this->teachingExperience;
    }

    public function setTeachingExperience(?int $teachingExperience): self
    {
        $this->teachingExperience = $teachingExperience;

        return $this;
    }

    public function getIntellectualProduction(): ?int
    {
        return $this->intellectualProduction;
    }

    public function setIntellectualProduction(?int $intellectualProduction): self
    {
        $this->intellectualProduction = $intellectualProduction;

        return $this;
    }

    public function getFurtherTraining(): ?int
    {
        return $this->furtherTraining;
    }

    public function setFurtherTraining(?int $furtherTraining): self
    {
        $this->furtherTraining = $furtherTraining;

        return $this;
    }

    public function getHvScore(): ?string
    {
        return $this->hvScore;
    }

    public function setHvScore(?string $hvScore): self
    {
        $this->hvScore = $hvScore;

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
