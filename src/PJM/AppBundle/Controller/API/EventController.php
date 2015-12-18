<?php

namespace PJM\AppBundle\Controller\API;

use PJM\AppBundle\Entity\Event\Evenement;
use PJM\AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/event")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/calendrier", options={"expose"=true})
     * @Method("GET")
     */
    public function calendarAction(Request $request)
    {
        $start = new \DateTime($request->query->get('start'));
        $end = new \DateTime($request->query->get('end'));
        $end->setTime(23, 59, 59);

        $events = array_map(function(Evenement $event) {
            return array(
                'title' => $event->getNom(),
                'allDay' => $event->isDay(),
                'start' => $event->getDateDebut()->format('c'),
                'end' => $event->getDateFin()->format('c'),
                'url' => $this->generateUrl('pjm_app_event_index', array('slug' => $event->getSlug()), UrlGeneratorInterface::ABSOLUTE_URL),
                'className' => !$event->isPublic() ? 'prive' : ($event->isMajeur() ? 'majeur' : 'mineur'),
                'lieu' => $event->getLieu(),
            );
        }, $this->get('pjm.services.evenement_manager')->getBetweenDates($start, $end, $this->getUser()));

        $em = $this->getDoctrine()->getManager();

        $annee_debut = $start->format('Y');
        $annee_fin = $end->format('Y');
        $mois_debut = $start->format('m');
        $anniversaires = array_map(function (User $user) use ($annee_debut, $mois_debut, $annee_fin) {
            $mois_anniv = $user->getAnniversaire()->format('m');
            $annee_anniv = $annee_debut;

            if ($mois_anniv == 1 && $mois_debut > 1) {
                $annee_anniv = $annee_fin;
            }

            $anniversaire = new \DateTime($annee_anniv.'-'.$mois_anniv.'-'.$user->getAnniversaire()->format('d'));

            return array(
                'title' => $user->getBucque().' '.$user->getUsername(),
                'allDay' => true,
                'start' => $anniversaire->format('c'),
                'end' => $anniversaire->format('c'),
                'className' => 'anniversaire',
            );
        }, $em->getRepository('PJMAppBundle:User')->getByBirthdayBetweenDates($start, $end, $this->getUser()->getProms()));

        return new JsonResponse(array_merge($events, $anniversaires));
    }
}

