<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * WorkHistory
 *
 * @ORM\Table(name="work_history", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class WorkHistory
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
     * @ORM\Column(name="type_document", type="string", length=255, nullable=false)
     */
    private $typeDocument;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_document", type="date", nullable=false)
     */
    private $dateDocument;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="newValue", type="string", length=255, nullable=true)
     */
    private $newvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="document_pdf", type="text", length=0, nullable=false)
     */
    private $documentPdf;

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

    public function getTypeDocument(): ?string
    {
        return $this->typeDocument;
    }

    public function setTypeDocument(string $typeDocument): static
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    public function getDateDocument(): ?\DateTimeInterface
    {
        return $this->dateDocument;
    }

    public function setDateDocument(\DateTimeInterface $dateDocument): static
    {
        $this->dateDocument = $dateDocument;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNewvalue(): ?string
    {
        return $this->newvalue;
    }

    public function setNewvalue(?string $newvalue): static
    {
        $this->newvalue = $newvalue;

        return $this;
    }

    public function getDocumentPdf(): ?string
    {
        return $this->documentPdf;
    }

    public function setDocumentPdf(string $documentPdf): static
    {
        $this->documentPdf = $documentPdf;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }


}
