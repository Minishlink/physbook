<?php

namespace PJM\AppBundle\Entity\Inbox;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Length(
     *  min = 2,
     *  max = 1000,
     *  minMessage = "Ton message est trop court.",
     *  maxMessage = "Ton message est trop long ! Fais en plusieurs."
     * )
     */
    private $contenu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="Reception", mappedBy="message", cascade={"all"})
     **/
    private $receptions;

    private $destinations;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $destinataires;


    /**
     * @ORM\ManyToOne(targetEntity="Inbox", inversedBy="sent", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $expedition;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->receptions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set expedition
     *
     * @param Inbox $expedition
     * @return Message
     */
    public function setExpedition(Inbox $expedition)
    {
        $this->expedition = $expedition;

        return $this;
    }

    /**
     * Get expedition
     *
     * @return Inbox
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

    /**
     * Add receptions
     *
     * @param \PJM\AppBundle\Entity\Inbox\Reception $receptions
     * @return Message
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

    public function getDestinatairesUsers()
    {
        $destinataires = new \Doctrine\Common\Collections\ArrayCollection();

        foreach($this->receptions as $reception)
        {
            $destinataires[] = $reception->getInbox()->getUser();
        }

        return $destinataires;
    }

    public function getDestinatairesLus()
    {
        $lus = array();

        foreach($this->receptions as $reception)
        {
            if ($reception->getLu()) {
                $lus[] = $reception->getInbox()->getUser()->getUsername();
            }
        }

        return $lus;
    }

    public function getDestinations()
    {
        $destinations = new \Doctrine\Common\Collections\ArrayCollection();

        foreach($this->receptions as $reception)
        {
            $destinations[] = $reception->getInbox();
        }

        return $destinations;
    }

    public function setDestinations($destinations)
    {
        foreach($destinations as $destination)
        {
            $reception = new Reception();

            $reception->setMessage($this);
            $reception->setInbox($destination);

            $this->addReception($reception);
        }

        return $this;
    }

    /**
     * Set destinataires
     *
     * @param array $destinataires
     * @return Message
     */
    public function setDestinataires($destinataires)
    {
        $this->destinataires = $destinataires;

        return $this;
    }

    /**
     * Get destinataires
     *
     * @return array
     */
    public function getDestinataires()
    {
        return $this->destinataires;
    }
}
