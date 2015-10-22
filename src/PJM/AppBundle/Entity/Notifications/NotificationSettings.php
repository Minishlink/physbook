<?php

namespace PJM\AppBundle\Entity\Notifications;

use Doctrine\ORM\Mapping as ORM;
use PJM\AppBundle\Enum\Notifications\NotificationSettingsEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NotificationSettings.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class NotificationSettings
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
     * @ORM\Column(name="subscriptions", type="simple_array", nullable=true)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\Notifications\NotificationSettingsEnum", "getSubscriptionsChoices"}, multiple=true)
     */
    private $subscriptions;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\User", inversedBy="notificationSettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="webhook", type="string", length=255, nullable=true)
     * @Assert\Url()
     */
    private $webhook;

    /**
     * @var boolean
     *
     * @ORM\Column(name="email", type="boolean")
     */
    private $email;

    public function __construct()
    {
        $enum = new NotificationSettingsEnum();
        $this->subscriptions = $enum->getDefaultSubscriptionsChoices();
        $this->email = false;

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
     * @return array
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param array $subscriptions
     *
     * @return NotificationSettings
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    /**
     * Has subscription by type.
     *
     * @return bool
     */
    public function has($type)
    {
        return in_array($type, $this->subscriptions);
    }

    /**
     * Set user.
     *
     * @param \PJM\AppBundle\Entity\User $user
     *
     * @return NotificationSettings
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
     * Set webhook.
     *
     * @param string $webhook
     *
     * @return NotificationSettings
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;

        return $this;
    }

    /**
     * Get webhook.
     *
     * @return string
     */
    public function getWebhook()
    {
        return $this->webhook;
    }

    /**
     * @return boolean
     */
    public function isEmail()
    {
        return $this->email;
    }

    /**
     * @param boolean $email
     * @return NotificationSettings
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }
}
