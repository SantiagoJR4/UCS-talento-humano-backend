<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * TblCall
 *
<<<<<<< HEAD
 * @ORM\Table(name="tbl_call", indexes={@ORM\Index(name="fk_tbl_call_subprofile", columns={"subprofile_id"}), @ORM\Index(name="fk_tbl_call_special_profile", columns={"special_profile_id"}), @ORM\Index(name="fk_tbl_call_profile", columns={"profile_id"}), @ORM\Index(name="fk_tbl_call_user", columns={"selected_user_id"})})
=======
 * @ORM\Table(name="tbl_call", indexes={@ORM\Index(name="fk_tbl_call_special_profile", columns={"special_profile_id"}), @ORM\Index(name="fk_tbl_call_profile", columns={"profile_id"}), @ORM\Index(name="fk_tbl_call_user", columns={"selected_user_id"}), @ORM\Index(name="fk_tbl_call_subprofile", columns={"subprofile_id"})})
>>>>>>> d3380203620501b3756662959676da6bfe4c8764
 * @ORM\Entity
 */
class TblCall
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
     * @ORM\Column(name="name", type="integer", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="steps_of_call", type="text", length=0, nullable=true)
     */
    private $stepsOfCall;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"comment"="0->created,
1->approvedTH,
2->approvedVF,
3->approvedRectory(wait-percentages),
4->open,
5->rejected,
6->success,
7->deserted
"})
     */
    private $state;

    /**
     * @var string|null
     *
     * @ORM\Column(name="required_for_percentages", type="text", length=0, nullable=true)
     */
    private $requiredForPercentages;

    /**
     * @var string|null
     *
     * @ORM\Column(name="required_for_curriculum_vitae", type="text", length=0, nullable=true)
     */
    private $requiredForCurriculumVitae;

    /**
     * @var string|null
     *
     * @ORM\Column(name="required_to_sign_up", type="text", length=0, nullable=true)
     */
    private $requiredToSignUp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="knowledge_test_minimum_score", type="decimal", precision=4, scale=3, nullable=true)
     */
    private $knowledgeTestMinimumScore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="jury", type="text", length=0, nullable=true)
     */
    private $jury;

    /**
     * @var string|null
     *
     * @ORM\Column(name="salary", type="text", length=0, nullable=true)
     */
    private $salary;

    /**
     * @var string|null
     *
     * @ORM\Column(name="history", type="text", length=0, nullable=true)
     */
    private $history;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="opening_date", type="date", nullable=true)
     */
    private $openingDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="reception_deadline_date", type="date", nullable=true)
     */
    private $receptionDeadlineDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="selection_date", type="date", nullable=true)
     */
    private $selectionDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="publication_date", type="date", nullable=true)
     */
    private $publicationDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="knowledge_test_date", type="datetime", nullable=true)
     */
    private $knowledgeTestDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="knowledge_results_date", type="date", nullable=true)
     */
    private $knowledgeResultsDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="psycho_date", type="datetime", nullable=true)
     */
    private $psychoDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="interview_date", type="datetime", nullable=true)
     */
    private $interviewDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="final_results_date", type="date", nullable=true)
     */
    private $finalResultsDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_of_contract_date", type="date", nullable=true)
     */
    private $startOfContractDate;

    /**
     * @var \Subprofile
     *
     * @ORM\ManyToOne(targetEntity="Subprofile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subprofile_id", referencedColumnName="id")
     * })
     */
    private $subprofile;

    /**
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * })
     */
    private $profile;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="selected_user_id", referencedColumnName="id")
     * })
     */
    private $selectedUser;

    /**
     * @var \SpecialProfile
     *
     * @ORM\ManyToOne(targetEntity="SpecialProfile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_profile_id", referencedColumnName="id")
     * })
     */
    private $specialProfile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?int
    {
        return $this->name;
    }

    public function setName(?int $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStepsOfCall(): ?string
    {
        return $this->stepsOfCall;
    }

    public function setStepsOfCall(?string $stepsOfCall): self
    {
        $this->stepsOfCall = $stepsOfCall;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getRequiredForPercentages(): ?string
    {
        return $this->requiredForPercentages;
    }

    public function setRequiredForPercentages(?string $requiredForPercentages): self
    {
        $this->requiredForPercentages = $requiredForPercentages;

        return $this;
    }

    public function getRequiredForCurriculumVitae(): ?string
    {
        return $this->requiredForCurriculumVitae;
    }

    public function setRequiredForCurriculumVitae(?string $requiredForCurriculumVitae): self
    {
        $this->requiredForCurriculumVitae = $requiredForCurriculumVitae;

        return $this;
    }

    public function getRequiredToSignUp(): ?string
    {
        return $this->requiredToSignUp;
    }

    public function setRequiredToSignUp(?string $requiredToSignUp): self
    {
        $this->requiredToSignUp = $requiredToSignUp;

        return $this;
    }

    public function getKnowledgeTestMinimumScore(): ?string
    {
        return $this->knowledgeTestMinimumScore;
    }

    public function setKnowledgeTestMinimumScore(?string $knowledgeTestMinimumScore): self
    {
        $this->knowledgeTestMinimumScore = $knowledgeTestMinimumScore;

        return $this;
    }

    public function getJury(): ?string
    {
        return $this->jury;
    }

<<<<<<< HEAD
    public function setJury(string $jury): self
=======
    public function setJury(?string $jury): self
>>>>>>> d3380203620501b3756662959676da6bfe4c8764
    {
        $this->jury = $jury;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(?string $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->openingDate;
    }

    public function setOpeningDate(?\DateTimeInterface $openingDate): self
    {
        $this->openingDate = $openingDate;

        return $this;
    }

    public function getReceptionDeadlineDate(): ?\DateTimeInterface
    {
        return $this->receptionDeadlineDate;
    }

    public function setReceptionDeadlineDate(?\DateTimeInterface $receptionDeadlineDate): self
    {
        $this->receptionDeadlineDate = $receptionDeadlineDate;

        return $this;
    }

    public function getSelectionDate(): ?\DateTimeInterface
    {
        return $this->selectionDate;
    }

    public function setSelectionDate(?\DateTimeInterface $selectionDate): self
    {
        $this->selectionDate = $selectionDate;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getKnowledgeTestDate(): ?\DateTimeInterface
    {
        return $this->knowledgeTestDate;
    }

    public function setKnowledgeTestDate(?\DateTimeInterface $knowledgeTestDate): self
    {
        $this->knowledgeTestDate = $knowledgeTestDate;

        return $this;
    }

    public function getKnowledgeResultsDate(): ?\DateTimeInterface
    {
        return $this->knowledgeResultsDate;
    }

    public function setKnowledgeResultsDate(?\DateTimeInterface $knowledgeResultsDate): self
    {
        $this->knowledgeResultsDate = $knowledgeResultsDate;

        return $this;
    }

    public function getPsychoDate(): ?\DateTimeInterface
    {
        return $this->psychoDate;
    }

    public function setPsychoDate(?\DateTimeInterface $psychoDate): self
    {
        $this->psychoDate = $psychoDate;

        return $this;
    }

    public function getInterviewDate(): ?\DateTimeInterface
    {
        return $this->interviewDate;
    }

    public function setInterviewDate(?\DateTimeInterface $interviewDate): self
    {
        $this->interviewDate = $interviewDate;

        return $this;
    }

    public function getFinalResultsDate(): ?\DateTimeInterface
    {
        return $this->finalResultsDate;
    }

    public function setFinalResultsDate(?\DateTimeInterface $finalResultsDate): self
    {
        $this->finalResultsDate = $finalResultsDate;

        return $this;
    }

    public function getStartOfContractDate(): ?\DateTimeInterface
    {
        return $this->startOfContractDate;
    }

    public function setStartOfContractDate(?\DateTimeInterface $startOfContractDate): self
    {
        $this->startOfContractDate = $startOfContractDate;

        return $this;
    }

    public function getSubprofile(): ?Subprofile
    {
        return $this->subprofile;
    }

    public function setSubprofile(?Subprofile $subprofile): self
    {
        $this->subprofile = $subprofile;

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

    public function getSelectedUser(): ?User
    {
        return $this->selectedUser;
    }

    public function setSelectedUser(?User $selectedUser): self
    {
        $this->selectedUser = $selectedUser;

        return $this;
    }

    public function getSpecialProfile(): ?SpecialProfile
    {
        return $this->specialProfile;
    }

    public function setSpecialProfile(?SpecialProfile $specialProfile): self
    {
        $this->specialProfile = $specialProfile;

        return $this;
    }


}
