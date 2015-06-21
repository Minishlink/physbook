<?php

namespace PJM\AppBundle\Entity\Inbox;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reception.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Inbox\ReceptionRepository")
 */
class Reception
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
     * @var bool
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lu.
     *
     * @param bool $lu
     *
     * @return MessageInbox
     */
    public function setLu($lu)
    {
        if ($this->lu != $lu) {
            $this->lu = $lu;

            if ($this->lu) {
                $this->inbox->incrementNbNonLus(-1);
            }
        }

        return $this;
    }

    /**
     * Get lu.
     *
     * @return bool
     */
    public function getLu()
    {
        return $this->lu;
    }

    /**
     * Set inbox.
     *
     * @param \PJM\AppBundle\Entity\Inbox\Inbox $inbox
     *
     * @return MessagesInbox
     */
    public function setInbox(\PJM\AppBundle\Entity\Inbox\Inbox $inbox)
    {
        $this->inbox = $inbox;

        if (!$this->lu) {
            $this->inbox->incrementNbNonLus();
        }

        return $this;
    }

    /**
     * Get inbox.
     *
     * @return \PJM\AppBundle\Entity\Inbox\Inbox
     */
    public function getInbox()
    {
        return $this->inbox;
    }

    /**
     * Set message.
     *
     * @param \PJM\AppBundle\Entity\Inbox\Message $message
     *
     * @return MessagesInbox
     */
    public function setMessage(\PJM\AppBundle\Entity\Inbox\Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return \PJM\AppBundle\Entity\Inbox\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set variables.
     *
     * @param array $variables
     *
     * @return Reception
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Get variables.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
