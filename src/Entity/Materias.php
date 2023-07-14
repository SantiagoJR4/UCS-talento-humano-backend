<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Materias
 *
 * @ORM\Table(name="materias", indexes={@ORM\Index(name="programa_id", columns={"programa_id"}), @ORM\Index(name="materia_id", columns={"materia_id"})})
 * @ORM\Entity
 */
class Materias
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
     * @ORM\Column(name="codigo", type="string", length=6, nullable=true, options={"fixed"=true})
     */
    private $codigo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", length=60, nullable=true)
     */
    private $nombre;

    /**
     * @var int|null
     *
     * @ORM\Column(name="semestre", type="integer", nullable=true)
     */
    private $semestre;

    /**
     * @var int|null
     *
     * @ORM\Column(name="creditos", type="integer", nullable=true)
     */
    private $creditos;

    /**
     * @var int|null
     *
     * @ORM\Column(name="horas", type="integer", nullable=true)
     */
    private $horas;

    /**
     * @var string|null
     *
     * @ORM\Column(name="estado", type="string", length=1, nullable=true, options={"fixed"=true,"comment"="0: Inactiva | 1: Activa"})
     */
    private $estado;

    /**
     * @var string|null
     *
     * @ORM\Column(name="codigoMoodle", type="string", length=20, nullable=true)
     */
    private $codigomoodle;

    /**
     * @var int|null
     *
     * @ORM\Column(name="materia_id", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $materiaId;

    /**
     * @var \Programas
     *
     * @ORM\ManyToOne(targetEntity="Programas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="programa_id", referencedColumnName="id")
     * })
     */
    private $programa;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getSemestre(): ?int
    {
        return $this->semestre;
    }

    public function setSemestre(?int $semestre): static
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function getCreditos(): ?int
    {
        return $this->creditos;
    }

    public function setCreditos(?int $creditos): static
    {
        $this->creditos = $creditos;

        return $this;
    }

    public function getHoras(): ?int
    {
        return $this->horas;
    }

    public function setHoras(?int $horas): static
    {
        $this->horas = $horas;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getCodigomoodle(): ?string
    {
        return $this->codigomoodle;
    }

    public function setCodigomoodle(?string $codigomoodle): static
    {
        $this->codigomoodle = $codigomoodle;

        return $this;
    }

    public function getMateriaId(): ?int
    {
        return $this->materiaId;
    }

    public function setMateriaId(?int $materiaId): static
    {
        $this->materiaId = $materiaId;

        return $this;
    }

    public function getPrograma(): ?Programas
    {
        return $this->programa;
    }

    public function setPrograma(?Programas $programa): static
    {
        $this->programa = $programa;

        return $this;
    }


}
