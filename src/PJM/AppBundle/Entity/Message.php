<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Message
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
     * @ORM\Column(name="contenu", type="text")
     */
    private $contenu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var array
     *
     * @ORM\Column(name="variables", type="json_array", nullable=true)
     */
    private $variables;

    /**
     * @ORM\ManyToMany(targetEntity="PJM\AppBundle\Entity\Inbox", inversedBy="received", cascade={"persist"})
     **/
    private $destinations;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Inbox", inversedBy="sent", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $expedition;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date = new \DateTime();
    }

    public function __toString()
    {
        return $this->contenu;
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
     * Set contenu
     *
     * @param string $contenu
     * @return Message
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Message
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
     * Set variables
     *
     * @param array $variables
     * @return Message
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

    /**
     * Add destination
     *
     * @param \PJM\AppBundle\Entity\Inbox $destination
     * @return Message
     */
    public function addDestination(\PJM\AppBundle\Entity\Inbox $destination)
    {
        $destination->addReceived($this);
        $this->destinations[] = $destination;

        return $this;
    }

    /**
     * Remove destinations
     *
     * @param \PJM\AppBundle\Entity\Inbox $destinations
     */
    public function removeDestination(\PJM\AppBundle\Entity\Inbox $destinations)
    {
        $destination->removeReceived($this);
        $this->destinations->removeElement($destinations);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * Set expedition
     *
     * @param \PJM\AppBundle\Entity\Inbox $expedition
     * @return Message
     */
    public function setExpedition(\PJM\AppBundle\Entity\Inbox $expedition)
    {
        $this->expedition = $expedition;

        return $this;
    }

    /**
     * Get expedition
     *
     * @return \PJM\AppBundle\Entity\Inbox
     */
    public function getExpedition()
    {
        return $this->expedition;
    }

    /**
     * Get expediteur
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getExpediteur()
    {
        return $this->expedition->getUser();
    }
}
