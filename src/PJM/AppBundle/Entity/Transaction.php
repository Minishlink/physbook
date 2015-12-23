<?php

namespace PJM\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @ORM\Column(name="montant", type="integer")
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

    /** @var Compte */
    private $compteLie;

    /** @var ArrayCollection<Compte> */
    private $comptes;

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
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        $moyenPaiement = $this->getMoyenPaiement();
        $montant = $this->getMontant();
        $infos = $this->getInfos();
        $doTransfert = (null !== $this->getCompteLie());

        if ($moyenPaiement === 'cheque' && empty($infos)) {
            $context->buildViolation('Merci de renseigner le n° du chèque.')
                ->atPath('infos')
                ->addViolation();
        }

        // opération de crédit ou débit
        if (in_array($moyenPaiement, array('autre', 'operation'))) {
            if (empty($infos)) {
                $context->buildViolation('Merci de préciser la raison.')
                    ->atPath('infos')
                    ->addViolation();
            }

            if ($doTransfert) {
                $context->buildViolation('Le transfert automatique n\'est pas possible pour les opérations.')
                    ->atPath('compte')
                    ->addViolation();
            }
        }

        // on vérifie le signe du montant
        if ($moyenPaiement === 'operation') {
            if ($montant >= 0) {
                $context->buildViolation('Une opération de débit doit avoir un montant négatif.')
                    ->atPath('montant')
                    ->addViolation();
            }
        } else {
            if ($montant <= 0) {
                $context->buildViolation('Le montant doit être positif.')
                    ->atPath('montant')
                    ->addViolation();
            }
        }

        if ($doTransfert) {
            if ($this->getCompteLie() === $this->getCompte()) {
                $context->buildViolation('Le compte de destination et le compte créditeur ne peuvent pas être les mêmes.')
                    ->atPath('compte')
                    ->addViolation();
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
     * @param Compte $compte
     *
     * @return Transaction
     */
    public function setCompte(Compte $compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte.
     *
     * @return Compte
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set compteLie. Le compte receveur du transfert.
     *
     * @param Compte $compteLie
     *
     * @return Transaction
     */
    public function setCompteLie(Compte $compteLie)
    {
        $this->compteLie = $compteLie;

        return $this;
    }

    /**
     * Get compteLie.
     *
     * @return Compte
     */
    public function getCompteLie()
    {
        return $this->compteLie;
    }

    /**
     * @return ArrayCollection
     */
    public function getComptes()
    {
        return $this->comptes;
    }

    /**
     * @param ArrayCollection $comptes
     *
     * @return Transaction
     */
    public function setComptes(ArrayCollection $comptes)
    {
        $this->comptes = $comptes;

        return $this;
    }
}
