<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use PJM\UserBundle\Entity\User;

class Citation
{
    protected $em;
    protected $templating;

    public function __construct(EntityManager $em, $templating)
    {
        $this->em = $em;
        $this->templating = $templating;
    }

    /**
     * Remplace dans un texte les @username par un lien cliquable
     * @param  string  $texte Texte où regarder
     * @return integer Nombre d'occurences remplacées
     */
    public function parseCitationUsers($texte, &$count = null)
    {
        $count = 0;

        preg_match_all("/@[a-zA-Z0-9\-]+[bo|li|an|me|ch|cl|ai|ka|pa][0-9]+/", $texte, $usernames);

        $repo_user = $this->em->getRepository('PJMUserBundle:User');

        if ($usernames !== null) {
            $usersOK = array();
            foreach($usernames[0] as $username) {
                $username = substr($username, 1);
                $user = $repo_user->findOneByUsername($username);
                if ($user !== null) {
                    if(!in_array($username, $usersOK)) {
                        $view = $this->templating->render('PJMAppBundle:Profil:encart.html.twig', array(
                            'user' => $user,
                            'citation' => true
                        ));
                        $texte = str_replace("@".$username, $view, $texte, $count);

                        $usersOK[] = $username;
                    }
                }
            }
        }

        return $texte;
    }

    public function parseArticles($articles)
    {
        foreach ($articles as &$article) {
            $count = 0;
            $contenu = $this->parseCitationUsers($article->getContenu(), $count);
            if ($count > 0) {
                $article->setContenu($contenu);
            }
        }

        return $articles;
    }

    public function parseArticle(\PJM\AppBundle\Entity\Actus\Article $article)
    {
        $articles = $this->parseArticles(array($article));
        return $articles[0];
    }
}
