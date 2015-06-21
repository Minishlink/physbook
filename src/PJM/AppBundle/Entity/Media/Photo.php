<?php

namespace PJM\AppBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Photo.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Media\PhotoRepository")
 */
class Photo
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
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="legende", type="string", length=160, nullable=true)
     */
    private $legende;

    /**
     * @var int
     *
     * @ORM\Column(name="publication", type="smallint")
     * @Assert\NotBlank()
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\Media\PhotoEnum", "getPublicationChoices"},
     * message = "Choisissez un état de publication valide.")
     */
    private $publication;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\Image", cascade={"persist", "remove"})
     * @Assert\Valid()
     **/
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="PJM\AppBundle\Entity\UsersHM", cascade={"persist", "remove"})
     **/
    private $usersHM;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User", inversedBy="photosCreated")
     **/
    private $proprietaire;

    // FUTURE users apparaissants sur la photo

    public function __construct()
    {
        $this->legende = '';
        $this->date = new \DateTime();
        $this->usersHM = new \PJM\AppBundle\Entity\UsersHM();
        $this->publication = 0;
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
     * Set legende.
     *
     * @param string $legende
     *
     * @return Photo
     */
    public function setLegende($legende)
    {
        if ($legende === null) {
            $legende = '';
        }
        $this->legende = $legende;

        return $this;
    }

    /**
     * Get legende.
     *
     * @return string
     */
    public function getLegende()
    {
        return $this->legende;
    }

    /**
     * Set publication.
     *
     * @param int $publication
     *
     * @return Photo
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication.
     *
     * @return int
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Photo
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
     * Set image.
     *
     * @param \PJM\AppBundle\Entity\Image $image
     *
     * @return Photo
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
     * @return Photo
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

    /**
     * Get nbUsersHM.
     *
     * @return int
     */
    public function getNbUsersHM()
    {
        return count($this->usersHM->getUsers());
    }

    /**
     * Set proprietaire.
     *
     * @param \PJM\UserBundle\Entity\User $proprietaire
     *
     * @return Photo
     */
    public function setProprietaire(\PJM\UserBundle\Entity\User $proprietaire = null)
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    /**
     * Get proprietaire.
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getProprietaire()
    {
        return $this->proprietaire;
    }
}
