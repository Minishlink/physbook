<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Transaction.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\TransactionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Transaction
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
     * @var string
     *
     * @ORM\Column(name="moyenPaiement", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Choice(callback = {"PJM\AppBundle\Enum\TransactionEnum", "getMoyenPaiementChoices"})
     */
    private $moyenPaiement;

    /**
     * @var string
     *
     * @ORM\Column(name="infos", type="string", length=255, nullable=true)
     */
    private $infos;

    /**
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Compte", inversedBy="transactions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     **/
    private $compte;

    /**
     * @var int
     *
     * @ORM\Column(name="montant", type="smallint")
     * @Assert\NotBlank()
     * @Assert\NotEqualTo(
     *      value = 0,
     *      message = "Le montant doit être différent de 0€."
     * )
     */
    private $montant;

    /**
     * @var string
     *             "OK" : paiement validé et enregistré
     *             "NOK" : paiement non validé
     *             {errorCode} : erreur communiquée par S-Money
     *             null : paiement non complété
     *
     * @ORM\Column(name="status", type="string", length=100, nullable=true)
     */
    private $status;

    private $compteLie;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function toArray()
    {
        $user = $this->compte->getUser();

        return array(
            'date' => $this->date->format('d/m/Y H:i:s'),
            'username' => $user->getUsername(),
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'montant' => $this->montant / 100,
            'moyenPaiement' => $this->moyenPaiement,
            'infos' => $this->infos,
            'status' => $this->status,
        );
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
    public function validate(ExecutionContextInterface $context)
    {
        $moyenPaiement = $this->getMoyenPaiement();
        $montant = $this->getMontant();
        $infos = $this->getInfos();
        if ($moyenPaiement == 'cheque' && empty($infos)) {
            $context->addViolationAt(
                'infos',
                'Merci de renseigner le n° du chèque.',
                array(),
                null
            );
        }

        if (in_array($moyenPaiement, array('autre', 'operation')) && empty($infos)) {
            $context->addViolationAt(
                'infos',
                'Merci de préciser la raison.',
                array(),
                null
            );
        }

        if ($moyenPaiement == 'operation') {
            if ($montant >= 0) {
                $context->addViolationAt(
                    'montant',
                    'Une opération doit avoir un montant négatif.',
                    array(),
                    null
                );
            }

            if (null !== $this->getCompteLie()) {
                $context->addViolationAt(
                    'compte',
                    "Le transfert n'est pas possible pour une opération.",
                    array(),
                    null
                );
            }
        } else {
            if ($montant <= 0) {
                $context->addViolationAt(
                    'montant',
                    'Le montant doit être positif.',
                    array(),
                    null
                );
            }
        }

        if (null !== $this->getCompteLie()) {
            if ($this->getCompteLie() === $this->getCompte()) {
                $context->addViolationAt(
                    'compte',
                    'Le compte de destination et le compte créditeur ne peuvent pas être les mêmes.',
                    array(),
                    null
                );
            }
        }
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
     * @return Transaction
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
     * Set caisseSMoney.
     *
     * @param string $caisseSMoney
     *
     * @return Transaction
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
     * Set montant.
     *
     * @param int $montant
     *
     * @return Transaction
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant.
     *
     * @return int
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Show montant.
     *
     * @return string
     */
    public function showMontant()
    {
        return (string) $this->montant / 100;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set moyenPaiement.
     *
     * @param string $moyenPaiement
     *
     * @return Transaction
     */
    public function setMoyenPaiement($moyenPaiement)
    {
        $this->moyenPaiement = $moyenPaiement;

        return $this;
    }

    /**
     * Get moyenPaiement.
     *
     * @return string
     */
    public function getMoyenPaiement()
    {
        return $this->moyenPaiement;
    }

    /**
     * Set infos.
     *
     * @param string $infos
     *
     * @return Transaction
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos.
     *
     * @return string
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Set compte.
     *
     * @param \PJM\AppBundle\Entity\Compte $compte
     *
     * @return Transaction
     */
    public function setCompte(\PJM\AppBundle\Entity\Compte $compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte.
     *
     * @return \PJM\AppBundle\Entity\Compte
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set compteLie.
     *
     * @param \PJM\AppBundle\Entity\Compte $compteLie
     *
     * @return Transaction
     */
    public function setCompteLie(\PJM\AppBundle\Entity\Compte $compteLie)
    {
        $this->compteLie = $this->compte;
        $this->compte = $compteLie;

        return $this;
    }

    /**
     * Get compteLie.
     *
     * @return \PJM\AppBundle\Entity\Compte
     */
    public function getCompteLie()
    {
        return $this->compteLie;
    }
}
