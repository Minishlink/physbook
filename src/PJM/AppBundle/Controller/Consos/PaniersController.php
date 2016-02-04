<?php

namespace PJM\AppBundle\Controller\Consos;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaniersController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Template
     */
    public function indexAction()
    {
        $paniersService = $this->get('pjm.services.boquette.paniers');
        $panier = $paniersService->getCurrentPanier();
        $commande = isset($panier) ? $paniersService->getCommande($panier, $this->getUser()) : null;

        return array(
            'boquette' => $this->getBoquette(),
            'panier' => $panier,
            'dejaCommande' => isset($commande),
            'solde' => $paniersService->getSolde($this->getUser()),
        );
    }

    public function commanderAction(Request $request)
    {
        // on va chercher le dernier panier
        $panier = $this->get('pjm.services.boquette.paniers')->getCurrentPanier();

        // si le panier est bien actif
        if (isset($panier) && $panier->getValid()) {
            // on vérifie si l'utilisateur n'a pas déjà commandé un panier
            $commandes = $this->getDoctrine()->getManager()->getRepository('PJMAppBundle:Historique')->findByUserAndItem($this->getUser(), $panier);
            if (empty($commandes)) {
                if ($this->get('pjm.services.historique_manager')->paiement($this->getUser(), $panier, true)) {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Le panier a été commandé. Tu pourras le récupérer chez le ZiPaniers ou dans le local du C\'vis. N\'oublie pas ce jour-là d\'indiquer que tu l\'as récupéré en signant la feuille de reçu.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Tu as déjà commandé ce panier.'
                );
            }
        } else {
            $request->getSession()->getFlashBag()->add(
                'danger',
                "Désolé, c'est trop tard pour commander ce panier."
            );
        }

        return $this->redirect($this->generateUrl('pjm_app_boquette_index', array('slug' => $this->getBoquette()->getSlug())));
    }

    public function annulerAction(Request $request)
    {
        $paniersService = $this->get('pjm.services.boquette.paniers');
        // on va chercher le dernier panier
        $panier = $paniersService->getCurrentPanier();

        if (isset($panier)) {
            $commande = $paniersService->getCommande($panier, $this->getUser());
            // si on a commandé le panier et que le panier est actif
            if (isset($commande) && $panier->getValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($commande);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Ta commande de panier a été annulée et tu as été remboursé.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    "Tu n'as pas commandé ce panier ou ce panier n'est plus annulable automatiquement car les commandes sont déjà prises au prestataire. Contacte le ZiPaniers."
                );
            }
        } else {
            $request->getSession()->getFlashBag()->add(
                'danger',
                "Il n'y a pas de panier disponible actuellement."
            );
        }

        return $this->redirect($this->generateUrl('pjm_app_boquette_index', array('slug' => $this->getBoquette()->getSlug())));
    }

    /**
     * @Template("PJMAppBundle:Boquette:nav.html.twig")
     *
     * @return array
     */
    public function navAction()
    {
        return array(
            'boquette' => $this->getBoquette(),
            'logo' => 'images/header/Fruits-et-legumes-B.png',
        );
    }

    private function getBoquette()
    {
        return $this->get('pjm.services.boquette_manager')->getByType('paniers');
    }
}
