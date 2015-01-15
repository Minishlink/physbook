<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Responsable
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Responsable
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
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Responsabilite", inversedBy="responsables")
     **/
    private $responsabilite;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User", inversedBy="responsables")
     **/
    private $user;


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
     * Set active
     *
     * @param boolean $active
     * @return Responsable
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Responsable
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
     * Set responsabilite
     *
     * @param \PJM\AppBundle\Entity\Responsabilite $responsabilite
     * @return Responsable
     */
    public function setResponsabilite(\PJM\AppBundle\Entity\Responsabilite $responsabilite = null)
    {
        $this->responsabilite = $responsabilite;

        return $this;
    }

    /**
     * Get responsabilite
     *
     * @return \PJM\AppBundle\Entity\Responsabilite
     */
    public function getResponsabilite()
    {
        return $this->responsabilite;
    }

    /**
     * Set user
     *
     * @param \PJM\UserBundle\Entity\User $user
     * @return Responsable
     */
    public function setUser(\PJM\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
