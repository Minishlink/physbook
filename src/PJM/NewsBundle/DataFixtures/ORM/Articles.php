<?php

namespace PJM\NewsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\NewsBundle\Entity\Article;
use PJM\NewsBundle\Entity\Commentaire;
use PJM\NewsBundle\Entity\Categorie;

class Articles implements FixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager)
    {
        $article = new Article();
        $article->setTitre('AMJE Bordeaux rocks');
        $article->setContenu('Et ouai mon gars !');
        $article->setAuteur('Louis');
        $article->setPublication(true);
        $com = new Commentaire();
        $com->setAuteur('Toto');
        $com->setContenu('Hey !');
        $article->addCommentaire($com);
        $manager->persist($com);
        $manager->persist($article);

        $article = new Article();
        $article->setTitre('Trop bien notre PJM');
        $article->setContenu('Hein hein ?');
        $article->setAuteur('Louis');
        $article->setPublication(true);
        $cat = new Categorie();
        $cat->setNom('PJM');
        $article->addCategory($cat);
        $manager->persist($article);

        $article = new Article();
        $article->setTitre('This is the end');
        $article->setContenu('of the world as we know it.');
        $article->setAuteur('Louis');
        $article->setPublication(false);
        $manager->persist($article);

        // On déclenche l'enregistrement
        $manager->flush();
    }
}
