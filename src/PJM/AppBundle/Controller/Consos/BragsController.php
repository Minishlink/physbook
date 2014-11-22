<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Form\Consos\CommandeType;

class BragsController extends Controller
{
    private $slug;

    public function __construct()
    {
        $this->slug = 'brags';
    }

    public function indexAction(Request $request)
    {
        return $this->render('PJMAppBundle:Consos:Brags/index.html.twig', array(
            'solde' => $this->getSolde(),
            'prixBaguette' => $this->getPrixBaguette(),
            'nbParJour' => 0.5
        ));
    }

    public function getSolde()
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('PJMAppBundle:Boquette');
        $boquette = $repository->findOneBySlug($this->slug);

        $repository = $em->getRepository('PJMAppBundle:Compte');
        $compte = $repository->findOneByUserAndBoquette($this->getUser(), $boquette);

        if ($compte === null) {
            $solde = 0;
        } else {
            $solde = $compte->showSolde();
        }

        return $solde;
    }

    public function getPrixBaguette()
    {
        $em = $this->getDoctrine()->getManager();
        $prixBaguette = $em->getRepository('PJMAppBundle:Item')
            ->findOneBySlug('baguette')
            ->getPrix();

        return $prixBaguette;
    }

    public function rechargementAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('montant', 'money', array(
                'error_bubbling' => true,
                'constraints' => array(
                new NotBlank(),
                new Range(array(
                    'min' => 1,
                    'minMessage' => 'Le montant doit être supérieur à 1€.',
                )),
            )))
            ->setMethod('POST')
            ->setAction($this->generateUrl('pjm_app_consos_brags_rechargement'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $montant = $data['montant'];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on redirige vers S-Money
                $resRechargement = json_decode(
                    $this->forward('PJMAppBundle:Consos/Rechargement:getURL', array(
                        'montant' => $montant*100,
                        'boquette_slug' => $this->slug
                    ))->getContent(),
                    true
                );

                if ($resRechargement['valid'] === true) {
                    // succès, on redirige vers l'URL de paiement
                    return $this->redirect($resRechargement['url']);
                } else {
                    // erreur
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        'Il y a eu une erreur lors de la communication avec S-Money.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de ton rechargement. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_index'));
        }

        return $this->render('PJMAppBundle:Consos:Brags/rechargement.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function commandeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $commande = new Historique();

        $form = $this->createForm(new CommandeType(), $commande, array(
            'action' => $this->generateUrl('pjm_app_consos_brags_commande'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = $em->getRepository('PJMAppBundle:Item')
                    ->findOneBySlug('baguette');
                $commande->setItem($item);
                $commande->setUser($this->getUser());
                $commande->setNombre($commande->getNombre()*10);
                $em->persist($commande);
                $em->flush($commande);

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Ta commande a été passée. Elle sera validée par le ZiBrag\'s le jour où tu commenceras à pouvoir prendre ton pain. Tu seras notifié.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de ta commande. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_consos_brags_index'));
        }

        return $this->render('PJMAppBundle:Consos:Brags/commande.html.twig', array(
            'form' => $form->createView(),
            'messages' => isset($messages) ? $messages : null,
            'nbParJour' => 0.5
        ));
    }
}
