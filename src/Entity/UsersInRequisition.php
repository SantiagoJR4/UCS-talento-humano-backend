<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersInRequisition
 *
 * @ORM\Table(name="users_in_requisition", uniqueConstraints={@ORM\UniqueConstraint(name="requisition_id", columns={"requisition_id"}), @ORM\UniqueConstraint(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class UsersInRequisition
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
     * @var \Requisition
     *
     * @ORM\ManyToOne(targetEntity="Requisition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="requisition_id", referencedColumnName="id")
     * })
     */
    private $requisition;

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

    public function getRequisition(): ?Requisition
    {
        return $this->requisition;
    }

    public function setRequisition(?Requisition $requisition): self
    {
        $this->requisition = $requisition;

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
