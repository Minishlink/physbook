<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReglagesNotifications.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ReglagesNotifications
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
     * @var array
     *
     * @ORM\Column(name="actus", type="simple_array", nullable=true)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\ReglagesNotificationsEnum", "getActusChoices"}, multiple=true)
     */
    private $actus;

    /**
     * @var bool
     *
     * @ORM\Column(name="messages", type="boolean")
     */
    private $messages;

    /**
     * @var bool
     *
     * @ORM\Column(name="events", type="boolean")
     */
    private $events;

    /**
     * @var array
     *
     * @ORM\Column(name="banque", type="simple_array", nullable=true)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\ReglagesNotificationsEnum", "getBanqueChoices"}, multiple=true)
     */
    private $banque;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\User", inversedBy="reglagesNotifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $enum = new \PJM\AppBundle\Enum\ReglagesNotificationsEnum();
        $this->actus = $enum->getDefaultActusChoices();
        $this->banque = $enum->getDefaultBanqueChoices();
        $this->messages = true;
        $this->events = true;

        return $this;
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
     * Set actus.
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
     * Get actus.
     *
     * @return array
     */
    public function getActus()
    {
        return $this->actus;
    }

    /**
     * Set messages.
     *
     * @param bool $messages
     *
     * @return ReglagesNotifications
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get messages.
     *
     * @return bool
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Has setting by type.
     *
     * @return bool
     */
    public function has($type)
    {
        return (
            ($type == 'message' && $this->messages) ||
            ($type == 'events' && $this->events) ||
            in_array($type, $this->actus) ||
            in_array($type, $this->banque));
    }

    /**
     * Set banque.
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
     * Get banque.
     *
     * @return array
     */
    public function getBanque()
    {
        return $this->banque;
    }

    /**
     * Set user.
     *
     * @param \PJM\AppBundle\Entity\User $user
     *
     * @return ReglagesNotifications
     */
    public function setUser(\PJM\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \PJM\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set events.
     *
     * @param bool $events
     *
     * @return ReglagesNotifications
     */
    public function setEvents($events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get events.
     *
     * @return bool
     */
    public function getEvents()
    {
        return $this->events;
    }
}
