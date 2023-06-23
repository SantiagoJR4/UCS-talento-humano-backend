<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * UsersInCall
 *
 * @ORM\Table(name="users_in_call", indexes={@ORM\Index(name="fk_users_in_call_user", columns={"user_id"}), @ORM\Index(name="fk_users_in_call_call", columns={"call_id"})})
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
     * @var int
     *
     * @ORM\Column(name="user_status", type="smallint", nullable=false, options={"comment"="0->hv
1->knowledge
2->psycho_and_interview
3->final
4->selected"})
     */
    private $userStatus = '0';

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
     * @var int
     *
     * @ORM\Column(name="cv_status", type="smallint", nullable=false)
     */
    private $cvStatus = '0';

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

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

    public function getUserStatus(): ?int
    {
        return $this->userStatus;
    }

    public function setUserStatus(int $userStatus): self
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

    public function getCvStatus(): ?int
    {
        return $this->cvStatus;
    }

    public function setCvStatus(int $cvStatus): self
    {
        $this->cvStatus = $cvStatus;

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
