<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subjects
 *
 * @ORM\Table(name="subjects", indexes={@ORM\Index(name="fk_subjects_materia", columns={"materia_id"}), @ORM\Index(name="fk_subjects_subprofile", columns={"subprofile_id"})})
 * @ORM\Entity
 */
class Subjects
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
     * @var \Subprofile
     *
     * @ORM\ManyToOne(targetEntity="Subprofile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subprofile_id", referencedColumnName="id")
     * })
     */
    private $subprofile;

    /**
     * @var \Materias
     *
     * @ORM\ManyToOne(targetEntity="Materias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="materia_id", referencedColumnName="id")
     * })
     */
    private $materia;

    /**
     * @var \Materias
     *
     * @ORM\ManyToOne(targetEntity="Materias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="materia_id", referencedColumnName="id")
     * })
     */
    private $materia;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubprofile(): ?Subprofile
    {
        return $this->subprofile;
    }

    public function setSubprofile(?Subprofile $subprofile): static
    {
        $this->subprofile = $subprofile;

        return $this;
    }

    public function getMateria(): ?Materias
    {
        return $this->materia;
    }

    public function setMateria(?Materias $materia): static
    {
        $this->materia = $materia;

        return $this;
    }


}
