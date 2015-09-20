<?php

namespace PJM\AppBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Invitation.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Event\InvitationRepository")
 * @UniqueEntity(fields = {"event", "invite"})
 */
class Invitation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="estPresent", type="boolean", nullable=true)
     */
    private $estPresent;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Event\Evenement", inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Aucun évènement spécifié.")
     **/
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Aucun invité spécifié.")
     **/
    private $invite;

    public function __construct()
    {
        $this->hasPaid = false;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set estPresent.
     *
     * @param bool $estPresent
     *
     * @return Invitation
     */
    public function setEstPresent($estPresent = null)
    {
        $this->estPresent = $estPresent;

        return $this;
    }

    /**
     * Get estPresent.
     *
     * @return bool
     */
    public function getEstPresent()
    {
        return $this->estPresent;
    }

    /**
     * Set event.
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
     * Get event.
     *
     * @return \PJM\AppBundle\Entity\Event\Evenement
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set invite.
     *
     * @param \PJM\AppBundle\Entity\User $invite
     *
     * @return Invitation
     */
    public function setInvite(\PJM\AppBundle\Entity\User $invite)
    {
        $this->invite = $invite;

        return $this;
    }

    /**
     * Get invite.
     *
     * @return \PJM\AppBundle\Entity\User
     */
    public function getInvite()
    {
        return $this->invite;
    }
}
