<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Responsabilite
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\ResponsabiliteRepository")
 * @UniqueEntity("libelle")
 */
class Responsabilite
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
     * @ORM\Column(name="libelle", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * Niveau hiérarchique de la responsabilité, 0 étant le plus important.
     *
     * @var integer
     *
     * @ORM\Column(name="niveau", type="smallint")
     * @Assert\NotBlank()
     */
    private $niveau;

    /**
     * Si la responsabilité existe au tabagn'ss ou pas.
     *
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="Responsable", mappedBy="responsabilite")
     **/
    private $responsables;

    /**
     * @ORM\ManyToOne(targetEntity="Boquette", inversedBy="responsabilites")
     * @Assert\NotBlank()
     **/
    private $boquette;

    public function __construct() {
        $this->responsables = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Responsabilite
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
     * Set role
     *
     * @param string $role
     * @return Responsabilite
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set niveau
     *
     * @param integer $niveau
     * @return Responsabilite
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau
     *
     * @return integer
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Set boquette
     *
     * @param \PJM\AppBundle\Entity\Boquette $boquette
     * @return Responsabilite
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
     * Add responsables
     *
     * @param \PJM\AppBundle\Entity\Responsable $responsables
     * @return Responsabilite
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
     * Set active
     *
     * @param boolean $active
     * @return Responsabilite
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
}
