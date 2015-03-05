<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Transaction
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\TransactionRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\Column(name="moyenPaiement", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"smoney", "cheque", "monnaie"})
     */
    private $moyenPaiement;

    /**
     * @var string
     *
     * @ORM\Column(name="infos", type="string", length=255, nullable=true)
     */
    private $infos;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Boquette")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boquette;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Compte", inversedBy="transactions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $compte;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="smallint")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(
     *      value = 0,
     *      message = "Le montant doit être supérieur à 0€."
     * )
     */
    private $montant;

    /**
     * @var boolean
     * "OK" : paiement validé et enregistré
     * "NOK" : paiement non validé
     * {errorCode} : erreur communiquée par S-Money
     * null : paiement non complété
     *
     * @ORM\Column(name="status", type="string", length=100, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;



    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function finaliser($erreur = null)
    {
        if ($erreur !== null) {
            $this->compte->debiter($this->montant);
            $this->setStatus($erreur);
        } else {
            $this->compte->crediter($this->montant);
        }
    }

    /**
     * @Assert\Callback
     */
    public function isInfosValid(ExecutionContextInterface $context)
    {
        $moyenPaiement = $this->getMoyenPaiement();
        $infos = $this->getInfos();
        if ($moyenPaiement == "cheque" && empty($infos)) {
            $context->addViolationAt(
                'infos',
                'Merci de renseigner le n° du chèque.',
                array(),
                null
            );
        }

        if ($moyenPaiement == "monnaie" && !empty($infos)) {
            $context->addViolationAt(
                'infos',
                'Le champ de n° de chèque doit être vide pour de la monnaie.',
                array(),
                null
            );
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

    /**
     * Set moyenPaiement
     *
     * @param string $moyenPaiement
     * @return Transaction
     */
    public function setMoyenPaiement($moyenPaiement)
    {
        $this->moyenPaiement = $moyenPaiement;

        return $this;
    }

    /**
     * Get moyenPaiement
     *
     * @return string
     */
    public function getMoyenPaiement()
    {
        return $this->moyenPaiement;
    }

    /**
     * Set infos
     *
     * @param string $infos
     * @return Transaction
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos
     *
     * @return string
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Set compte
     *
     * @param \PJM\AppBundle\Entity\Compte $compte
     * @return Transaction
     */
    public function setCompte(\PJM\AppBundle\Entity\Compte $compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return \PJM\AppBundle\Entity\Compte
     */
    public function getCompte()
    {
        return $this->compte;
    }
}
