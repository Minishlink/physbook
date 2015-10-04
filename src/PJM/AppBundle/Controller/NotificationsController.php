<?php

namespace PJM\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationsController extends Controller
{
    /**
     * @return array
     *
     * @Template
     */
    public function indexAction() {
        $notificationManager = $this->get('pjm.services.notification');
        $notifications = $notificationManager->get($this->getUser());
        return array(
            'notifications' => $notifications
        );
    }

    public function extraitAction() {

    }
}
