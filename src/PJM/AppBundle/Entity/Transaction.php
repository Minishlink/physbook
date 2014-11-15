<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction
 *
 * @ORM\Table()
 * @ORM\Entity
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
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="caisseSMoney", type="string", length=255)
     */
    private $caisseSMoney;

    /**
     * @var string
     *
     * @ORM\Column(name="boquette", type="string", length=255)
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
     * 1 : paiement validé / 0 : paiement non validé / null : paiement non complété
     * @ORM\Column(name="valide", type="boolean", nullable=true)
     */
    private $valide;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;



    public function __construct($montant, $caisseSMoney, $boquette, \PJM\UserBundle\Entity\User $user)
    {
        $this->date = new \DateTime();
        $this->montant = $montant;
        $this->caisseSMoney = $caisseSMoney;
        $this->boquette = $boquette;
        $this->user = $user;
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
     * Set valide
     *
     * @param boolean $valide
     * @return Transaction
     */
    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return boolean
     */
    public function getValide()
    {
        return $this->valide;
    }

    /**
     * Set boquette
     *
     * @param string $boquette
     * @return Transaction
     */
    public function setBoquette($boquette)
    {
        $this->boquette = $boquette;

        return $this;
    }

    /**
     * Get boquette
     *
     * @return string
     */
    public function getBoquette()
    {
        return $this->boquette;
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
}
