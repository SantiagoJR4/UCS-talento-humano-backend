<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReferencesData
 *
 * @ORM\Table(name="references_data", indexes={@ORM\Index(name="fk_references_user", columns={"user_id"})})
 * @ORM\Entity
 */
class ReferencesData
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
     * @var string
     *
     * @ORM\Column(name="type_references", type="string", length=2, nullable=false, options={"fixed"=true})
     */
    private $typeReferences;

    /**
     * @var string
     *
     * @ORM\Column(name="names", type="string", length=255, nullable=false)
     */
    private $names;

    /**
     * @var string
     *
     * @ORM\Column(name="relationship", type="string", length=255, nullable=false)
     */
    private $relationship;

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", type="string", length=255, nullable=false)
     */
    private $occupation;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=12, nullable=false)
     */
    private $phone;

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

    public function getTypeReferences(): ?string
    {
        return $this->typeReferences;
    }

    public function setTypeReferences(string $typeReferences): self
    {
        $this->typeReferences = $typeReferences;

        return $this;
    }

    public function getNames(): ?string
    {
        return $this->names;
    }

    public function setNames(string $names): self
    {
        $this->names = $names;

        return $this;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function setRelationship(string $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function setOccupation(string $occupation): self
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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
