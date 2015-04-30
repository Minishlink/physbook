<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReglagesNotifications
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ReglagesNotifications
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
     * @var array
     *
     * @ORM\Column(name="actus", type="simple_array", nullable=true)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\ReglagesNotificationsEnum", "getActusChoices"}, multiple=true)
     */
    private $actus;

    /**
     * @var boolean
     *
     * @ORM\Column(name="messages", type="boolean")
     */
    private $messages;

    /**
     * @var array
     *
     * @ORM\Column(name="banque", type="simple_array", nullable=true)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\ReglagesNotificationsEnum", "getBanqueChoices"}, multiple=true)
     */
    private $banque;

    /**
     * @ORM\OneToOne(targetEntity="PJM\UserBundle\Entity\User", inversedBy="reglagesNotifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $enum = new \PJM\AppBundle\Enum\ReglagesNotificationsEnum();
        $this->actus = $enum->getActusChoices();
        $this->banque = $enum->getBanqueChoices();
        $this->messages = true;

        return $this;
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
     * Set actus
     *
     * @param array $actus
     *
     * @return ReglagesNotifications
     */
    public function setActus($actus)
    {
        $this->actus = $actus;

        return $this;
    }

    /**
     * Get actus
     *
     * @return array
     */
    public function getActus()
    {
        return $this->actus;
    }

    /**
     * Set messages
     *
     * @param boolean $messages
     *
     * @return ReglagesNotifications
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get messages
     *
     * @return boolean
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Has setting by type
     *
     * @return boolean
     */
    public function has($type)
    {
        return (($type == 'message' && $this->messages) ||
            in_array($type, $this->actus) ||
            in_array($type, $this->banque));
    }

    /**
     * Set banque
     *
     * @param array $banque
     *
     * @return ReglagesNotifications
     */
    public function setBanque($banque)
    {
        $this->banque = $banque;

        return $this;
    }

    /**
     * Get banque
     *
     * @return array
     */
    public function getBanque()
    {
        return $this->banque;
    }

    /**
     * Set user
     *
     * @param \PJM\UserBundle\Entity\User $user
     *
     * @return ReglagesNotifications
     */
    public function setUser(\PJM\UserBundle\Entity\User $user)
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
