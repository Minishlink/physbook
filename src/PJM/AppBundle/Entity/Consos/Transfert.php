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
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\Consos\TransfertRepository")
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 20000,
     *      minMessage = "Le montant minimum est de 1 centime.",
     *      maxMessage = "Le montant maximum est de 200€."
     * )
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Compte", inversedBy="receptions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $receveur;

    private $receveurUser;

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
     * @var string
     * "OK" : paiement validé et enregistré
     * "NOK" : paiement non validé
     * {chaine} : erreur
     * null : paiement non complété
     *
     * @ORM\Column(name="status", type="string", length=100, nullable=true)
     */
    private $status;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function finaliser($erreur = null)
    {
        if ($erreur !== null) {
            $this->receveur->debiter($this->montant);
            $this->emetteur->crediter($this->montant);
            $this->status = $erreur;
        } else {
            $this->receveur->crediter($this->montant);
            $this->emetteur->debiter($this->montant);
            $this->status = "OK";
        }
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
     * Set receveurUser
     *
     * @param \PJM\UserBundle\Entity\User $receveurUser
     * @return Transfert
     */
    public function setReceveurUser(\PJM\UserBundle\Entity\User $receveurUser)
    {
        $this->receveurUser = $receveurUser;

        return $this;
    }

    /**
     * Get receveurUser
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getReceveurUser()
    {
        return $this->receveurUser;
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

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Transfert
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
     * Set status
     *
     * @param string $status
     * @return Transfert
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
