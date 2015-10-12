<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Item.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\ItemRepository")
 */
class Item
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
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var int
     *
     * @ORM\Column(name="prix", type="integer")
     */
    private $prix;

    /**
     * @var bool
     *
     * @ORM\Column(name="valid", type="boolean")
     */
    private $valid;

    /**
     * @var \DateTime
     *
     * Sert à avoir un historique des prix d'un item
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var array
     *
     * @ORM\Column(name="infos", type="json_array", nullable=true)
     */
    private $infos;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Boquette")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boquette;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\UsersHM", cascade={"persist", "remove"})
     **/
    private $usersHM;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->valid = true;
        $this->usersHM = new UsersHM();
    }

    public function __toString()
    {
        return $this->libelle;
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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Item
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set prix.
     *
     * @param int $prix
     *
     * @return Item
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix.
     *
     * @return int cents
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Show prix.
     *
     * @return int euros
     */
    public function showPrix()
    {
        return $this->prix/100;
    }

    /**
     * Set boquette.
     *
     * @param \PJM\AppBundle\Entity\Boquette $boquette
     *
     * @return Item
     */
    public function setBoquette(\PJM\AppBundle\Entity\Boquette $boquette)
    {
        $this->boquette = $boquette;

        return $this;
    }

    /**
     * Get boquette.
     *
     * @return \PJM\AppBundle\Entity\Boquette
     */
    public function getBoquette()
    {
        return $this->boquette;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Item
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Item
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
     * Set valid.
     *
     * @param bool $valid
     *
     * @return Item
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

    /**
     * Set infos.
     *
     * @param array $infos
     *
     * @return Item
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos.
     *
     * @return array
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Set image.
     *
     * @param \PJM\AppBundle\Entity\Image $image
     *
     * @return Item
     */
    public function setImage(\PJM\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return \PJM\AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set usersHM.
     *
     * @param \PJM\AppBundle\Entity\UsersHM $usersHM
     *
     * @return Item
     */
    public function setUsersHM(\PJM\AppBundle\Entity\UsersHM $usersHM = null)
    {
        $this->usersHM = $usersHM;

        return $this;
    }

    /**
     * Get usersHM.
     *
     * @return \PJM\AppBundle\Entity\UsersHM
     */
    public function getUsersHM()
    {
        return $this->usersHM;
    }
}
