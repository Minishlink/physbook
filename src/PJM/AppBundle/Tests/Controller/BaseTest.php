<?php

namespace PJM\AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class BaseTest for helper functions in functional tests.
 */
abstract class BaseTest extends WebTestCase
{
    protected function createAuthenticatedClient($username = 'ancien')
    {
        return self::createClient(array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => 'test',
        ));
    }

    protected function requestWithAuthenticatedClient(Client $client, $method, $url, $username = 'conscrit', $parameters = array())
    {
        $client->request($method, $url, $parameters, array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => 'test',
        ));
    }
}
