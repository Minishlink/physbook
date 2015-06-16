<?php

namespace PJM\AppBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

use PJM\AppBundle\Validator\Constraints as PJMAssert;

/**
 * Evenement
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Event\EvenementRepository")
 * @PJMAssert\DateDebutFin
 */
class Evenement
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
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\NotBlank
     */
    private $nom;

    /**
    * @Gedmo\Slug(fields={"nom"}, updatable=false)
    * @ORM\Column(length=128, unique=true)
    */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isJournee", type="boolean")
     */
    private $isJournee;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     * @Assert\DateTime()
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime")
     * @Assert\DateTime()
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime")
     * @Assert\DateTime()
     */
    private $dateFin;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu", type="string", length=255, nullable=true)
     */
    private $lieu;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\Image", cascade={"persist", "remove"})
     * @Assert\Valid()
     **/
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\Item", cascade={"persist", "remove"})
     * @Assert\Valid()
     **/
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Aucun créateur spécifié.")
     **/
    private $createur;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Boquette")
     * @ORM\JoinColumn(nullable=true)
     **/
    private $boquette;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPublic", type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Event\Invitation", mappedBy="event", cascade={"remove"})
     **/
    private $invitations;

    /**
     * @var integer
     *
     * @ORM\Column(name="prix", type="smallint"))
     * @Assert\GreaterThanOrEqual(
     *      value = 0,
     *      message = "Le prix doit être supérieur ou égal à 0€."
     * )
     */
    private $prix;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateDebut = new \DateTime();
        $this->dateDebut->setTime($this->dateCreation->format('H'),0);
        $this->dateFin = new \DateTime();
        $this->dateFin->setTime($this->dateCreation->format('H')+1,'0');
        $this->isJournee = false;
        $this->isPublic = true;
        $this->prix = 0;
        $this->invitations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nom
     *
     * @param string $nom
     * @return Evenement
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
     * Set slug
     *
     * @param string $slug
     * @return Evenement
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
     * Set description
     *
     * @param string $description
     * @return Evenement
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isJournee
     *
     * @param boolean $isJournee
     * @return Evenement
     */
    public function setJournee($isJournee)
    {
        $this->isJournee = $isJournee;

        return $this;
    }

    /**
     * Get isJournee
     *
     * @return boolean
     */
    public function isJournee()
    {
        return $this->isJournee;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return Evenement
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return Evenement
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set lieu
     *
     * @param string $lieu
     * @return Evenement
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu
     *
     * @return string
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Evenement
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     *
     * @return Evenement
     */
    public function setPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set image
     *
     * @param \PJM\AppBundle\Entity\Image $image
     *
     * @return Evenement
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
     * Set createur
     *
     * @param \PJM\UserBundle\Entity\User $createur
     *
     * @return Evenement
     */
    public function setCreateur(\PJM\UserBundle\Entity\User $createur)
    {
        $this->createur = $createur;

        return $this;
    }

    /**
     * Get createur
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getCreateur()
    {
        return $this->createur;
    }

    /**
     * Set boquette
     *
     * @param \PJM\AppBundle\Entity\Boquette $boquette
     *
     * @return Evenement
     */
    public function setBoquette(\PJM\AppBundle\Entity\Boquette $boquette = null)
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
     * Get couleur
     *
     * @return string
     */
    public function getCouleur()
    {
        if ($this->boquette !== null) {
            $couleur = $this->boquette->getCouleur();
        }

        return isset($couleur) ? $couleur : "rouge";
    }

    /**
     * Set prix
     *
     * @param integer $prix
     *
     * @return Evenement
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
     * Add invitation
     *
     * @param \PJM\AppBundle\Entity\Event\Invitation $invitation
     *
     * @return Evenement
     */
    public function addInvitation(\PJM\AppBundle\Entity\Event\Invitation $invitation)
    {
        $this->invitations[] = $invitation;

        return $this;
    }

    /**
     * Remove invitation
     *
     * @param \PJM\AppBundle\Entity\Event\Invitation $invitation
     */
    public function removeInvitation(\PJM\AppBundle\Entity\Event\Invitation $invitation)
    {
        $this->invitations->removeElement($invitation);
    }

    /**
     * Get invitations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvitations()
    {
        return $this->invitations;
    }

    public function getInvites($statut = null, $tous = false) {
        $invites = array();

        foreach ($this->invitations as $invitation) {
            if ($invitation->getEstPresent() === $statut || $tous) {
                $invites[] = $invitation->getInvite();
            }
        }

        return $invites;
    }

    public function getParticipants() {
        return $this->getInvites(true);
    }

    public function getNonParticipants() {
        return $this->getInvites(false);
    }

    public function canBeSeenByUser(\PJM\UserBundle\Entity\User $user) {
        // FUTURE visibilité conscrits/anciens/P3/archis

        if (!$this->isPublic()) {
            // on vérifie que l'utilisateur est invité
            if (!in_array($user, $this->getInvites(null, true))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set item
     *
     * @param \PJM\AppBundle\Entity\Item $item
     *
     * @return Evenement
     */
    public function setItem(\PJM\AppBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \PJM\AppBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }
}
