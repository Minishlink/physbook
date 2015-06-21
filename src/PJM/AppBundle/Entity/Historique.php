<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Historique.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\HistoriqueRepository")
 */
class Historique
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
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Item")
     * @ORM\JoinColumn(nullable=false)
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(name="valid", type="boolean", nullable=true)
     */
    private $valid;

    /**
     * @var int
     *          multiplié par 10
     * @ORM\Column(name="nombre", type="smallint")
     */
    private $nombre;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->setNombre(10);
    }

    public function setCommande(Commande $commande)
    {
        $this->item = $commande->getItem();
        $this->user = $commande->getUser();
        $this->nombre = $commande->getNombre();
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Historique
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set nombre.
     *
     * @param int $nombre
     *
     * @return Historique
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre.
     *
     * @return int
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Get prix (cents).
     *
     * @return int
     */
    public function getPrix()
    {
        return $this->getNombre() * $this->getItem()->getPrix() / 10;
    }

    /**
     * Set item.
     *
     * @param \PJM\AppBundle\Entity\Item $item
     *
     * @return Historique
     */
    public function setItem(\PJM\AppBundle\Entity\Item $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item.
     *
     * @return \PJM\AppBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set user.
     *
     * @param \PJM\UserBundle\Entity\User $user
     *
     * @return Historique
     */
    public function setUser(\PJM\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set valid.
     *
     * @param bool $valid
     *
     * @return Historique
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid.
     *
     * @return bool
     */
    public function getValid()
    {
        return $this->valid;
    }
}
