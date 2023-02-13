<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReferencesData
 *
 * @ORM\Table(name="references_data")
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

    public function getId(): ?int
    {
        return $this->id;
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


}
