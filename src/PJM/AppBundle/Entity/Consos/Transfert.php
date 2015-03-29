<?php

namespace PJM\AppBundle\Entity\Consos;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transfert
 * Transfert d'argent entre deux comptes de même nature (Pian's à Pian's)
 * à cause de contraintes de transferts bancaires nécessaires dans l'autre cas.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Transfert
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
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Compte", inversedBy="receptions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $receveur;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Compte", inversedBy="envois", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $emetteur;

    /**
     * @var string
     *
     * @ORM\Column(name="raison", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $raison;


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
     * Set montant
     *
     * @param integer $montant
     * @return Transfert
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return integer
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set raison
     *
     * @param string $raison
     * @return Transfert
     */
    public function setRaison($raison)
    {
        $this->raison = $raison;

        return $this;
    }

    /**
     * Get raison
     *
     * @return string
     */
    public function getRaison()
    {
        return $this->raison;
    }

    /**
     * Set receveur
     *
     * @param \PJM\AppBundle\Entity\Compte $receveur
     * @return Transfert
     */
    public function setReceveur(\PJM\AppBundle\Entity\Compte $receveur)
    {
        $this->receveur = $receveur;

        return $this;
    }

    /**
     * Get receveur
     *
     * @return \PJM\AppBundle\Entity\Compte
     */
    public function getReceveur()
    {
        return $this->receveur;
    }

    /**
     * Set emetteur
     *
     * @param \PJM\AppBundle\Entity\Compte $emetteur
     * @return Transfert
     */
    public function setEmetteur(\PJM\AppBundle\Entity\Compte $emetteur)
    {
        $this->emetteur = $emetteur;

        return $this;
    }

    /**
     * Get emetteur
     *
     * @return \PJM\AppBundle\Entity\Compte
     */
    public function getEmetteur()
    {
        return $this->emetteur;
    }
}
