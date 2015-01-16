<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityRepository;

use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Responsable;
use PJM\AppBundle\Form\Consos\TransactionType;
use PJM\AppBundle\Form\Admin\ResponsableType;

class BoquetteController extends Controller
{
    protected $slug;

    public function getSolde($user = null)
    {
        if (!isset($user)) {
            $user = $this->getUser();
        }

        $utils = $this->get('pjm.services.utils');
        return $utils->getSolde($user, $this->slug);
    }

    public function getBoquette()
    {
        $utils = $this->get('pjm.services.utils');
        return $utils->getBoquette($this->slug);
    }

    public function getItem($itemSlug, $valid = true)
    {
        $em = $this->getDoctrine()->getManager();
        $baguette = $em
            ->getRepository('PJMAppBundle:Item')
            ->findOneBySlugAndValid($itemSlug, $valid);

        return $baguette;
    }

    /**
     * Gère la liste des crédits pour une boquette.
     */
    public function gestionCreditsAction(Request $request, Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();

        $credit = new Transaction();

        $form = $this->createForm(new TransactionType(), $credit, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                "pjm_app_admin_consos_gestionCredits",
                array('slug' => $boquette->getSlug())
            )
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // on enregistre le crédit dans l'historique
                $credit->setStatus("OK");
                $credit->setBoquette($boquette);
                $em->persist($credit);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'La transaction a été enregistrée et le compte a été crédité.'
                );
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la transaction. Réessaye.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl("pjm_app_admin_consos_".$boquette->getSlug()."_index"));
        }

        $datatable = $this->get("pjm.datatable.credits");
        $datatable->setAjaxUrl($this->generateUrl(
            "pjm_app_admin_consos_creditsResults",
            array('boquette_slug' => $boquette->getSlug())
        ));
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:Consos/gestionCredits.html.twig', array(
            'form' => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * Action ajax de rendu de la liste des crédits d'une boquette.
     */
    public function creditsResultsAction($boquette_slug)
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.credits"));
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMAppBundle:Transaction');
        $datatable->addWhereBuilderCallback($repository->callbackFindByBoquetteSlugAndValid($boquette_slug));

        return $datatable->getResponse();
    }

    /**
     * Gère la liste des responsables pour une boquette. (ajout et liste)
     */
    public function gestionResponsablesAction(Request $request, Boquette $boquette)
    {
        $responsable = new Responsable();

        $form = $this->createForm(new ResponsableType(), $responsable, array(
            'method' => 'POST',
            'action' => $this->generateUrl(
                'pjm_app_admin_gestionResponsables',
                array(
                    'slug' => $boquette->getSlug()
                )
            ),
            'boquette' => $boquette
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($responsable);

                $userManager = $this->get('fos_user.user_manager');
                $user = $responsable->getUser();
                $role = $responsable->getResponsabilite()->getRole();

                if ($responsable->getActive()) {
                    if (!$user->hasRole($role)) {
                        $user->addRole($role);
                        $userManager->updateUser($user, false);
                    }

                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $user.' est maintenant '.$responsable->getResponsabilite().' dans '.$boquette.'.'
                    );
                } else {
                    if ($user->hasRole($role)) {
                        $user->removeRole($role);
                        $userManager->updateUser($user, false);
                    }

                    $em->flush();

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $user.' n\'est plus '.$responsable->getResponsabilite().' dans '.$boquette.'.'
                    );
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la modification du responsable. Réessaye. Vérifie que le profil de l\'utilisateur est complet.'
                );

                $data = $form->getData();

                foreach ($form->getErrors() as $error) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $error->getMessage()
                    );
                }
            }

            return $this->redirect($this->generateUrl("pjm_app_admin_consos_".$boquette->getSlug()."_index"));
        }

        $datatable = $this->get("pjm.datatable.admin.responsable");
        $datatable->setBoquetteSlug($boquette->getSlug());
        $datatable->buildDatatableView();

        return $this->render('PJMAppBundle:Admin:gestionResponsables.html.twig', array(
            'form'      => $form->createView(),
            'datatable' => $datatable
        ));
    }

    /**
     * Action ajax de rendu de la liste des responsables d'une boquette.
     */
    public function responsablesResultsAction($boquette_slug)
    {
        $datatable = $this->get("sg_datatables.datatable")->getDatatable($this->get("pjm.datatable.admin.responsable"));

        $datatable->addWhereBuilderCallback(
            function($qb) use ($boquette_slug)
            {
                $qb
                    ->join('Responsable.responsabilite', 're')
                    ->join('re.boquette', 'b', 'WITH', 'b.slug = :boquette_slug')
                    ->setParameter(":boquette_slug", $boquette_slug)
                ;
            }
        );

        return $datatable->getResponse();
    }

    /**
     * Action ajax d'activation ou désactivation des responsables.
     */
    public function toggleResponsablesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $listeResponsables = $request->request->get("data");

            $em = $this->getDoctrine()->getManager();

            foreach ($listeResponsables as $responsable) {
                $responsable->toggleActive();
                $em->persist($responsable);
            }

            $em->flush();

            return new Response("Responsables toggled.");
        }

        return new Response("This is not ajax.", 400);
    }
}
