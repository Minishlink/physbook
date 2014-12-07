<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transaction
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\TransactionRepository")
 */
class Transaction
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
     * @var string
     *
     * @ORM\Column(name="caisseSMoney", type="string", length=255)
     */
    private $caisseSMoney;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Boquette")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boquette;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="smallint")
     */
    private $montant;

    /**
     * @var boolean
     * "OK" : paiement validé et enregistré
     * "NOK" : paiement non validé
     * {errorCode} : erreur communiquée par S-Money
     * null : paiement non complété
     *
     * @ORM\Column(name="status", type="string", length=5, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;



    public function __construct($montant, \PJM\AppBundle\Entity\Boquette $boquette, \PJM\UserBundle\Entity\User $user)
    {
        $this->date = new \DateTime();
        $this->montant = $montant;
        $this->caisseSMoney = $boquette->getCaisseSMoney();
        $this->boquette = $boquette;
        $this->user = $user;
    }

    public function getMoyenPaiement()
    {
        switch ($this->caisseSMoney) {
            case 'cheque':
                $moyen = "Chèque";
                break;
            case 'monnaie':
                $moyen = "Monnaie";
                break;
            default:
                $moyen = "[S-Money] ".$this->caisseSMoney;
                break;
        }

        return $moyen;
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
     * Set date
     *
     * @param \DateTime $date
     * @return Transaction
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
     * Set caisseSMoney
     *
     * @param string $caisseSMoney
     * @return Transaction
     */
    public function setCaisseSMoney($caisseSMoney)
    {
        $this->caisseSMoney = $caisseSMoney;

        return $this;
    }

    /**
     * Get caisseSMoney
     *
     * @return string
     */
    public function getCaisseSMoney()
    {
        return $this->caisseSMoney;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     * @return Transaction
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
     * Show montant
     *
     * @return string
     */
    public function showMontant()
    {
        return (string) $this->montant/100;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Transaction
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

    /**
     * Set user
     *
     * @param \PJM\UserBundle\Entity\User $user
     * @return Transaction
     */
    public function setUser(\PJM\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PJM\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set boquette
     *
     * @param \PJM\AppBundle\Entity\Boquette $boquette
     * @return Transaction
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
}
