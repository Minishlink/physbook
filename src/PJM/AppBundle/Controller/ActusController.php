<?php

namespace PJM\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PJM\AppBundle\Entity\Actus\Article;
use PJM\AppBundle\Form\Type\Actus\ArticleType;

class ActusController extends Controller
{
    /**
     * @param $page
     *
     * @return array
     *
     * @Route("/{page}", requirements={"page" = "\d*"})
     * @Method("GET")
     * @Template
     */
    public function indexAction($page = 1)
    {
        $articleManager = $this->get('pjm.services.article_manager');
        $articles = $articleManager->getByPage($page);
        $brouillons = $articleManager->getDrafts($this->getUser());

        return array(
            'articles' => $articles,
            'brouillons' => $brouillons,
            'page' => $page,
            'nombrePages' => ceil(count($articles) / $articleManager->nbArticlesParPage),
        );
    }

    /**
     * @param Article $article
     *
     * @return array
     *
     * @Route("/article/{slug}")
     * @Method("GET")
     * @Template
     */
    public function voirAction(Article $article)
    {
        return array(
            'article' => $article,
        );
    }

    /**
     * @param $nombre
     *
     * @return array
     *
     * @Route("/extrait/{nombre}", requirements={"nombre" = "\d*"})
     * @Method("GET")
     * @Template
     */
    public function extraitAction($nombre = 4)
    {
        return array(
            'liste_articles' => $this->get('pjm.services.article_manager')->getSome($nombre),
        );
    }

    /**
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/ajouter")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function ajouterAction(Request $request)
    {
        $articleManager = $this->get('pjm.services.article_manager');
        $article = $articleManager->create($this->getUser());

        $form = $this->createForm(new ArticleType(), $article, array(
            'action' => $this->generateUrl('pjm_app_actus_ajouter'),
            'user' => $this->getUser(),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleManager->update($article, true);

                return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
            }

            $this->get('pjm.services.notification')->sendFlash(
                'danger',
                'Un problème est survenu lors de l\'ajout. Réessaye.'
            );
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @param Request $request
     * @param Article $article
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/modifier/{slug}")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function modifierAction(Request $request, Article $article)
    {
        $articleManager = $this->get('pjm.services.article_manager');

        if (!$articleManager->canEdit($this->getUser(), $article)) {
            $this->get('pjm.services.notification')->sendFlash(
                'danger',
                "Tu ne peux pas modifier cet article car tu n'en es pas l'auteur."
            );

            return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
        }

        $form = $this->createForm(new ArticleType(), $article, array(
            'action' => $this->generateUrl('pjm_app_actus_modifier', array(
                'slug' => $article->getSlug(),
            )),
            'user' => $this->getUser(),
            'ajout' => false,
        ));

        $wasDraft = !$article->getPublication(); // treated as new if it was a draft
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleManager->update($article, $wasDraft);

                return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
            }

            $this->get('pjm.services.notification')->sendFlash(
                'danger',
                'Un problème est survenu lors de la modification. Réessaye.'
            );
        }

        return array(
            'article' => $article,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Request $request
     * @param Article $article
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/supprimer/{slug}")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function supprimerAction(Request $request, Article $article)
    {
        $articleManager = $this->get('pjm.services.article_manager');

        if (!$articleManager->canDelete($this->getUser(), $article)) {
            $this->get('pjm.services.notification')->sendFlash(
                'danger',
                "Tu ne peux pas supprimer cet article car tu n'en es pas l'auteur ou tu n'es pas ZiCom Asso."
            );

            return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleManager->remove($article);

                $this->get('pjm.services.notification')->sendFlash(
                    'info',
                    'Article bien supprimé'
                );

                return $this->redirect($this->generateUrl('pjm_app_actus_index'));
            }

            $this->get('pjm.services.notification')->sendFlash(
                'danger',
                'Un problème est survenu lors de la suppression. Réessaye.'
            );
        }

        return array(
            'article' => $article,
            'form' => $form->createView(),
        );
    }
}
