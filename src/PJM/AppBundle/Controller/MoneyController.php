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

        $url = '***REMOVED***';
        // #FUTURE ***REMOVED***
        $headers = array(
            "Authorization" => "Bearer ***REMOVED***",
        );
        $content = array(
            "amount" => 1,
            "receiver" => "aeensambordeaux",
            "transactionId" => "0",
            "amountEditable" => true,
            "receiverEditable" => true,
            "agent" => "other",
            "source" => "web",
            "identifier" => ""
        );

        $response = $buzz->post($url, $headers, $content);
        //$response = $buzz->submit($url, $content, "POST", $headers);

        if ($response->getStatusCode() != 200) {
            $erreur = array(
                'niveau' => 'error',
                'code' => $response->getStatusCode(),
                'contenu' => $response->getReasonPhrase()
            );
        }

        $data = json_decode($response->getContent(), true);

        $erreur = array(
            'niveau' => 'warning',
            'code' => $data['Code'],
            'contenu' => $data['ErrorMessage']
        );

        return $this->render('PJMAppBundle:Money:index.html.twig', array(
            'response' => $data,
            'requete' => $buzz->getLastRequest(),
            'erreur' => isset($erreur) ? $erreur : null
        ));
    }
}
