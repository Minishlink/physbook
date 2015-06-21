<?php

// http://www.symfony-grenoble.fr/238/lister-les-utilisateurs-en-ligne/


namespace PJM\UserBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use PJM\UserBundle\Entity\User;

class ActivityListener
{
    protected $tokenStorage;
    protected $em;

    public function __construct(TokenStorage $tokenStorage, EntityManager $manager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $manager;
    }

    /**
     * Update the user "lastActivity" on each request.
     *
     * @param FilterControllerEvent $event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        // ici nous vérifions que la requête est une "MASTER_REQUEST" pour que les sous-requête soit ingoré (par exemple si vous faites un render() dans votre template)
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        // Nous vérifions qu'un token d'autentification est bien présent avant d'essayer manipuler l'utilisateur courant.
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            // Nous utilisons un délais pendant lequel nous considèrerons que l'utilisateur est toujours actif et qu'il n'est pas nécessaire de refaire de mise à jour
            $delay = new \DateTime();
            $delay->setTimestamp(strtotime('2 minutes ago'));

            // Nous vérifions que l'utilisateur est bien du bon type pour ne pas appeler getLastActivity() sur un objet autre objet User
            if ($user instanceof User && $user->getLastActivity() < $delay) {
                $user->setLastActivity(new \DateTime());
                $this->em->flush($user);
            }
        }
    }
}
