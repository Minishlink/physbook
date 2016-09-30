<?php

namespace PJM\AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLogin()
    {
        $client = self::createClient();
        $client->request('GET', '/');

        // Make sure we are redirected to the login page
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));

        $client->followRedirect();

        // Submit login form
        $form = $client->getCrawler()->selectButton('Connexion')->form();
        $client->submit($form, array(
            '_username' => 'ancien',
            '_password' => 'test',
        ));

        // Login is successful
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/'));

        $client->followRedirect();

        // On the homepage
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        return $client;
    }
}
