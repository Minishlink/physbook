<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Historique;

class PaniersController extends Controller
{
    private $slug;

    public function __construct()
    {
        $this->slug = 'paniers';
    }

    public function indexAction(Request $request)
    {
        $paniersService = $this->get('pjm.services.boquette.paniers');
        $panier = $paniersService->getCurrentPanier();
        $commande = $paniersService->getCommande($panier, $this->getUser());

        return $this->render('PJMAppBundle:Consos:Paniers/index.html.twig', array(
            'boquetteSlug' => $this->slug,
            'panier' => $panier,
            'dejaCommande' => isset($commande),
            'solde' => $paniersService->getSolde($this->getUser()),
        ));
    }

    public function commanderAction(Request $request)
    {
        $paniersService = $this->get('pjm.services.boquette.paniers');
        // on va chercher le dernier panier
        $panier = $paniersService->getCurrentPanier();

        // si le panier est bien actif
        if (isset($panier) && $panier->getValid()) {
            // on vérifie si l'utilisateur n'a pas déjà commandé un panier
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Historique');
            $commandes = $repository->findByUserAndItem($this->getUser(), $panier);
            if (empty($commandes)) {
                // on vérifie que l'utilisateur ait assez d'argent
                $repository = $em->getRepository('PJMAppBundle:Compte');
                if (null !== $repository->findOneByUserAndBoquetteAndMinSolde($this->getUser(), $panier->getBoquette(), $panier->getPrix())) {
                    // on enregistre dans l'historique
                    $achat = new Historique();
                    $achat->setUser($this->getUser());
                    $achat->setItem($panier);
                    $achat->setValid(true);

                    $em->persist($achat);
                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Le panier a été commandé. Tu pourras le récupérer chez le ZiPaniers ou dans le local du C\'vis. N\'oublie pas ce jour-là d\'indiquer que tu l\'as récupéré en signant la feuille de reçu.'
                    );
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Tu n\'as pas assez d\'argent sur ton compte.'
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

        return $this->redirect($this->generateUrl('pjm_app_boquette_paniers_index'));
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

        return $this->redirect($this->generateUrl('pjm_app_boquette_paniers_index'));
    }
}
