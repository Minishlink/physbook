<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PushSubscription.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\PushSubscriptionRepository")
 */
class PushSubscription
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
     * @var string
     *
     * @ORM\Column(name="subscriptionId", type="string", length=255)
     */
    private $subscriptionId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastSubscribed", type="datetime")
     */
    private $lastSubscribed;

    /**
     * @var string
     *
     * @ORM\Column(name="endpoint", type="string", length=255)
     */
    private $endpoint;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\User", inversedBy="pushSubscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="browserUA", type="string", length=255)
     */
    private $browserUA;

    public function __construct()
    {
        $this->lastSubscribed = new \DateTime();
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
     * Set subscriptionId.
     *
     * @param string $subscriptionId
     *
     * @return PushSubscription
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * Get subscriptionId.
     *
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Set lastSubscribed.
     *
     * @param \DateTime $lastSubscribed
     *
     * @return PushSubscription
     */
    public function setLastSubscribed($lastSubscribed)
    {
        $this->lastSubscribed = $lastSubscribed;

        return $this;
    }

    /**
     * Get lastSubscribed.
     *
     * @return \DateTime
     */
    public function getLastSubscribed()
    {
        return $this->lastSubscribed;
    }

    /**
     * Refresh lastSubscribed.
     *
     * @return PushSubscription
     */
    public function refreshLastSubscribed()
    {
        $this->lastSubscribed = new \DateTime();

        return $this;
    }

    /**
     * Set endpoint.
     *
     * @param string $endpoint
     *
     * @return PushSubscription
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set user.
     *
     * @param \PJM\AppBundle\Entity\User $user
     *
     * @return PushSubscription
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
     * Set browserUA.
     *
     * @param string $browserUA
     *
     * @return PushSubscription
     */
    public function setBrowserUA($browserUA)
    {
        $this->browserUA = $browserUA;

        return $this;
    }

    /**
     * Get browserUA.
     *
     * @return string
     */
    public function getBrowserUA()
    {
        return $this->browserUA;
    }
}
