<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Actus\Article;

class LoadArticleData extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $articles = array(
            array(
                'auteur' => 'ancien',
                'titre' => 'L\'homme moderne face à un défi majeur : les tâches ménagères',
                'contenu' => '',
                'publication' => true,
            ),
            array(
                'auteur' => 'ancien',
                'titre' => 'Je ne sais pas encore mon titre',
                'contenu' => '',
                'publication' => false,
            ),
            array(
                'auteur' => 'p3',
                'titre' => 'Le rôle des pingouins dans le krach de 1929',
                'contenu' => '',
                'publication' => true,
            ),
        );

        foreach ($articles as $article) {
            $this->loadArticle($manager, $article['auteur'], $article['titre'], $article['contenu'], $article['publication']);
        }

        $manager->flush();
    }

    private function loadArticle(ObjectManager $manager, $auteur, $titre, $contenu, $publication)
    {
        if (empty($contenu)) {
            $contenu = $this->getLoremIpsum(rand(1, 4), 'paragraphs');
        }

        $article = new Article();
        $article->setAuteur($this->getUser($auteur));
        $article->setTitre($titre);
        $article->setContenu($contenu);
        $article->setPublication($publication);
        $article->setDate($this->getRandomDateAgo(5, 30));

        if ($publication) {
            $article->setDateEdition($this->getRandomDateAgo(0, 5));
        }

        $manager->persist($article);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
