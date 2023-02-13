<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * IntellectualProduction
 *
 * @ORM\Table(name="intellectual_production")
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
     * @ORM\Column(name="url_cvlac", type="text", length=65535, nullable=false)
     */
    private $urlCvlac;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlCvlac(): ?string
    {
        return $this->urlCvlac;
    }

    public function setUrlCvlac(string $urlCvlac): self
    {
        $this->urlCvlac = $urlCvlac;

        return $this;
    }

    public function getTypeProd(): ?string
    {
        return $this->typeProd;
    }

    public function setTypeProd(string $typeProd): self
    {
        $this->typeProd = $typeProd;

        return $this;
    }

    public function getTitleProd(): ?string
    {
        return $this->titleProd;
    }

    public function setTitleProd(string $titleProd): self
    {
        $this->titleProd = $titleProd;

        return $this;
    }

    public function getUrlVerification(): ?string
    {
        return $this->urlVerification;
    }

    public function setUrlVerification(string $urlVerification): self
    {
        $this->urlVerification = $urlVerification;

        return $this;
    }


}
