<?php

namespace PJM\AppBundle\Entity\Inbox;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reception
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Reception
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
     * @ORM\ManyToOne(targetEntity="Inbox", inversedBy="receptions")
     * @ORM\JoinColumn(nullable=false)
     **/
    private $inbox;

    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="receptions")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\OrderBy({"date" = "desc"})
     **/
    private $message;

    /**
     * @var array
     *
     * @ORM\Column(name="variables", type="json_array", nullable=true)
     */
    private $variables;

    public function __construct()
    {
        $this->lu = false;
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

    /**
     * Set variables
     *
     * @param array $variables
     * @return Reception
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Get variables
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
