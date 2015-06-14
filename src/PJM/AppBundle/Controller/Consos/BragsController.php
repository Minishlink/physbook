<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Commande;
use PJM\AppBundle\Form\Type\Consos\CommandeType;

class BragsController extends Controller
{
    public function indexAction(Request $request)
    {
        $nbCommandes = $this->getDoctrine()->getManager()
            ->getRepository('PJMAppBundle:Commande')
            ->getTotalCommandes();

        $group = $this->get('pjm.services.group');
        $bragsService = $this->get('pjm.services.boquette.brags');

        $finAnnee = ($this->getUser()->getProms() == $group->getPromsPN(2)) ?
            new \DateTime(date('Y').'-06-05') : // anciens
            new \DateTime(date('Y').'-06-12'); // conscrits

        return $this->render('PJMAppBundle:Consos:Brags/index.html.twig', array(
            'boquetteSlug' => 'brags',
            'solde' => $bragsService->getSolde($this->getUser()),
            'prixBaguette' => $bragsService->getCurrentBaguette()->getPrix(),
            'commande' => $bragsService->getCommande($this->getUser()),
            'nbCommandes' => $nbCommandes,
            'finAnnee' => $finAnnee,
            'resteNbJoursOuvres' => $bragsService->getNbJoursOuvres($finAnnee)
        ));
    }

    public function commandeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $commande = new Commande();
        $bragsService = $this->get('pjm.services.boquette.brags');

        $form = $this->createForm(new CommandeType(), $commande, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_boquette_brags_commande'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $kagib = $this->getUser()->getAppartement();
                if ($kagib === null || !preg_match("/(^([A-C])(\d)+([a-zA-Z]+)?$)|SKF/", substr($kagib,0,2))) {
                    $request->getSession()->getFlashBag()->add(
                        'danger',
                        "Il faut que tu indiques au moins ton étage (ex. \"B2\") dans ton profil pour pouvoir commander du brag's. Si tu es SKF, mets l'étage auquel tu veux aller chercher ton pain. Tu peux mettre n'importe quoi après les deux premières lettres comme par ex. \"B2 d'hons (SFK)\"."
                    );

                    return $this->redirect($this->generateUrl('pjm_app_boquette_brags_index'));
                }

                $commande->setItem($bragsService->getCurrentBaguette());
                $commande->setUser($this->getUser());
                $commande->setNombre($commande->getNombre()*10);
                $em->persist($commande);
                $em->flush($commande);

                if ($commande->getNombre() > 0) {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Ta commande a été passée. Tu pourras commencer à prendre ton pain le jour où un ZiBrag\'s valide ta demande de commande.'
                    );
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Tu ne recevras plus de pain bientôt. Tant que ta résiliation n\'a pas été validée par le ZiBrag\'s, tu peux continuer à prendre ton pain et tu seras débité.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de ta commande. Réessaye.'
                );

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl('pjm_app_boquette_brags_index'));
        }

        return $this->render('PJMAppBundle:Consos:Brags/commande.html.twig', array(
            'form' => $form->createView(),
            'commande' => $bragsService->getCommande($this->getUser())
        ));
    }
}
