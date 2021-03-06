<?php

namespace PJM\AppBundle\Entity\Actus;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PJM\AppBundle\Entity\UsersHM;

/**
 * Article.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Actus\ArticleRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Article
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEdition", type="datetime", nullable=true)
     */
    private $dateEdition;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $titre;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="text")
     * @Assert\NotBlank()
     */
    private $contenu;

    /**
     * @var bool
     *
     * @ORM\Column(name="publication", type="boolean")
     */
    private $publication;

    /**
     * @ORM\ManyToMany(targetEntity="PJM\AppBundle\Entity\Actus\Categorie", cascade={"persist"})
     * @Assert\Valid()
     */
    private $categories;

    /**
     * @Gedmo\Slug(fields={"titre"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\UsersHM", cascade={"persist", "remove"})
     **/
    private $usersHM;

    public function __construct()
    {
        $this->date = new \Datetime();
        $this->publication = false;
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commentaires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersHM = new UsersHM();
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setDateEdition(new \Datetime());
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
     * @return Article
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
     * Set titre.
     *
     * @param string $titre
     *
     * @return Article
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set auteur.
     *
     * @param \PJM\AppBundle\Entity\User $auteur
     *
     * @return Article
     */
    public function setAuteur(\PJM\AppBundle\Entity\User $auteur)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get auteur.
     *
     * @return \PJM\AppBundle\Entity\User
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set contenu.
     *
     * @param string $contenu
     *
     * @return Article
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu.
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set publication.
     *
     * @param bool $publication
     *
     * @return Article
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication.
     *
     * @return bool
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Add categories.
     *
     * @param \PJM\AppBundle\Entity\Actus\Categorie $categories
     *
     * @return Article
     */
    public function addCategory(\PJM\AppBundle\Entity\Actus\Categorie $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories.
     *
     * @param \PJM\AppBundle\Entity\Actus\Categorie $categories
     */
    public function removeCategory(\PJM\AppBundle\Entity\Actus\Categorie $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set dateEdition.
     *
     * @param \DateTime $dateEdition
     *
     * @return Article
     */
    public function setDateEdition($dateEdition)
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * Get dateEdition.
     *
     * @return \DateTime
     */
    public function getDateEdition()
    {
        return $this->dateEdition;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Article
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
     * Get thread id (commentaires).
     *
     * @return string
     */
    public function getThreadId()
    {
        return 'article_'.$this->id;
    }

    /**
     * Set usersHM.
     *
     * @param \PJM\AppBundle\Entity\UsersHM $usersHM
     *
     * @return Article
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
