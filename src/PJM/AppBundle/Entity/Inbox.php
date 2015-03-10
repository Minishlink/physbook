<?php

namespace PJM\AppBundle\Entity;

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
     * @ORM\ManyToMany(targetEntity="PJM\AppBundle\Entity\Message", inversedBy="destinataires")
     **/
    private $received;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Message", mappedBy="expediteur")
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
     * @param \PJM\AppBundle\Entity\Message $received
     * @return Inbox
     */
    public function addReceived(\PJM\AppBundle\Entity\Message $received)
    {
        $this->received[] = $received;

        return $this;
    }

    /**
     * Remove received
     *
     * @param \PJM\AppBundle\Entity\Message $received
     */
    public function removeReceived(\PJM\AppBundle\Entity\Message $received)
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
     * @param \PJM\AppBundle\Entity\Message $sent
     * @return Inbox
     */
    public function addSent(\PJM\AppBundle\Entity\Message $sent)
    {
        $this->sent[] = $sent;

        return $this;
    }

    /**
     * Remove sent
     *
     * @param \PJM\AppBundle\Entity\Message $sent
     */
    public function removeSent(\PJM\AppBundle\Entity\Message $sent)
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
