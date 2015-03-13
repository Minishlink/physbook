<?php

namespace PJM\AppBundle\Entity\Inbox;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessagesInbox
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MessagesInbox
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
     * @ORM\Column(name="lu", type="boolean")
     */
    private $lu;

    /**
     * @ORM\ManyToOne(targetEntity="Inbox", inversedBy="received")
     * @ORM\JoinColumn(nullable=false)
     **/
    private $inbox;

    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="destinations")
     * @ORM\JoinColumn(nullable=false)
     **/
    private $message;


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
     * Set lu
     *
     * @param boolean $lu
     * @return MessageInbox
     */
    public function setLu($lu)
    {
        $this->lu = $lu;

        return $this;
    }

    /**
     * Get lu
     *
     * @return boolean
     */
    public function getLu()
    {
        return $this->lu;
    }

    /**
     * Set inbox
     *
     * @param \PJM\AppBundle\Entity\Inbox\Inbox $inbox
     * @return MessagesInbox
     */
    public function setInbox(\PJM\AppBundle\Entity\Inbox\Inbox $inbox)
    {
        $this->inbox = $inbox;

        return $this;
    }

    /**
     * Get inbox
     *
     * @return \PJM\AppBundle\Entity\Inbox\Inbox
     */
    public function getInbox()
    {
        return $this->inbox;
    }

    /**
     * Set message
     *
     * @param \PJM\AppBundle\Entity\Inbox\Message $message
     * @return MessagesInbox
     */
    public function setMessage(\PJM\AppBundle\Entity\Inbox\Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \PJM\AppBundle\Entity\Inbox\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
