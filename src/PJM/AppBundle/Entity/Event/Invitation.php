<?php

namespace PJM\AppBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Invitation
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Event\InvitationRepository")
 */
class Invitation
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
     * @ORM\Column(name="hasPaid", type="boolean")
     */
    private $hasPaid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="estPresent", type="boolean")
     */
    private $estPresent;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Event\Evenement", inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Aucun évènement spécifié.")
     **/
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Aucun invité spécifié.")
     **/
    private $invite;


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
     * Set hasPaid
     *
     * @param boolean $hasPaid
     *
     * @return Invitation
     */
    public function setHasPaid($hasPaid)
    {
        $this->hasPaid = $hasPaid;

        return $this;
    }

    /**
     * Get hasPaid
     *
     * @return boolean
     */
    public function getHasPaid()
    {
        return $this->hasPaid;
    }

    /**
     * Set estPresent
     *
     * @param boolean $estPresent
     *
     * @return Invitation
     */
    public function setEstPresent($estPresent)
    {
        $this->estPresent = $estPresent;

        return $this;
    }

    /**
     * Get estPresent
     *
     * @return boolean
     */
    public function getEstPresent()
    {
        return $this->estPresent;
    }

    /**
     * Set event
     *
     * @param \PJM\AppBundle\Entity\Event\Evenement $event
     *
     * @return Invitation
     */
    public function setEvent(\PJM\AppBundle\Entity\Event\Evenement $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \PJM\AppBundle\Entity\Event\Evenement
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set invite
     *
     * @param \PJM\UserBundle\Entity\User $invite
     *
     * @return Invitation
     */
    public function setInvite(\PJM\UserBundle\Entity\User $invite)
    {
        $this->invite = $invite;

        return $this;
    }

    /**
     * Get invite
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getInvite()
    {
        return $this->invite;
    }
}
