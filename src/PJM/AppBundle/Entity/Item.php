<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Item
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\ItemRepository")
 */
class Item
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
     * @var integer
     *
     * @ORM\Column(name="prix", type="smallint")
     */
    private $prix;

    /**
     * @var boolean
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
     * @ORM\ManyToMany(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinTable(name="items_usersHm",
     *      joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $usersHm;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->valid = true;
        $this->usersHm = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->libelle;
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
     * Set libelle
     *
     * @param string $libelle
     * @return Item
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set prix
     *
     * @param integer $prix
     * @return Item
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return integer
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set boquette
     *
     * @param \PJM\AppBundle\Entity\Boquette $boquette
     * @return Item
     */
    public function setBoquette(\PJM\AppBundle\Entity\Boquette $boquette)
    {
        $this->boquette = $boquette;

        return $this;
    }

    /**
     * Get boquette
     *
     * @return \PJM\AppBundle\Entity\Boquette
     */
    public function getBoquette()
    {
        return $this->boquette;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Item
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Item
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
     * Set valid
     *
     * @param boolean $valid
     * @return Item
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return boolean
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set infos
     *
     * @param array $infos
     * @return Item
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
     * Set image
     *
     * @param \PJM\AppBundle\Entity\Image $image
     * @return Item
     */
    public function setImage(\PJM\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \PJM\AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add usersHm
     *
     * @param \PJM\UserBundle\Entity\User $usersHm
     * @return Item
     */
    public function addUsersHm(\PJM\UserBundle\Entity\User $usersHm)
    {
        $this->usersHm[] = $usersHm;

        return $this;
    }

    /**
     * Remove usersHm
     *
     * @param \PJM\UserBundle\Entity\User $usersHm
     */
    public function removeUsersHm(\PJM\UserBundle\Entity\User $usersHm)
    {
        $this->usersHm->removeElement($usersHm);
    }

    /**
     * Get usersHm
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsersHm()
    {
        return $this->usersHm;
    }
}
