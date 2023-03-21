<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CallPercentage
 *
 * @ORM\Table(name="call_percentage")
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
     * @var int
     *
     * @ORM\Column(name="curriculum_vitae", type="integer", nullable=false)
     */
    private $curriculumVitae;

    /**
     * @var int
     *
     * @ORM\Column(name="knowledge_test", type="integer", nullable=false)
     */
    private $knowledgeTest;

    /**
     * @var int
     *
     * @ORM\Column(name="psycho_test", type="integer", nullable=false)
     */
    private $psychoTest;

    /**
     * @var int
     *
     * @ORM\Column(name="interview", type="integer", nullable=false)
     */
    private $interview;

    /**
     * @var int
     *
     * @ORM\Column(name="class", type="integer", nullable=false)
     */
    private $class;

    /**
     * @var int
     *
     * @ORM\Column(name="undergraduate", type="integer", nullable=false)
     */
    private $undergraduate;

    /**
     * @var int
     *
     * @ORM\Column(name="postgraduate", type="integer", nullable=false)
     */
    private $postgraduate;

    /**
     * @var int
     *
     * @ORM\Column(name="work_experience", type="integer", nullable=false)
     */
    private $workExperience;

    /**
     * @var int
     *
     * @ORM\Column(name="teacher_experience", type="integer", nullable=false)
     */
    private $teacherExperience;

    /**
     * @var int
     *
     * @ORM\Column(name="further_training", type="integer", nullable=false)
     */
    private $furtherTraining;

    /**
     * @var int
     *
     * @ORM\Column(name="intellectual_production", type="integer", nullable=false)
     */
    private $intellectualProduction;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_class_necessary", type="boolean", nullable=false)
     */
    private $isClassNecessary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_undergraduate_necessary", type="boolean", nullable=false)
     */
    private $isUndergraduateNecessary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_postgraduate_necessary", type="boolean", nullable=false)
     */
    private $isPostgraduateNecessary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_work_experience_necessary", type="boolean", nullable=false)
     */
    private $isWorkExperienceNecessary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_teacher_experience_necessary", type="boolean", nullable=false)
     */
    private $isTeacherExperienceNecessary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_further_training_necessary", type="boolean", nullable=false)
     */
    private $isFurtherTrainingNecessary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_intellectual_production_necessary", type="boolean", nullable=false)
     */
    private $isIntellectualProductionNecessary;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurriculumVitae(): ?int
    {
        return $this->curriculumVitae;
    }

    public function setCurriculumVitae(int $curriculumVitae): self
    {
        $this->curriculumVitae = $curriculumVitae;

        return $this;
    }

    public function getKnowledgeTest(): ?int
    {
        return $this->knowledgeTest;
    }

    public function setKnowledgeTest(int $knowledgeTest): self
    {
        $this->knowledgeTest = $knowledgeTest;

        return $this;
    }

    public function getPsychoTest(): ?int
    {
        return $this->psychoTest;
    }

    public function setPsychoTest(int $psychoTest): self
    {
        $this->psychoTest = $psychoTest;

        return $this;
    }

    public function getInterview(): ?int
    {
        return $this->interview;
    }

    public function setInterview(int $interview): self
    {
        $this->interview = $interview;

        return $this;
    }

    public function getClass(): ?int
    {
        return $this->class;
    }

    public function setClass(int $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getUndergraduate(): ?int
    {
        return $this->undergraduate;
    }

    public function setUndergraduate(int $undergraduate): self
    {
        $this->undergraduate = $undergraduate;

        return $this;
    }

    public function getPostgraduate(): ?int
    {
        return $this->postgraduate;
    }

    public function setPostgraduate(int $postgraduate): self
    {
        $this->postgraduate = $postgraduate;

        return $this;
    }

    public function getWorkExperience(): ?int
    {
        return $this->workExperience;
    }

    public function setWorkExperience(int $workExperience): self
    {
        $this->workExperience = $workExperience;

        return $this;
    }

    public function getTeacherExperience(): ?int
    {
        return $this->teacherExperience;
    }

    public function setTeacherExperience(int $teacherExperience): self
    {
        $this->teacherExperience = $teacherExperience;

        return $this;
    }

    public function getFurtherTraining(): ?int
    {
        return $this->furtherTraining;
    }

    public function setFurtherTraining(int $furtherTraining): self
    {
        $this->furtherTraining = $furtherTraining;

        return $this;
    }

    public function getIntellectualProduction(): ?int
    {
        return $this->intellectualProduction;
    }

    public function setIntellectualProduction(int $intellectualProduction): self
    {
        $this->intellectualProduction = $intellectualProduction;

        return $this;
    }

    public function isIsClassNecessary(): ?bool
    {
        return $this->isClassNecessary;
    }

    public function setIsClassNecessary(bool $isClassNecessary): self
    {
        $this->isClassNecessary = $isClassNecessary;

        return $this;
    }

    public function isIsUndergraduateNecessary(): ?bool
    {
        return $this->isUndergraduateNecessary;
    }

    public function setIsUndergraduateNecessary(bool $isUndergraduateNecessary): self
    {
        $this->isUndergraduateNecessary = $isUndergraduateNecessary;

        return $this;
    }

    public function isIsPostgraduateNecessary(): ?bool
    {
        return $this->isPostgraduateNecessary;
    }

    public function setIsPostgraduateNecessary(bool $isPostgraduateNecessary): self
    {
        $this->isPostgraduateNecessary = $isPostgraduateNecessary;

        return $this;
    }

    public function isIsWorkExperienceNecessary(): ?bool
    {
        return $this->isWorkExperienceNecessary;
    }

    public function setIsWorkExperienceNecessary(bool $isWorkExperienceNecessary): self
    {
        $this->isWorkExperienceNecessary = $isWorkExperienceNecessary;

        return $this;
    }

    public function isIsTeacherExperienceNecessary(): ?bool
    {
        return $this->isTeacherExperienceNecessary;
    }

    public function setIsTeacherExperienceNecessary(bool $isTeacherExperienceNecessary): self
    {
        $this->isTeacherExperienceNecessary = $isTeacherExperienceNecessary;

        return $this;
    }

    public function isIsFurtherTrainingNecessary(): ?bool
    {
        return $this->isFurtherTrainingNecessary;
    }

    public function setIsFurtherTrainingNecessary(bool $isFurtherTrainingNecessary): self
    {
        $this->isFurtherTrainingNecessary = $isFurtherTrainingNecessary;

        return $this;
    }

    public function isIsIntellectualProductionNecessary(): ?bool
    {
        return $this->isIntellectualProductionNecessary;
    }

    public function setIsIntellectualProductionNecessary(bool $isIntellectualProductionNecessary): self
    {
        $this->isIntellectualProductionNecessary = $isIntellectualProductionNecessary;

        return $this;
    }


}
