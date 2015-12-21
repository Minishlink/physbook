<?php

namespace PJM\AppBundle\Services\Actus;

use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Actus\Article;
use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Services\Group;
use PJM\AppBundle\Services\NotificationManager;

class ArticleManager
{
    private $em;
    private $notificationManager;
    private $groupService;

    public $nbArticlesParPage = 5;

    public function __construct(EntityManager $em, NotificationManager $notificationManager, Group $groupService)
    {
        $this->em = $em;
        $this->notificationManager = $notificationManager;
        $this->groupService = $groupService;
    }

    /**
     * @param User $user
     *
     * @return Article
     */
    public function create(User $user)
    {
        $article = new Article();
        $article->setAuteur($user);

        return $article;
    }

    /**
     * @param $page
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getByPage($page)
    {
        return $this->em->getRepository('PJMAppBundle:Actus\Article')->getArticles($this->nbArticlesParPage, $page);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getDrafts(User $user)
    {
        return $this->em->getRepository('PJMAppBundle:Actus\Article')->getBrouillons($user);
    }

    /**
     * @param $count
     *
     * @return array|\PJM\AppBundle\Entity\Actus\Article[]
     */
    public function getSome($count)
    {
        return $this->em->getRepository('PJMAppBundle:Actus\Article')->findBy(
            array('publication' => true),
            array('date' => 'desc'), // on trie par date décroissante
            $count, // on sélectionne $count articles
            0 // à partir du premier
        );
    }

    /**
     * @param Article    $article
     * @param bool|false $new     Is the article not yet published ?
     */
    public function update(Article $article, $new = false)
    {
        $this->em->persist($article);
        $this->em->flush();

        if ($new && $article->getPublication()) {
            // pour l'instant on notifie juste les gens des promos n-1, n, n+1
            $this->notificationManager->send('actus.nouvelle', array(
                'titre' => $article->getTitre(),
                'auteur' => $article->getAuteur()->getBucque(),
            ), $this->groupService->getUsersAuTabagns());
        }
    }

    /**
     * @param Article $article
     */
    public function remove(Article $article)
    {
        $this->em->remove($article);
        $this->em->flush();
    }

    public function canEdit(User $user, Article $article)
    {
        return $article->getAuteur() === $user;
    }

    public function canDelete(User $user, Article $article)
    {
        return $article->getAuteur() === $user || $user->hasRole('ROLE_ASSO_COM');
    }
}
