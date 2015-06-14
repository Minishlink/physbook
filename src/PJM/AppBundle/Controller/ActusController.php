<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use PJM\AppBundle\Entity\Actus\Article;
use PJM\AppBundle\Form\Type\Actus\ArticleType;

class ActusController extends Controller
{
    public function indexAction($page)
    {
        $nbArticlesParPage = 5;

        // on récupère la liste des articles
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PJMAppBundle:Actus\Article');
        $articles = $repo->getArticles($nbArticlesParPage, $page);
        $brouillons = $repo->getBrouillons($this->getUser());

        // on retourne le template
        return $this->render('PJMAppBundle:Actus:index.html.twig', array(
            'articles'   => $articles,
            'brouillons' => $brouillons,
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

    public function ajouterAction(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(new ArticleType(), $article, array(
            'method' => 'POST',
            'action' => $this->generateUrl('pjm_app_actus_ajouter'),
        ));

        $form->handleRequest($request);

         if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $article->setAuteur($this->getUser());
                $em->persist($article);
                $em->flush();

                return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
            }

            $request->getSession()->getFlashBag()->add(
                'danger',
                'Un problème est survenu lors de l\'ajout. Réessaye.'
            );
         }

        return $this->render('PJMAppBundle:Actus:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function modifierAction(Request $request, Article $article)
    {
        if ($this->getUser() == $article->getAuteur()) {
            $form = $this->createForm(new ArticleType(), $article, array(
                'method' => 'POST',
                'action' => $this->generateUrl('pjm_app_actus_modifier', array(
                    'slug' => $article->getSlug()
                )),
                'ajout' => false,
            ));

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($article);
                    $em->flush();

                    return $this->redirect($this->generateUrl('pjm_app_actus_voir', array(
                        'slug' => $article->getSlug()
                    )));
                }

                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la modification. Réessaye.'
                );
            }

            return $this->render('PJMAppBundle:Actus:modifier.html.twig', array(
                'article' => $article,
                'form' => $form->createView()
            ));
        }

        $request->getSession()->getFlashBag()->add(
            'danger',
            "Tu ne peux pas modifier cet article car tu n'en es pas l'auteur."
        );

        return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
    }

    public function supprimerAction(Request $request, Article $article)
    {
        if ($this->getUser() == $article->getAuteur() || $this->getUser()->hasRole('ROLE_ASSO_COM')) {
            $form = $this->createFormBuilder()->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($article);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');

                    return $this->redirect($this->generateUrl('pjm_app_actus_index'));
                }

                $request->getSession()->getFlashBag()->add(
                    'danger',
                    'Un problème est survenu lors de la suppression. Réessaye.'
                );
            }

            return $this->render('PJMAppBundle:Actus:supprimer.html.twig', array(
                'article' => $article,
                'form' => $form->createView()
            ));
        }

        $request->getSession()->getFlashBag()->add(
            'danger',
            "Tu ne peux pas supprimer cet article car tu n'en es pas l'auteur ou tu n'es pas ZiCom Asso."
        );

        return $this->redirect($this->generateUrl('pjm_app_actus_voir', array('slug' => $article->getSlug())));
    }

    public function extraitAction($nombre)
    {
        // on récupère la liste des articles
        $articles = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('PJMAppBundle:Actus\Article')
                        ->findBy(
                            array('publication' => true),
                            array('date' => 'desc'), // on trie par date décroissante
                            $nombre, // on sélectionne $nombre articles
                            0 // à partir du premier
                        );

        // affichage du menu
        return $this->render('PJMAppBundle:Actus:extrait.html.twig', array(
            'liste_articles' => $articles
        ));
    }
}
