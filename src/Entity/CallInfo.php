<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * CallInfo
 *
 * @ORM\Table(name="call_info")
 * @ORM\Entity
 */
class CallInfo
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
     * @ORM\Column(name="under_graduate", type="text", length=65535, nullable=false)
     */
    private $underGraduate;

    /**
     * @var string
     *
     * @ORM\Column(name="post_graduate", type="text", length=65535, nullable=false)
     */
    private $postGraduate;

    /**
     * @var string
     *
     * @ORM\Column(name="work_experience", type="text", length=65535, nullable=false)
     */
    private $workExperience;

    /**
     * @var string
     *
     * @ORM\Column(name="teaching_experience", type="text", length=65535, nullable=false)
     */
    private $teachingExperience;

    /**
     * @var string
     *
     * @ORM\Column(name="further_training", type="text", length=65535, nullable=false)
     */
    private $furtherTraining;

    /**
     * @var string
     *
     * @ORM\Column(name="basic_knowledge", type="text", length=65535, nullable=false)
     */
    private $basicKnowledge;

    /**
     * @var string
     *
     * @ORM\Column(name="salary", type="string", length=10, nullable=false)
     */
    private $salary;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="opening_date", type="date", nullable=false)
     */
    private $openingDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reception_deadline_for_cv_date", type="date", nullable=false)
     */
    private $receptionDeadlineForCvDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="selection_day_for_cv_date", type="date", nullable=false)
     */
    private $selectionDayForCvDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publication_for_cv_date", type="date", nullable=false)
     */
    private $publicationForCvDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="knowledge_test_date", type="datetime", nullable=false)
     */
    private $knowledgeTestDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="knowledge_results_date", type="date", nullable=false)
     */
    private $knowledgeResultsDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="psycho_and_interview_test_date", type="datetime", nullable=false)
     */
    private $psychoAndInterviewTestDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="final_results_date", type="date", nullable=false)
     */
    private $finalResultsDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_of_contract_date", type="date", nullable=false)
     */
    private $startOfContractDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUnderGraduate(): ?string
    {
        return $this->underGraduate;
    }

    public function setUnderGraduate(string $underGraduate): self
    {
        $this->underGraduate = $underGraduate;

        return $this;
    }

    public function getPostGraduate(): ?string
    {
        return $this->postGraduate;
    }

    public function setPostGraduate(string $postGraduate): self
    {
        $this->postGraduate = $postGraduate;

        return $this;
    }

    public function getWorkExperience(): ?string
    {
        return $this->workExperience;
    }

    public function setWorkExperience(string $workExperience): self
    {
        $this->workExperience = $workExperience;

        return $this;
    }

    public function getTeachingExperience(): ?string
    {
        return $this->teachingExperience;
    }

    public function setTeachingExperience(string $teachingExperience): self
    {
        $this->teachingExperience = $teachingExperience;

        return $this;
    }

    public function getFurtherTraining(): ?string
    {
        return $this->furtherTraining;
    }

    public function setFurtherTraining(string $furtherTraining): self
    {
        $this->furtherTraining = $furtherTraining;

        return $this;
    }

    public function getBasicKnowledge(): ?string
    {
        return $this->basicKnowledge;
    }

    public function setBasicKnowledge(string $basicKnowledge): self
    {
        $this->basicKnowledge = $basicKnowledge;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(string $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->openingDate;
    }

    public function setOpeningDate(\DateTimeInterface $openingDate): self
    {
        $this->openingDate = $openingDate;

        return $this;
    }

    public function getReceptionDeadlineForCvDate(): ?\DateTimeInterface
    {
        return $this->receptionDeadlineForCvDate;
    }

    public function setReceptionDeadlineForCvDate(\DateTimeInterface $receptionDeadlineForCvDate): self
    {
        $this->receptionDeadlineForCvDate = $receptionDeadlineForCvDate;

        return $this;
    }

    public function getSelectionDayForCvDate(): ?\DateTimeInterface
    {
        return $this->selectionDayForCvDate;
    }

    public function setSelectionDayForCvDate(\DateTimeInterface $selectionDayForCvDate): self
    {
        $this->selectionDayForCvDate = $selectionDayForCvDate;

        return $this;
    }

    public function getPublicationForCvDate(): ?\DateTimeInterface
    {
        return $this->publicationForCvDate;
    }

    public function setPublicationForCvDate(\DateTimeInterface $publicationForCvDate): self
    {
        $this->publicationForCvDate = $publicationForCvDate;

        return $this;
    }

    public function getKnowledgeTestDate(): ?\DateTimeInterface
    {
        return $this->knowledgeTestDate;
    }

    public function setKnowledgeTestDate(\DateTimeInterface $knowledgeTestDate): self
    {
        $this->knowledgeTestDate = $knowledgeTestDate;

        return $this;
    }

    public function getKnowledgeResultsDate(): ?\DateTimeInterface
    {
        return $this->knowledgeResultsDate;
    }

    public function setKnowledgeResultsDate(\DateTimeInterface $knowledgeResultsDate): self
    {
        $this->knowledgeResultsDate = $knowledgeResultsDate;

        return $this;
    }

    public function getPsychoAndInterviewTestDate(): ?\DateTimeInterface
    {
        return $this->psychoAndInterviewTestDate;
    }

    public function setPsychoAndInterviewTestDate(\DateTimeInterface $psychoAndInterviewTestDate): self
    {
        $this->psychoAndInterviewTestDate = $psychoAndInterviewTestDate;

        return $this;
    }

    public function getFinalResultsDate(): ?\DateTimeInterface
    {
        return $this->finalResultsDate;
    }

    public function setFinalResultsDate(\DateTimeInterface $finalResultsDate): self
    {
        $this->finalResultsDate = $finalResultsDate;

        return $this;
    }

    public function getStartOfContractDate(): ?\DateTimeInterface
    {
        return $this->startOfContractDate;
    }

    public function setStartOfContractDate(\DateTimeInterface $startOfContractDate): self
    {
        $this->startOfContractDate = $startOfContractDate;

        return $this;
    }


}
