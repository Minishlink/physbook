<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use PJM\AppBundle\Entity\Actus\Article;
use PJM\AppBundle\Entity\Actus\Image;
use PJM\AppBundle\Entity\Actus\Commentaire;
use PJM\AppBundle\Entity\Actus\Categorie;
use PJM\AppBundle\Form\Actus\ArticleType;

class ActusController extends Controller
{
    public function indexAction($page)
    {
        $nbArticlesParPage = 5;

        // on récupère la liste des articles
        $articles = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('PJMAppBundle:Actus\Article')
                        ->getArticles($nbArticlesParPage, $page);

        // on retourne le template
        return $this->render('PJMAppBundle:Actus:index.html.twig', array(
            'articles'   => $articles,
            'page'       => $page,
            'nombrePages'=> ceil(count($articles)/$nbArticlesParPage)
        ));
    }


    public function voirAction(Article $article)
    {
            // on retourne le template
            return $this->render('PJMAppBundle:Actus:voir.html.twig', array(
                'article' => $article
            ));
    }

    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function ajouterAction()
    {
        $article = new Article();
        $form = $this->createForm(new ArticleType, $article);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();

                return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
            }
        }

        return $this->render('PJMAppBundle:Actus:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function modifierAction(Article $article)
    {
        $form = $this->createForm(new ArticleType, $article);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();

                return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
            }
        }

        // affichage du formulaire
        return $this->render('PJMAppBundle:Actus:modifier.html.twig', array(
            'article' => $article,
            'form' => $form->createView()
        ));
    }

    public function supprimerAction(Article $article)
    {
        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($article);
                $em->flush();

                $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');

                return $this->redirect($this->generateUrl('pjm_app_actus_index'));
            }
        }

        return $this->render('PJMAppBundle:Actus:supprimer.html.twig', array(
            'article' => $article,
            'form' => $form->createView()
        ));
    }

    public function menuAction($nombre)
    {
        // on récupère la liste des articles
        $articles = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('PJMAppBundle:Actus\Article')
                        ->findBy(
                            array('publication' => true), // pas de critère
                            array('date' => 'desc'), // on trie par date décroissante
                            $nombre, // on sélectionne $nombre articles
                            0 // à partir du premier
                        );

        // affichage du menu
        return $this->render('PJMAppBundle:Actus:menu.html.twig', array(
            'liste_articles' => $articles
        ));
    }
}
