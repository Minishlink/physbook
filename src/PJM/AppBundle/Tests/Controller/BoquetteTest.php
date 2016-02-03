<?php

namespace Controller;

use PJM\AppBundle\Tests\Controller\BaseTest;

class BoquetteTest extends BaseTest
{
    /**
     * @dataProvider accessAdminProvider
     *
     * @param string $boquette
     * @param string $login
     * @param bool $access
     */
    public function testAccessAdmin($boquette, $login, $access)
    {
        $client = self::createAuthenticatedClient($login);
        $client->followRedirects(); // in order to handle special boquettes
        $client->request('GET', '/boquette/'.$boquette);

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals($access, count($client->getCrawler()->selectLink('Gestion')));

        $client->followRedirects(false); // catch 403
        $client->request('GET', '/admin/boquette/'.$boquette);
        $this->assertEquals($access, $client->getResponse()->isSuccessful());
    }

    public function accessAdminProvider()
    {
        return array(
            array('physbook', 'ancien', true), // la resp. ZiPhy'sbook n'existe pas mais ancien a ROLE_ADMIN
            array('physbook', 'p3', false), // la resp. ZiPhy'sbook n'existe pas
            array('cdf', 'p3', true), // p3 est ZiCdF
            array('cdf', 'archi', false), // archi n'est plus ViZiCdF
            array('pians', 'ancienne', true), // ancienne est ZiPian's
            array('cvis', 'ancienne', true), // ancienne est ZiCvi's
            array('brags', 'ancienne', true), // ancienne est ZiBrag's
            array('paniers', 'ancienne', true), // ancienne est ZiPaniers
            array('brags', 'p3', false), // p3 n'est plus ZiBrag's
            array('pians', 'conscrit', false), // conscrit n'est pas ZiPian's
            array('cvis', 'conscrit', false), // conscrit n'est pas ZiC'vis
            array('brags', 'conscrit', false), // conscrit n'est pas ZiBrag's
            array('paniers', 'conscrit', false), // conscrit n'est pas ZiPaniers
        );
    }
}
