<?php

namespace PJM\AppBundle\Services;

use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Entity\Inbox\Inbox;
use PJM\AppBundle\Entity\Compte;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

class UserManager extends BaseUserManager
{
    private $boquettesComptes;

    /** @var Mailer */
    private $mailer;

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
}
