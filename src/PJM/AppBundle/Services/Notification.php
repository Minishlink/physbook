<?php
/**
 * Created by PhpStorm.
 * User: Louis
 * Date: 10/09/2015
 * Time: 22:54
 */

namespace PJM\AppBundle\Services;

use PJM\AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class Notification
{
    private $requestStack;

    public function __construct(RequestStack $requestStack, Push $push)
    {
        $this->requestStack = $requestStack;
        $this->push = $push;
    }

    public function sendFlash($type, $message)
    {
        $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add($type, $message);
    }

    public function sendPushToUser(User $user, $message, $type)
    {
        $this->push->sendNotificationToUser($user, $message, $type);
    }
}
