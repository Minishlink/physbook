<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\JsonResponse;

class RechargementController extends Controller
{
    public function getURLAction($montant, $compte)
    {
        // TODO créer transaction pour avoir transactionId unique

        $buzz = $this->container->get('buzz');
        $curl = $buzz->getClient();
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        $authToken = $this->container->getParameter('paiement.smoney.auth');
        $urlSMoney = $this->container->getParameter('paiement.smoney.url');

        // #FUTURE https://mon-espace.s-money.fr/ecommerce/payments/smoney
        $headers = array(
            "Authorization" => $authToken,
        );
        $content = array(
            "amount" => $montant,
            "receiver" => "aeensambordeaux",
            "transactionId" => "9",
            "amountEditable" => false,
            "receiverEditable" => false,
            "agent" => "other",
            "source" => "web",
            "identifier" => "",
            "message" => "[Physbook] Brags"
        );

        $response = $buzz->post($urlSMoney, $headers, $content);

        if ($response->getStatusCode() != 200) {
            // si échec
            $resData = array(
                'valid' => false,
                'message' => array(
                    'niveau' => 'warning',
                    'contenu' => 'Erreur '.$response->getStatusCode().': '.$response->getReasonPhrase()
                ),
            );
        } else {
            // si on a une réponse valide de la part de S-Money
            $data = json_decode($response->getContent(), true);

            if (isset($data['url'])) {
                $resData = array(
                    'valid' => true,
                    'url' => $data['url']
                );
            } else {
                $resData = array(
                    'valid' => false,
                    'message' => array(
                        'niveau' => 'warning',
                        'contenu' => 'Erreur '.$data['Code'].': '.$data['ErrorMessage']
                    )
                );
            }
        }

        $res = new JsonResponse();
        $res->setData($resData);
        return $res;
    }
}
