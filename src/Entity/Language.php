<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Language
 *
 * @ORM\Table(name="language", indexes={@ORM\Index(name="fk_language_user", columns={"user_id"})})
 * @ORM\Entity
 */
class Language
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
     * @ORM\Column(name="name_language", type="string", length=255, nullable=false)
     */
    private $nameLanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="to_speak", type="string", length=3, nullable=false, options={"fixed"=true})
     */
    private $toSpeak;

    /**
     * @var string
     *
     * @ORM\Column(name="to_read", type="string", length=3, nullable=false, options={"fixed"=true})
     */
    private $toRead;

    /**
     * @var string
     *
     * @ORM\Column(name="to_write", type="string", length=3, nullable=false, options={"fixed"=true})
     */
    private $toWrite;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certified_pdf", type="text", length=65535, nullable=true)
     */
    private $certifiedPdf;

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

    public function getNameLanguage(): ?string
    {
        return $this->nameLanguage;
    }

    public function setNameLanguage(string $nameLanguage): self
    {
        $this->nameLanguage = $nameLanguage;

        return $this;
    }

    public function getToSpeak(): ?string
    {
        return $this->toSpeak;
    }

    public function setToSpeak(string $toSpeak): self
    {
        $this->toSpeak = $toSpeak;

        return $this;
    }

    public function getToRead(): ?string
    {
        return $this->toRead;
    }

    public function setToRead(string $toRead): self
    {
        $this->toRead = $toRead;

        return $this;
    }

    public function getToWrite(): ?string
    {
        return $this->toWrite;
    }

    public function setToWrite(string $toWrite): self
    {
        $this->toWrite = $toWrite;

        return $this;
    }

    public function getCertifiedPdf(): ?string
    {
        return $this->certifiedPdf;
    }

    public function setCertifiedPdf(?string $certifiedPdf): self
    {
        $this->certifiedPdf = $certifiedPdf;

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
