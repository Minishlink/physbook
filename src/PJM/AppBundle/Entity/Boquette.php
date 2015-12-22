<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Boquette.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Boquette
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
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="caisseSMoney", type="string", length=255, nullable=true)
     */
    private $caisseSMoney;

    /**
     * @ORM\OneToMany(targetEntity="Responsabilite", mappedBy="boquette")
     **/
    private $responsabilites;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="couleur", type="string", length=10, nullable=true)
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\CouleursEnum", "getCouleursChoices"}, message = "Choisissez une couleur valide.")
     */
    private $couleur;

    /**
     * @var array
     *
     * @ORM\Column(name="lieux", type="array", nullable=true)
     */
    private $lieux;

    public function __construct()
    {
        $this->responsabilites = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->nom;
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
     * Set nom.
     *
     * @param string $nom
     *
     * @return Boquette
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Get nom au format court.
     *
     * @return string
     */
    public function getNomCourt()
    {
        return strtok($this->nom, ' ');
    }

    /**
     * Set caisseSMoney.
     *
     * @param string $caisseSMoney
     *
     * @return Boquette
     */
    public function setCaisseSMoney($caisseSMoney)
    {
        $this->caisseSMoney = $caisseSMoney;

        return $this;
    }

    /**
     * Get caisseSMoney.
     *
     * @return string
     */
    public function getCaisseSMoney()
    {
        return $this->caisseSMoney;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Boquette
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
     * Add responsabilites.
     *
     * @param \PJM\AppBundle\Entity\Responsabilite $responsabilites
     *
     * @return Boquette
     */
    public function addResponsabilite(\PJM\AppBundle\Entity\Responsabilite $responsabilites)
    {
        $this->responsabilites[] = $responsabilites;

        return $this;
    }

    /**
     * Remove responsabilites.
     *
     * @param \PJM\AppBundle\Entity\Responsabilite $responsabilites
     */
    public function removeResponsabilite(\PJM\AppBundle\Entity\Responsabilite $responsabilites)
    {
        $this->responsabilites->removeElement($responsabilites);
    }

    /**
     * Get responsabilites.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResponsabilites()
    {
        return $this->responsabilites;
    }

    /**
     * Set image.
     *
     * @param \PJM\AppBundle\Entity\Image $image
     *
     * @return Boquette
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
     * Set couleur.
     *
     * @param string $couleur
     *
     * @return Boquette
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * Get couleur.
     *
     * @return string
     */
    public function getCouleur()
    {
        return $this->couleur;
    }

    /**
     * @return array
     */
    public function getLieux()
    {
        return $this->lieux;
    }

    /**
     * @param array $lieux
     *
     * @return Boquette
     */
    public function setLieux($lieux)
    {
        $this->lieux = $lieux;

        return $this;
    }
}
