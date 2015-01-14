<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityRepository;

use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Form\Consos\TransactionType;

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
     * Action ajax de rendu de la liste des crédits
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
     * Gère la liste des responsables pour une boquette.
     */
    public function gestionResponsablesAction(Request $request, Boquette $boquette)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PJMUserBundle:User');

        // TODO
        $role = 'ROLE_ZIBRAGS';

        $form = $this->createFormBuilder()
            ->add('user', 'genemu_jqueryselect2_entity', array(
                'error_bubbling' => true,
                'label' => 'Utilisateur',
                'class' => 'PJMUserBundle:User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'constraints' => array(
                    new Assert\NotBlank(),
            )))
            ->add('add', 'submit', array(
                'label' => 'Ajout',
            ))
            ->add('delete', 'submit', array(
                'label' => 'Supprimer',
            ))
            ->setMethod('POST')
            ->setAction($this->generateUrl(
                'pjm_app_admin_consos_gestionResponsables',
                array('slug' => $boquette->getSlug())
            ))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        $user = $data['user'];

        $userManager = $this->get('fos_user.user_manager');

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($form->get('add')->isClicked()) {
                    if (!$user->hasRole($role)) {
                        $user->addRole($role);
                        $userManager->updateUser($user);
                    }

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $user.' est maintenant responsable de cette boquette.'
                    );
                } else if($form->get('delete')->isClicked()) {
                    if ($user->hasRole($role)) {
                        $user->removeRole($role);
                        $userManager->updateUser($user);

                        $request->getSession()->getFlashBag()->add(
                            'success',
                            $user.' n\'est plus responsable de cette boquette.'
                        );
                    }
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

        $listeResponsables = $repository->findByRole($role);

        return $this->render('PJMAppBundle:Admin:Consos/gestionResponsables.html.twig', array(
            'form'      => $form->createView(),
            'listeResponsables' => $listeResponsables
        ));
    }
}
