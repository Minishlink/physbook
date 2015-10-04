<?php

namespace PJM\AppBundle\Entity\Notifications;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class Notification
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
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=255)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\NotificationEnum", "getKeys"})
     */
    private $key;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var array
     *
     * @ORM\Column(name="infos", type="array")
     */
    private $infos;

    /**
     * @var boolean
     *
     * @ORM\Column(name="received", type="boolean")
     */
    private $received;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $path;

    /**
     * @var boolean
     */
    private $important;


    public function __construct()
    {
        $this->date = new \DateTime();
        $this->received = false;
        $this->important = false;
        $this->path = "";
        $this->type = "";
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
     * Set key
     *
     * @param string $key
     *
     * @return Notification
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set infos
     *
     * @param array $infos
     *
     * @return Notification
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos
     *
     * @return array
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Set received
     *
     * @param boolean $received
     *
     * @return Notification
     */
    public function setReceived($received)
    {
        $this->received = $received;

        return $this;
    }

    /**
     * Get received
     *
     * @return boolean
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Notification
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
     * Set user
     *
     * @param \PJM\AppBundle\Entity\User $user
     *
     * @return Notification
     */
    public function setUser(\PJM\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PJM\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return boolean
     */
    public function isImportant()
    {
        return $this->important;
    }

    /**
     * @param boolean $important
     */
    public function setImportant($important)
    {
        $this->important = $important;
    }
}
