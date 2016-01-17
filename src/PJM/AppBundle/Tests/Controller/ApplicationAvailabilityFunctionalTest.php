<?php

namespace PJM\AppBundle\Tests\Controller;

class ApplicationAvailabilityFunctionalTest extends BaseTest
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    private static $client;


    public static function setUpBeforeClass()
    {
        self::$client = self::createAuthenticatedClient();
    }

    /**
     * @dataProvider urlProvider
     *
     * @param $url
     */
    public function testPageIsSuccessful($url)
    {
        self::$client->request('GET', $url);
        $this->assertTrue(self::$client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return array(
            array('/'),
            array('/actus'),
            array('/actus/ajouter'),
            array('/actus/extrait'),
            array('/admin/'),
            array('/admin/users/inscription/liste'),
            array('/admin/users/inscription/unique'),
            array('/admin/responsabilites'),
            array('/admin/gestionBoquettes'),
            array('/admin/boquette/brags/'),
            array('/admin/boquette/brags/gestionItem'),
            array('/admin/boquette/brags/responsables'),
            array('/admin/boquette/brags/featuredItem'),
            array('/admin/boquette/brags/gestionCredits'),
            array('/admin/boquette/brags/achats'),
            array('/admin/boquette/brags/comptes'),
            array('/admin/boquette/brags/listeCommandes'),
            array('/admin/boquette/brags/listeVacances'),
            array('/admin/boquette/brags/listePrix'),
            array('/admin/boquette/cvis/'),
            array('/admin/boquette/paniers/'),
            array('/admin/boquette/paniers/gestionPaniers'),
            array('/admin/boquette/pians/'),
            array('/admin/media/photos/gestion'),
            array('/banque/'),
            array('/banque/transfert'),
            array('/boquette/asso/'),
            array('/boquette/brags/'),
            array('/boquette/brags/listeItem'),
            array('/boquette/brags/responsables'),
            array('/boquette/brags/responsables/historique'),
            array('/boquette/brags/rechargement'),
            array('/boquette/brags/commande'),
            array('/boquette/cvis/'),
            array('/boquette/paniers/'),
            array('/boquette/pians/'),
            array('/boquette/tuiss/'),
            array('/boquette/uai/'),
            array('/event'),
            array('/event/elections'),
            array('/event/nouveau'),
            array('/event/calendrier'),
            array('/media/'),
            array('/media/bonjourGadzart'),
            array('/notifications/'),
            array('/notifications/reglages'),
            array('/profil/'),
            array('/profil/annuaire'),
            array('/profil/modifier'),
            array('/profil/changer/photo'),
            array('/profil/change-password'),
            array('/profil/voir/ancien'),
            array('/profil/encart/ancien'),
            array('/profil/encart/ancien/1'),
            array('/tutos/'),
            array('/logo'),
            array('/a-propos'),
            array('/contact'),
            array('/support-technique'),
            array('/ecole'),
            array('/plan-du-site'),
        );
    }
}
