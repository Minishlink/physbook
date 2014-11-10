<?php

namespace PJM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MoneyController extends Controller
{
    public function envoyerPaiementAction()
    {
        $buzz = $this->container->get('buzz');
        $curl = $buzz->getClient();
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        $url = 'https://rest-pp.s-money.fr/commerce/payments/smoney';
        // #FUTURE https://rest.s-money.fr/commerce/payments/smoney
        $headers = array(
            "Authorization" => "Bearer MTsxMztUR3F4ZUZwdDFs",
        );
        $content = array(
            "amount" => 0,
            "receiver" => "aeensambordeaux",
            "transactionId" => "0",
            "amountEditable" => true,
            "receiverEditable" => true,
            "agent" => "other",
            "source" => "web",
            "identifier" => ""
        );

        // post(url, headers, content)
        $response = $buzz->post($url, $headers, $content);

        if ($response->getStatusCode() != 200) {
            // échec
        }

        $content = $response->getContent();
        $data = json_decode($content);

        return $this->render('PJMAppBundle:Money:index.html.twig', array(
            'response' => $response,
            'requete' => $buzz->getLastRequest()
        ));
    }
}
