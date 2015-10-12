<?php

namespace PJM\AppBundle\Controller;

use PJM\AppBundle\Form\Type\NotificationSettingsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        $notificationManager->markAllAsRead($this->getUser());

        return array(
            'notifications' => $notifications
        );
    }

    /**
     * @Template
     */
    public function navAction() {
        return array(
            'nbNotReceived' => $this->getDoctrine()->getRepository('PJMAppBundle:Notifications\Notification')->countNotReceived($this->getUser())
        );
    }

    /**
     * Affichage et gestion des réglages
     *
     * @return array
     *
     * @Template
     */
    public function reglagesAction(Request $request)
    {
        $notificationSettings = $this->getUser()->getNotificationSettings();

        $form = $this->createForm(new NotificationSettingsType(), $notificationSettings, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_notifications_reglages'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($notificationSettings);
                $em->flush();
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu. Réessaye.'
                );
            }

            return $this->redirect($this->generateUrl('pjm_app_notifications_reglages'));
        }

        $datatable_push = $this->get('pjm.datatable.pushsubscription');
        $datatable_push->buildDatatable();

        return array(
            'form' => $form->createView(),
            'datatable_push' => $datatable_push,
        );
    }
}
