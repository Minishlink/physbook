<?php

namespace PJM\AppBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Entity\Inbox\Inbox;
use PJM\AppBundle\Entity\Compte;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends BaseUserManager
{
    private $boquettesComptes;

    /** @var Mailer */
    private $mailer;

    /** @var Trads */
    private $trads;

    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        CanonicalizerInterface $usernameCanonicalizer,
        CanonicalizerInterface $emailCanonicalizer,
        ObjectManager $om,
        $class,
        Trads $trads
    ) {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);

        $this->trads = $trads;
    }

    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Configure a user.
     *
     * Creates the user inbox and accounts.
     *
     * @param User $user The user
     * @param bool [$generatePassword=false] If true, a random password will be generated
     */
    public function configure(User $user, $generatePassword = false)
    {
        if ($generatePassword) {
            $password = substr(uniqid(), 0, 8);
            $user->setPlainPassword($password);
        }

        $user->setUsername($user->getFams().$user->getTabagns().$user->getProms());
        $user->setNums($this->trads->getNums($user->getFams()));

        $this->updateUser($user, false);

        // on crée l'inbox
        $inbox = new Inbox();
        $user->setInbox($inbox);

        // les boquettes concernées pour l'ouverture de compte :
        if (!isset($this->boquettesComptes)) {
            $repository = $this->objectManager->getRepository('PJMAppBundle:Boquette');
            $this->boquettesComptes = array(
                $repository->findOneBySlug('pians'),
                $repository->findOneBySlug('paniers'),
                $repository->findOneBySlug('brags'),
            );
        }

        // on crée les comptes
        foreach ($this->boquettesComptes as $boquette) {
            $nvCompte = new Compte($user, $boquette);
            $this->objectManager->persist($nvCompte);
        }

        if (isset($this->mailer)) {
            // on envoit un mail
            $context = array(
                'user' => $user,
                'password' => $password,
            );

            $template = 'PJMAppBundle:Mail:inscription.html.twig';

            $this->mailer->send($user, $context, $template);
        }
    }

    /**
     * Get all users whose birthdays are between these dates.
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param User      $currentUser     If set, users will be filtered by prom's +-1 the one of this user
     * @param bool      $forFullCalendar If true, return an array compatible with FullCalendar
     *
     * @return User[]|array
     */
    public function getBirthdaysBetweenDates(\DateTime $dateDebut, \DateTime $dateFin, User $currentUser = null, $forFullCalendar = false)
    {
        $users = $this->objectManager->getRepository('PJMAppBundle:User')
            ->getByBirthdayBetweenDates($dateDebut, $dateFin, isset($currentUser) ? $currentUser->getProms() : null);

        if (!$forFullCalendar) {
            return $users;
        }

        $annee_debut = $dateDebut->format('Y');
        $annee_fin = $dateFin->format('Y');
        $mois_debut = $dateDebut->format('m');

        return array_map(function (User $user) use ($annee_debut, $mois_debut, $annee_fin) {
            $mois_anniv = $user->getAnniversaire()->format('m');
            $annee_anniv = ($mois_anniv == 1 && $mois_debut > 1) ? $annee_fin : $annee_debut;

            $anniversaire = new \DateTime($annee_anniv.'-'.$mois_anniv.'-'.$user->getAnniversaire()->format('d'));

            return array(
                'title' => $user->getBucque().' '.$user->getUsername(),
                'allDay' => true,
                'start' => $anniversaire->format('c'),
                'end' => $anniversaire->format('c'),
                'className' => 'anniversaire',
            );
        }, $users);
    }

    /**
     * Get all users whose exance are between these dates, sorted by num's.
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param User      $currentUser     If set, users will be filtered by prom's +-1 the one of this user
     * @param bool      $forFullCalendar If true, return an array compatible with FullCalendar
     *
     * @return User[]|array
     */
    public function getExancesBetweenDates(\DateTime $dateDebut, \DateTime $dateFin, User $currentUser = null, $forFullCalendar = false)
    {
        $exances = array();
        $repo = $this->objectManager->getRepository('PJMAppBundle:User');
        $proms = isset($currentUser) ? $currentUser->getProms() : null;

        for ($date_exance = $dateDebut; $date_exance < $dateFin; $date_exance->modify('+1 day')) {
            $exance = $this->trads->getExanceFromDate($date_exance);

            // on vérifie que l'exance existe
            $users = $repo->findByNums($exance, $proms);
            if (!empty($users)) {
                if (!$forFullCalendar) {
                    $exances[$exance] = $users;
                } else {
                    $users = array_map(function (User $user) {
                        return $user->getBucque();
                    }, $users);

                    $exances[] = array(
                        'title' => 'Ex '.$exance.' : '.implode(', ', $users),
                        'allDay' => true,
                        'start' => $date_exance->format('c'),
                        'end' => $date_exance->format('c'),
                        'className' => 'exance',
                    );
                }
            }
        }

        return $exances;
    }
}
