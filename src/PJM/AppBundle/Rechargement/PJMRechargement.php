<?php

namespace PJM\AppBundle\Rechargement;

class PJMRechargement
{
    private $buzz;
    private $url;
    private $auth;

    public function __construct($buzz, $url, $auth)
    {
        $this->buzz = $buzz;
        $this->url = $url;
        $this->auth = $auth;
    }

    public function rechargerSMoney($montant)
    {
        $buzz = $this->buzz;
        $curl = $buzz->getClient();
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        // #FUTURE ***REMOVED***
        $headers = array(
            "Authorization" => $this->auth,
        );
        $content = array(
            "amount" => $montant,
            "receiver" => "louis.lagrange",
            "transactionId" => "0",
            "amountEditable" => false,
            "receiverEditable" => false,
            "agent" => "other",
            "source" => "web",
            "identifier" => ""
        );

        $response = $buzz->post($this->url, $headers, $content);

        if ($response->getStatusCode() != 200) {
            return array(false, array(
                'niveau' => 'warning',
                'contenu' => 'Erreur '.$response->getStatusCode().': '.$response->getReasonPhrase()
            ));
        }

        $data = json_decode($response->getContent(), true);

        $message = array(
            'niveau' => 'warning',
            'contenu' => 'Erreur '.$data['Code'].': '.$data['ErrorMessage']
        );

        return array(false, $message);
    }
}
