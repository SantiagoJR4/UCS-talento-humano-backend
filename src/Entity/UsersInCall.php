<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * UsersInCall
 *
 * @ORM\Table(name="users_in_call", indexes={@ORM\Index(name="fk_users_in_call_call", columns={"call_id"}), @ORM\Index(name="fk_users_in_call_user", columns={"user_id"})})
 * @ORM\Entity
 */
class UsersInCall
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="user_status", type="text", length=0, nullable=true, options={"comment"="CV:hoja_de_vida; KT:Prueba_de_conocimientos; PT:Psicotecnica; IN:Entrevista;CL:Clase; FI:final; SE: Seleccionado;"})
     */
    private $userStatus;

    /**
     * @var bool
     *
     * @ORM\Column(name="state_user_call", type="boolean", nullable=false)
     */
    private $stateUserCall;

    /**
     * @var string|null
     *
     * @ORM\Column(name="qualify_cv", type="text", length=0, nullable=true)
     */
    private $qualifyCv;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="text", length=0, nullable=true)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hv_rating", type="text", length=0, nullable=true)
     */
    private $hvRating;

    /**
     * @var string|null
     *
     * @ORM\Column(name="knowledge_rating", type="decimal", precision=4, scale=3, nullable=true)
     */
    private $knowledgeRating;

    /**
     * @var string|null
     *
     * @ORM\Column(name="knowledge_test_file", type="text", length=65535, nullable=true)
     */
    private $knowledgeTestFile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="psycho_rating", type="decimal", precision=4, scale=3, nullable=true)
     */
    private $psychoRating;

    /**
     * @var string|null
     *
     * @ORM\Column(name="psycho_test_file", type="text", length=65535, nullable=true)
     */
    private $psychoTestFile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="psycho_test_report", type="text", length=65535, nullable=true)
     */
    private $psychoTestReport;

    /**
     * @var string|null
     *
     * @ORM\Column(name="interview_rating", type="decimal", precision=4, scale=3, nullable=true)
     */
    private $interviewRating;

    /**
     * @var string|null
     *
     * @ORM\Column(name="interview_file", type="text", length=65535, nullable=true)
     */
    private $interviewFile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="class_rating", type="decimal", precision=4, scale=3, nullable=true)
     */
    private $classRating;

    /**
     * @var string|null
     *
     * @ORM\Column(name="final_rating", type="decimal", precision=4, scale=3, nullable=true)
     */
    private $finalRating;

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
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserStatus(): ?string
    {
        return $this->userStatus;
    }

    public function setUserStatus(?string $userStatus): self
    {
        $this->userStatus = $userStatus;

        return $this;
    }

    public function isStateUserCall(): ?bool
    {
        return $this->stateUserCall;
    }

    public function setStateUserCall(bool $stateUserCall): self
    {
        $this->stateUserCall = $stateUserCall;

        return $this;
    }

    public function getQualifyCv(): ?string
    {
        return $this->qualifyCv;
    }

    public function setQualifyCv(?string $qualifyCv): self
    {
        $this->qualifyCv = $qualifyCv;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHvRating(): ?string
    {
        return $this->hvRating;
    }

    public function setHvRating(?string $hvRating): self
    {
        $this->hvRating = $hvRating;

        return $this;
    }

    public function getKnowledgeRating(): ?string
    {
        return $this->knowledgeRating;
    }

    public function setKnowledgeRating(?string $knowledgeRating): self
    {
        $this->knowledgeRating = $knowledgeRating;

        return $this;
    }

    public function getKnowledgeTestFile(): ?string
    {
        return $this->knowledgeTestFile;
    }

    public function setKnowledgeTestFile(?string $knowledgeTestFile): self
    {
        $this->knowledgeTestFile = $knowledgeTestFile;

        return $this;
    }

    public function getPsychoRating(): ?string
    {
        return $this->psychoRating;
    }

    public function setPsychoRating(?string $psychoRating): self
    {
        $this->psychoRating = $psychoRating;

        return $this;
    }

    public function getPsychoTestFile(): ?string
    {
        return $this->psychoTestFile;
    }

    public function setPsychoTestFile(?string $psychoTestFile): self
    {
        $this->psychoTestFile = $psychoTestFile;

        return $this;
    }

    public function getPsychoTestReport(): ?string
    {
        return $this->psychoTestReport;
    }

    public function setPsychoTestReport(?string $psychoTestReport): self
    {
        $this->psychoTestReport = $psychoTestReport;

        return $this;
    }

    public function getInterviewRating(): ?string
    {
        return $this->interviewRating;
    }

    public function setInterviewRating(?string $interviewRating): self
    {
        $this->interviewRating = $interviewRating;

        return $this;
    }

    public function getInterviewFile(): ?string
    {
        return $this->interviewFile;
    }

    public function setInterviewFile(?string $interviewFile): self
    {
        $this->interviewFile = $interviewFile;

        return $this;
    }

    public function getClassRating(): ?string
    {
        return $this->classRating;
    }

    public function setClassRating(?string $classRating): self
    {
        $this->classRating = $classRating;

        return $this;
    }

    public function getFinalRating(): ?string
    {
        return $this->finalRating;
    }

    public function setFinalRating(?string $finalRating): self
    {
        $this->finalRating = $finalRating;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


}
