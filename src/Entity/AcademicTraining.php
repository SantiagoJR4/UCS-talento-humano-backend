<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AcademicTraining
 *
 * @ORM\Table(name="academic_training", indexes={@ORM\Index(name="fk_academicTraining_user", columns={"user_id"})})
 * @ORM\Entity
 */
class AcademicTraining
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
     * @ORM\Column(name="academic_modality", type="string", length=3, nullable=false)
     */
    private $academicModality;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="program_methodology", type="string", length=3, nullable=false, options={"fixed"=true})
     */
    private $programMethodology;

    /**
     * @var string
     *
     * @ORM\Column(name="title_name", type="string", length=255, nullable=false)
     */
    private $titleName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="snies", type="string", length=4, nullable=true)
     */
    private $snies;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_foreign_university", type="boolean", nullable=false)
     */
    private $isForeignUniversity;

    /**
     * @var string
     *
     * @ORM\Column(name="name_university", type="string", length=255, nullable=false)
     */
    private $nameUniversity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="degree_pdf", type="text", length=0, nullable=true)
     */
    private $degreePdf;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma_pdf", type="text", length=0, nullable=false)
     */
    private $diplomaPdf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certified_title_pdf", type="text", length=0, nullable=true)
     */
    private $certifiedTitlePdf;

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


}
