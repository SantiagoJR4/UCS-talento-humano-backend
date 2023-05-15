<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * TblCall
 *
 * @ORM\Table(name="tbl_call", indexes={@ORM\Index(name="fk_tbl_call_profile", columns={"profile_id"}), @ORM\Index(name="fk_tbl_call_user", columns={"selected_user_id"})})
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
     * @var int
     *
     * @ORM\Column(name="name", type="integer", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"comment"="0 -> created,
1 -> rejected,
2 -> open,
3 -> deserted,
4 -> success"})
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="salary", type="text", length=0, nullable=false)
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
     * @ORM\Column(name="reception_deadline_date", type="date", nullable=false)
     */
    private $receptionDeadlineDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="selection_date", type="date", nullable=false)
     */
    private $selectionDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publication_date", type="date", nullable=false)
     */
    private $publicationDate;

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
     * @ORM\Column(name="psycho_and_interview_date", type="datetime", nullable=false)
     */
    private $psychoAndInterviewDate;

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
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * })
     */
    private $profile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?int
    {
        return $this->name;
    }

    public function setName(int $name): self
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

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

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

    public function getReceptionDeadlineDate(): ?\DateTimeInterface
    {
        return $this->receptionDeadlineDate;
    }

    public function setReceptionDeadlineDate(\DateTimeInterface $receptionDeadlineDate): self
    {
        $this->receptionDeadlineDate = $receptionDeadlineDate;

        return $this;
    }

    public function getSelectionDate(): ?\DateTimeInterface
    {
        return $this->selectionDate;
    }

    public function setSelectionDate(\DateTimeInterface $selectionDate): self
    {
        $this->selectionDate = $selectionDate;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

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

    public function getPsychoAndInterviewDate(): ?\DateTimeInterface
    {
        return $this->psychoAndInterviewDate;
    }

    public function setPsychoAndInterviewDate(\DateTimeInterface $psychoAndInterviewDate): self
    {
        $this->psychoAndInterviewDate = $psychoAndInterviewDate;

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

    public function getSelectedUser(): ?User
    {
        return $this->selectedUser;
    }

    public function setSelectedUser(?User $selectedUser): self
    {
        $this->selectedUser = $selectedUser;

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


}
