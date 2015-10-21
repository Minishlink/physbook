<?php
/**
 * Created by PhpStorm.
 * User: Louis
 * Date: 21/10/2015
 * Time: 21:03
 */

namespace PJM\AppBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class BaseTest extends WebTestCase
{
    protected function createAuthenticatedClient($username = 'ancien')
    {
        return self::createClient(array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW'   => 'test',
        ));
    }

    protected function requestWithAuthenticatedClient(Client $client, $method, $url, $username = 'conscrit', $parameters = array())
    {
        $client->request($method, $url, $parameters, array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW'   => 'test',
        ));
    }
}
