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
     * @ORM\ManyToMany(targetEntity="PJM\AppBundle\Entity\Inbox", mappedBy="received")
     **/
    private $destinataires;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Inbox", inversedBy="sent")
     * @ORM\JoinColumn(nullable=false)
     **/
    private $expediteur;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinataires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date = new \DateTime();
    }

    public function __toString()
    {
        return $this->getUser();
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
     * Add destinataires
     *
     * @param \PJM\AppBundle\Entity\Inbox $destinataires
     * @return Message
     */
    public function addDestinataire(\PJM\AppBundle\Entity\Inbox $destinataires)
    {
        $this->destinataires[] = $destinataires;

        return $this;
    }

    /**
     * Remove destinataires
     *
     * @param \PJM\AppBundle\Entity\Inbox $destinataires
     */
    public function removeDestinataire(\PJM\AppBundle\Entity\Inbox $destinataires)
    {
        $this->destinataires->removeElement($destinataires);
    }

    /**
     * Get destinataires
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinataires()
    {
        return $this->destinataires;
    }

    /**
     * Set expediteur
     *
     * @param \PJM\AppBundle\Entity\Inbox $expediteur
     * @return Message
     */
    public function setExpediteur(\PJM\AppBundle\Entity\Inbox $expediteur = null)
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    /**
     * Get expediteur
     *
     * @return \PJM\AppBundle\Entity\Inbox
     */
    public function getExpediteur()
    {
        return $this->expediteur;
    }

     /**
     * Get user
     *
     * @return \PJM\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->expediteur->getUser();
    }
}
