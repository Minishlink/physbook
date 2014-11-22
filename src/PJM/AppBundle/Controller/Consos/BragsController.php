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

    public function indexAction(Request $request, $messages = null)
    {
        $em = $this->getDoctrine()->getManager();
        $prixBaguette = $em->getRepository('PJMAppBundle:Item')
            ->findOneBySlug('baguette')
            ->getPrix();

        $formRechargement = $this->createFormBuilder()
            ->add('montant', 'money', array(
                'constraints' => array(
                new NotBlank(),
                new Range(array(
                    'min' => 1,
                    'minMessage' => 'Le montant doit être supérieur à 1€.',
                )),
            )))
        ->getForm();

        $formRechargement->handleRequest($request);
        $data = $formRechargement->getData();
        $montant = $data['montant'];

        if ($formRechargement->isValid()) {
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
                $messages[] = array(
                    'niveau' => 'danger',
                    'contenu' => 'Il y a eu une erreur lors de la communication avec S-Money.'
                );
                $messages[] = $resRechargement['message'];
            }
        } else {
            if (isset($montant) && $montant < 1) {
                $messages[] = array(
                    'niveau' => 'danger',
                    'contenu' => 'Le montant ('.$montant.'€) doit être supérieur à 1€.'
                );
            }
        }

        return $this->render('PJMAppBundle:Consos:brags.html.twig', array(
            'formRechargement' => $formRechargement->createView(),
            'messages' => isset($messages) ? $messages : null,
            'solde' => $this->getSolde(),
            'prixBaguette' => $prixBaguette,
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
                        $error->getMessage()."<br>".$data->getNombre()
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
