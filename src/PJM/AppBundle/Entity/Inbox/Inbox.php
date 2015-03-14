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
     * @ORM\OneToMany(targetEntity="Reception", mappedBy="inbox", cascade={"all"})
     * @ORM\OrderBy({"lu" = "asc", "id" = "desc"})
     **/
    private $receptions;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="expedition")
     * @ORM\OrderBy({"date" = "desc"})
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

    public function incrementNbNonLus($val = 1)
    {
        $this->nbNonLus += $val;

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
     * Get received
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReceived()
    {
        $received = new \Doctrine\Common\Collections\ArrayCollection();

        foreach($this->receptions as $reception)
        {
            $received[] = $reception->getMessage();
        }

        return $received;
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

    /**
     * Add receptions
     *
     * @param \PJM\AppBundle\Entity\Inbox\Reception $receptions
     * @return Inbox
     */
    public function addReception(\PJM\AppBundle\Entity\Inbox\Reception $receptions)
    {
        $this->receptions[] = $receptions;

        return $this;
    }

    /**
     * Remove receptions
     *
     * @param \PJM\AppBundle\Entity\Inbox\Reception $receptions
     */
    public function removeReception(\PJM\AppBundle\Entity\Inbox\Reception $receptions)
    {
        $this->receptions->removeElement($receptions);
    }

    /**
     * Get receptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReceptions()
    {
        return $this->receptions;
    }
}
