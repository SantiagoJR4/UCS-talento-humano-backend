<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Programas
 *
 * @ORM\Table(name="programas")
 * @ORM\Entity
 */
class Programas
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
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", length=50, nullable=true)
     */
    private $nombre;

    /**
     * @var int|null
     *
     * @ORM\Column(name="numeroSemestres", type="integer", nullable=true)
     */
    private $numerosemestres;

    /**
     * @var int|null
     *
     * @ORM\Column(name="totalCreditos", type="integer", nullable=true)
     */
    private $totalcreditos;

    /**
     * @var string|null
     *
     * @ORM\Column(name="codigoSnies", type="string", length=8, nullable=true)
     */
    private $codigosnies;

    /**
     * @var string|null
     *
     * @ORM\Column(name="codigo", type="string", length=2, nullable=true, options={"fixed"=true})
     */
    private $codigo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="director", type="string", length=100, nullable=true)
     */
    private $director;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cargo", type="string", length=100, nullable=true)
     */
    private $cargo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="correo", type="string", length=100, nullable=true)
     */
    private $correo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tipo", type="string", length=1, nullable=true, options={"fixed"=true,"comment"="1: Programa | 2: Extensión"})
     */
    private $tipo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getNumerosemestres(): ?int
    {
        return $this->numerosemestres;
    }

    public function setNumerosemestres(?int $numerosemestres): self
    {
        $this->numerosemestres = $numerosemestres;

        return $this;
    }

    public function getTotalcreditos(): ?int
    {
        return $this->totalcreditos;
    }

    public function setTotalcreditos(?int $totalcreditos): self
    {
        $this->totalcreditos = $totalcreditos;

        return $this;
    }

    public function getCodigosnies(): ?string
    {
        return $this->codigosnies;
    }

    public function setCodigosnies(?string $codigosnies): self
    {
        $this->codigosnies = $codigosnies;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(?string $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function getCargo(): ?string
    {
        return $this->cargo;
    }

    public function setCargo(?string $cargo): self
    {
        $this->cargo = $cargo;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(?string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }


}
