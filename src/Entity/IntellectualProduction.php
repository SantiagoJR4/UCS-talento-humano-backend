<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * IntellectualProduction
 *
 * @ORM\Table(name="intellectual_production", indexes={@ORM\Index(name="fk_intellectualProd_user", columns={"user_id"})})
 * @ORM\Entity
 */
class IntellectualProduction
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
     * @ORM\Column(name="type_prod", type="string", length=2, nullable=false, options={"fixed"=true})
     */
    private $typeProd;

    /**
     * @var string
     *
     * @ORM\Column(name="title_prod", type="string", length=255, nullable=false)
     */
    private $titleProd;

    /**
     * @var string
     *
     * @ORM\Column(name="url_verification", type="text", length=65535, nullable=false)
     */
    private $urlVerification;

    /**
     * @var string|null
     *
     * @ORM\Column(name="history", type="text", length=0, nullable=true)
     */
    private $history;

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

    public function getTypeProd(): ?string
    {
        return $this->typeProd;
    }

    public function setTypeProd(string $typeProd): static
    {
        $this->typeProd = $typeProd;

        return $this;
    }

    public function getTitleProd(): ?string
    {
        return $this->titleProd;
    }

    public function setTitleProd(string $titleProd): static
    {
        $this->titleProd = $titleProd;

        return $this;
    }

    public function getUrlVerification(): ?string
    {
        return $this->urlVerification;
    }

    public function setUrlVerification(string $urlVerification): static
    {
        $this->urlVerification = $urlVerification;

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): static
    {
        $this->history = $history;

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
