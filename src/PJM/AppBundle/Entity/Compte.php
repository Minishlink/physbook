<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Compte.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\CompteRepository")
 */
class Compte
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
     * @var int
     *
     * @ORM\Column(name="solde", type="integer")
     */
    private $solde;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Boquette")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boquette;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\User", inversedBy="comptes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Transaction", mappedBy="compte")
     **/
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Consos\Transfert", mappedBy="emetteur")
     **/
    private $envois;

    /**
     * @ORM\OneToMany(targetEntity="PJM\AppBundle\Entity\Consos\Transfert", mappedBy="receveur")
     **/
    private $receptions;

    public function __construct(\PJM\AppBundle\Entity\User $user, Boquette $boquette)
    {
        $this->solde = 0;
        $this->user = $user;
        $this->boquette = $boquette;
        $this->transactions = new ArrayCollection();
        $this->envois = new ArrayCollection();
        $this->receptions = new ArrayCollection();
    }

    public function __toString()
    {
        return '['.$this->boquette.'] '.$this->user;
    }

    public function toArray()
    {
        $user = $this->user;

        return array(
            'username' => $user->getUsername(),
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'kgib' => $user->getAppartement(),
            'solde' => $this->solde / 100,
        );
    }

    public function debiter($montant)
    {
        $this->solde -= $montant;
    }

    public function crediter($montant)
    {
        $this->solde += $montant;
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
     * Set solde (en centimes).
     *
     * @param int $solde
     *
     * @return Compte
     */
    public function setSolde($solde)
    {
        $this->solde = $solde;

        return $this;
    }

    /**
     * Get solde (en centimes).
     *
     * @return int
     */
    public function getSolde()
    {
        return $this->solde;
    }

    /**
     * Set boquette.
     *
     * @param \PJM\AppBundle\Entity\Boquette $boquette
     *
     * @return Compte
     */
    public function setBoquette(\PJM\AppBundle\Entity\Boquette $boquette)
    {
        $this->boquette = $boquette;

        return $this;
    }

    /**
     * Get boquette.
     *
     * @return \PJM\AppBundle\Entity\Boquette
     */
    public function getBoquette()
    {
        return $this->boquette;
    }

    /**
     * Set user.
     *
     * @param \PJM\AppBundle\Entity\User $user
     *
     * @return Compte
     */
    public function setUser(\PJM\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \PJM\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add transactions.
     *
     * @param \PJM\AppBundle\Entity\Boquette $transactions
     *
     * @return Compte
     */
    public function addTransaction(\PJM\AppBundle\Entity\Boquette $transactions)
    {
        $this->transactions[] = $transactions;

        return $this;
    }

    /**
     * Remove transactions.
     *
     * @param \PJM\AppBundle\Entity\Boquette $transactions
     */
    public function removeTransaction(\PJM\AppBundle\Entity\Boquette $transactions)
    {
        $this->transactions->removeElement($transactions);
    }

    /**
     * Get transactions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Add envois.
     *
     * @param \PJM\AppBundle\Entity\Consos\Transfert $envois
     *
     * @return Compte
     */
    public function addEnvois(\PJM\AppBundle\Entity\Consos\Transfert $envois)
    {
        $this->envois[] = $envois;

        return $this;
    }

    /**
     * Remove envois.
     *
     * @param \PJM\AppBundle\Entity\Consos\Transfert $envois
     */
    public function removeEnvois(\PJM\AppBundle\Entity\Consos\Transfert $envois)
    {
        $this->envois->removeElement($envois);
    }

    /**
     * Get envois.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnvois()
    {
        return $this->envois;
    }

    /**
     * Add receptions.
     *
     * @param \PJM\AppBundle\Entity\Consos\Transfert $receptions
     *
     * @return Compte
     */
    public function addReception(\PJM\AppBundle\Entity\Consos\Transfert $receptions)
    {
        $this->receptions[] = $receptions;

        return $this;
    }

    /**
     * Remove receptions.
     *
     * @param \PJM\AppBundle\Entity\Consos\Transfert $receptions
     */
    public function removeReception(\PJM\AppBundle\Entity\Consos\Transfert $receptions)
    {
        $this->receptions->removeElement($receptions);
    }

    /**
     * Get receptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReceptions()
    {
        return $this->receptions;
    }
}
