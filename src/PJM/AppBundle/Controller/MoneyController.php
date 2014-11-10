<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MoneyController extends Controller
{
    public function envoyerPaiementAction()
    {
        $buzz = $this->container->get('buzz');
        $url = 'https://rest.s-money.fr/commerce/payments/smoney';
        $response = $buzz->post($url, array(), array(
            "amount" => 20,
        ));

        if ($response->getStatusCode() != 200) {
            // handle failure
        }

        $content = $response->getContent();
        $data = json_decode($content);

        return $this->render('PJMAppBundle:Money:index.html.twig', array(
            'response' => $data
        ));
    }
}
