<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * EvaluationCv
 *
 * @ORM\Table(name="evaluation_cv", indexes={@ORM\Index(name="fk_evaluation_cv_user", columns={"user_id"})})
 * @ORM\Entity
 */
class EvaluationCv
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
     * @var bool
     *
     * @ORM\Column(name="state", type="boolean", nullable=false)
     */
    private $state;

    /**
     * @var string|null
     *
     * @ORM\Column(name="msj_reject", type="text", length=65535, nullable=true)
     */
    private $msjReject;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="load_again", type="boolean", nullable=true)
     */
    private $loadAgain;

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

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getMsjReject(): ?string
    {
        return $this->msjReject;
    }

    public function setMsjReject(?string $msjReject): self
    {
        $this->msjReject = $msjReject;

        return $this;
    }

    public function isLoadAgain(): ?bool
    {
        return $this->loadAgain;
    }

    public function setLoadAgain(?bool $loadAgain): self
    {
        $this->loadAgain = $loadAgain;

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
