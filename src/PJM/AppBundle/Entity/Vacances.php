<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Vacances
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\VacancesRepository")
 */
class Vacances
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbJours", type="smallint")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @Assert\LessThan(30)
     */
    private $nbJours;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @Assert\NotBlank()
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="credite_brags", type="boolean")
     */
    private $crediteBrags;


    public function __construct()
    {
        $this->date = new \DateTime();
        $this->crediteBrags = false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nbJours
     *
     * @param integer $nbJours
     * @return Vacances
     */
    public function setNbJours($nbJours)
    {
        $this->nbJours = $nbJours;

        return $this;
    }

    /**
     * Get nbJours
     *
     * @return integer
     */
    public function getNbJours()
    {
        return $this->nbJours;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Vacances
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set crediteBrags
     *
     * @param boolean $crediteBrags
     * @return Vacances
     */
    public function setCrediteBrags($crediteBrags)
    {
        $this->crediteBrags = $crediteBrags;

        return $this;
    }

    /**
     * Get crediteBrags
     *
     * @return boolean
     */
    public function getCrediteBrags()
    {
        return $this->crediteBrags;
    }
}
