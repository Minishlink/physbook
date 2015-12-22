<?php

namespace PJM\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PJM\AppBundle\Entity\Boquette;

class LoadBoquetteData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadBoquette($manager, "Phy'sbook", 'physbook', null, 'rouge');
        $this->loadBoquette($manager, "Pian's", 'pians', 'aeensampian', 'orange', array('Pians', 'Pian\'s', 'Pian\'ss', 'Pian\'sss', 'Pi'));
        $this->loadBoquette($manager, "C'vis", 'cvis', 'aeensampian', 'rouge', array('Cvis', 'Cvi\'s', 'Cvi\'ss', 'Cvi\'sss'));
        $this->loadBoquette($manager, "Brag's", 'brags', 'aeensambrags', 'jaune');
        $this->loadBoquette($manager, 'Paniers de fruits et légumes', 'paniers', 'aeensampanier', 'vert');
        $this->loadBoquette($manager, 'AMJE Bordeaux', 'amje-bordeaux', null, 'blanc');
        $this->loadBoquette($manager, 'Asso', 'asso', null, 'bleu');
        $this->loadBoquette($manager, 'UAI', 'uai', null, 'rose', array('Gymnase'));
        $this->loadBoquette($manager, 'CdF', 'cdf', null, 'jaune');
    }

    private function loadBoquette(ObjectManager $manager, $nom, $slug, $caisse, $couleur, $lieux = null)
    {
        $boquette = new Boquette();
        $boquette->setNom($nom);
        $boquette->setSlug($slug);
        $boquette->setCaisseSMoney($caisse);
        $boquette->setCouleur($couleur);
        $boquette->setLieux($lieux);

        $manager->persist($boquette);
        $manager->flush();

        $this->addReference($slug.'-boquette', $boquette);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
