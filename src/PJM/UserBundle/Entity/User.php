<?php

namespace PJM\UserBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="pjm_user")
 * @ORM\Entity(repositoryClass="PJM\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime
     *
     * @ORM\Column(name="lastActivity", type="datetime", nullable=true)
     */
    private $lastActivity;

    /**
     * @var string
     *
     * @ORM\Column(name="fams", type="string", length=20, nullable=true)
     * @Assert\NotBlank()
     */
    private $fams;

    /**
     * @var string
     *
     * @ORM\Column(name="tabagns", type="string", length=5, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"bo", "li", "an", "me", "ch", "cl", "ai", "ka", "pa"})
     */
    private $tabagns;

    /**
     * @var integer
     *
     * @ORM\Column(name="proms", type="smallint", length=5, nullable=true)
     * @Assert\NotBlank()
     */
    private $proms;

    /**
     * @var string
     *
     * @ORM\Column(name="bucque", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $bucque;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @var string
     *
     * @ORM\Column(name="appartement", type="string", length=10, nullable=true)
     */
    private $appartement;

    /**
     * @var string
     *
     * @ORM\Column(name="classe", type="string", length=10, nullable=true)
     */
    private $classe;

    /**
     * @var date
     *
     * @ORM\Column(name="anniversaire", type="date", nullable=true)
     * @Assert\Date()
     */
    private $anniversaire;

    /**
     * @var boolean
     * 1 si féminin
     *
     * @ORM\Column(name="genre", type="boolean")
     */
    private $genre;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Responsable", mappedBy="user")
     **/
    private $responsables;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\Inbox\Inbox", inversedBy="user", cascade={"persist", "remove"})
     **/
    private $inbox;

    /**
     * Photo de profil
     *
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\Media\Photo", cascade={"persist", "remove"})
     **/
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Media\Photo", mappedBy="proprietaire", cascade={"persist", "remove"})
     **/
    private $photosCreated;

    public function __toString()
    {
        $user = $this->username;
        if (!empty($this->bucque)) {
            $user = $this->bucque." ".$user;
        }

        if (!empty($this->prenom) || !empty($this->nom)) {
            $user .= " (";
            if (!empty($this->prenom)) {
                $user .= $this->prenom;
            }
            if (!empty($this->nom)) {
                if (!empty($this->prenom)) {
                    $user .= " ";
                }

                $user .= $this->nom;
            }
            $user .= ")";
        }

        return $user;
    }

    public function __construct() {
        parent::__construct();

        $this->responsables = new \Doctrine\Common\Collections\ArrayCollection();
        $this->photosCreated = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fams
     *
     * @param string $fams
     * @return User
     */
    public function setFams($fams)
    {
        $this->fams = $fams;

        return $this;
    }

    /**
     * Get fams
     *
     * @return string
     */
    public function getFams()
    {
        return $this->fams;
    }

    /**
     * Set tabagns
     *
     * @param string $tabagns
     * @return User
     */
    public function setTabagns($tabagns)
    {
        $this->tabagns = $tabagns;

        return $this;
    }

    /**
     * Get tabagns
     *
     * @return string
     */
    public function getTabagns()
    {
        return $this->tabagns;
    }

    /**
     * Set proms
     *
     * @param integer $proms
     * @return User
     */
    public function setProms($proms)
    {
        $this->proms = $proms;

        return $this;
    }

    /**
     * Get proms
     *
     * @return integer
     */
    public function getProms()
    {
        return $this->proms;
    }

    /**
     * Set bucque
     *
     * @param string $bucque
     * @return User
     */
    public function setBucque($bucque)
    {
        $this->bucque = $bucque;

        return $this;
    }

    /**
     * Get bucque
     *
     * @return string
     */
    public function getBucque()
    {
        return $this->bucque;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return User
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return User
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return User
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set appartement
     *
     * @param string $appartement
     * @return User
     */
    public function setAppartement($appartement)
    {
        $this->appartement = $appartement;

        return $this;
    }

    /**
     * Get appartement
     *
     * @return string
     */
    public function getAppartement()
    {
        return $this->appartement;
    }

    /**
     * Set classe
     *
     * @param string $classe
     * @return User
     */
    public function setClasse($classe)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return string
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set anniversaire
     *
     * @param string $anniversaire
     * @return User
     */
    public function setAnniversaire($anniversaire)
    {
        if (gettype($anniversaire) == "string") {
            $this->anniversaire = \DateTime::createFromFormat('d/m/Y', $anniversaire);
        } else {
            $this->anniversaire = $anniversaire;
        }

        return $this;
    }

    /**
     * Get anniversaire
     *
     * @return \DateTime
     */
    public function getAnniversaire()
    {
        return $this->anniversaire;
    }

    /**
     * Set lastActivity
     *
     * @param \DateTime $lastActivity
     * @return User
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    /**
     * Get lastActivity
     *
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * Add responsables
     *
     * @param \PJM\AppBundle\Entity\Responsable $responsables
     * @return User
     */
    public function addResponsable(\PJM\AppBundle\Entity\Responsable $responsables)
    {
        $this->responsables[] = $responsables;

        return $this;
    }

    /**
     * Remove responsables
     *
     * @param \PJM\AppBundle\Entity\Responsable $responsables
     */
    public function removeResponsable(\PJM\AppBundle\Entity\Responsable $responsables)
    {
        $this->responsables->removeElement($responsables);
    }

    /**
     * Get responsables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResponsables()
    {
        return $this->responsables;
    }

    /**
     * Set inbox
     *
     * @param \PJM\AppBundle\Entity\Inbox\Inbox $inbox
     * @return User
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
     * Set photo
     *
     * @param \PJM\AppBundle\Entity\Media\Photo $photo
     * @return User
     */
    public function setPhoto(\PJM\AppBundle\Entity\Media\Photo $photo = null)
    {
        $this->photo = $photo;

        if ($photo !== null) {
            $this->addPhotosCreated($photo);
        }

        return $this;
    }

    /**
     * Get photo
     *
     * @return \PJM\AppBundle\Entity\Media\Photo
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set genre
     *
     * @param boolean $genre
     * @return User
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return boolean
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Add photosCreated
     *
     * @param \PJM\AppBundle\Entity\Media\Photo $photosCreated
     * @return User
     */
    public function addPhotosCreated(\PJM\AppBundle\Entity\Media\Photo $photosCreated)
    {
        $this->photosCreated[] = $photosCreated;

        return $this;
    }

    /**
     * Remove photosCreated
     *
     * @param \PJM\AppBundle\Entity\Media\Photo $photosCreated
     */
    public function removePhotosCreated(\PJM\AppBundle\Entity\Media\Photo $photosCreated)
    {
        $this->photosCreated->removeElement($photosCreated);
    }

    /**
     * Get photosCreated
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotosCreated()
    {
        return $this->photosCreated;
    }
}
