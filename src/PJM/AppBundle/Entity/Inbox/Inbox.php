<?php

namespace PJM\AppBundle\Entity\Inbox;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Inbox
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Inbox
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
     * @ORM\Column(name="nbNonLus", type="smallint")
     */
    private $nbNonLus;

    /**
     * @ORM\OneToOne(targetEntity="PJM\UserBundle\Entity\User", mappedBy="inbox")
     **/
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="MessagesInbox", mappedBy="inbox")
     **/
    private $received;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="expedition")
     **/
    private $sent;

    public function __construct() {
        $this->received = new ArrayCollection();
        $this->sent = new ArrayCollection();
        $this->setNbNonLus(0);
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
     * Set nbNonLus
     *
     * @param integer $nbNonLus
     * @return Inbox
     */
    public function setNbNonLus($nbNonLus)
    {
        $this->nbNonLus = $nbNonLus;

        return $this;
    }

    /**
     * Get nbNonLus
     *
     * @return integer
     */
    public function getNbNonLus()
    {
        return $this->nbNonLus;
    }

    /**
     * Set user
     *
     * @param \PJM\UserBundle\Entity\User $user
     * @return Inbox
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

    /**
     * Add received
     *
     * @param MessagesInbox $received
     * @return Inbox
     */
    public function addReceived(MessagesInbox $received)
    {
        $this->received[] = $received;

        return $this;
    }

    /**
     * Remove received
     *
     * @param MessagesInbox $received
     */
    public function removeReceived(MessagesInbox $received)
    {
        $this->received->removeElement($received);
    }

    /**
     * Get received
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * Add sent
     *
     * @param Message $sent
     * @return Inbox
     */
    public function addSent(Message $sent)
    {
        $this->sent[] = $sent;

        return $this;
    }

    /**
     * Remove sent
     *
     * @param Message $sent
     */
    public function removeSent(Message $sent)
    {
        $this->sent->removeElement($sent);
    }

    /**
     * Get sent
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSent()
    {
        return $this->sent;
    }
}
