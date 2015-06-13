<?php

namespace PJM\AppBundle\Twig;

use Doctrine\ORM\EntityManager;

class CitationExtension extends \Twig_Extension
{
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('citationUsers', array($this, 'citationUsersFilter'), array(
                'needs_environment' => true
            ))
        );
    }

    /**
     * Remplace dans un texte les @username par un lien cliquable
     * @param  string  $texte Texte où regarder
     * @return integer Nombre d'occurences remplacées
     */
    public function citationUsersFilter(\Twig_Environment $twig, $texte)
    {
        preg_match_all("/@[a-zA-Z0-9\-!#]+[bo|li|an|me|ch|cl|ai|ka|pa][0-9]+/", $texte, $usernames);

        $repo_user = $this->em->getRepository('PJMUserBundle:User');

        if ($usernames !== null) {
            $usersOK = array();
            foreach($usernames[0] as $username) {
                $username = substr($username, 1);
                $user = $repo_user->findOneByUsername($username);
                if ($user !== null) {
                    if(!in_array($username, $usersOK)) {
                        $view = $twig->render('PJMAppBundle:Profil:encart.html.twig', array(
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

    public function getName()
    {
        return 'citation_extension';
    }
}
