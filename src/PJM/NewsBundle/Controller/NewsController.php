<?php

namespace PJM\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use PJM\NewsBundle\Entity\Article;
use PJM\NewsBundle\Entity\Image;
use PJM\NewsBundle\Entity\Commentaire;
use PJM\NewsBundle\Entity\Categorie;
use PJM\NewsBundle\Form\ArticleType;

class NewsController extends Controller
{
    public function indexAction($page)
    {
        $nbArticlesParPage = 5;

        // on récupère la liste des articles
        $articles = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('PJMNewsBundle:Article')
                        ->getArticles($nbArticlesParPage, $page);

        // on retourne le template
        return $this->render('PJMNewsBundle:News:index.html.twig', array(
            'articles'   => $articles,
            'page'       => $page,
            'nombrePages'=> ceil(count($articles)/$nbArticlesParPage)
        ));
    }


    public function voirAction(Article $article)
    {
            // on retourne le template
            return $this->render('PJMNewsBundle:News:voir.html.twig', array(
                'article' => $article
            ));
    }

    /**
    * @Secure(roles="ROLE_AUTEUR")
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

                return $this->redirect($this->generateUrl('pjm_news_voir', array('slug' => $article->getSlug())));
            }
        }

        return $this->render('PJMNewsBundle:News:ajouter.html.twig', array(
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

                return $this->redirect($this->generateUrl('pjm_news_voir', array('slug' => $article->getSlug())));
            }
        }

        // affichage du formulaire
        return $this->render('PJMNewsBundle:News:modifier.html.twig', array(
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

                return $this->redirect($this->generateUrl('pjm_news_accueil'));
            }
        }

        return $this->render('PJMNewsBundle:News:supprimer.html.twig', array(
            'article' => $article,
            'form' => $form->createView()
        ));
    }

    public function menuAction($nombre)
    {
        // on récupère la liste des articles
        $articles = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('PJMNewsBundle:Article')
                        ->findBy(
                            array('publication' => true), // pas de critère
                            array('date' => 'desc'), // on trie par date décroissante
                            $nombre, // on sélectionne $nombre articles
                            0 // à partir du premier
                        );

        // affichage du menu
        return $this->render('PJMNewsBundle:News:menu.html.twig', array(
            'liste_articles' => $articles
        ));
    }
}
