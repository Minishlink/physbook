<?php

namespace PJM\AppBundle\Services\Payments;

use Doctrine\ORM\EntityManager;
use Buzz\Browser;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Services\Consos\TransactionManager;

class LydiaProvider
{
    /** @var EntityManager */
    private $em;

    /** @var Browser */
    private $buzz;

    /** @var TransactionManager */
    private $transactionManager;

    private $url;
    private $vendorToken;
    private $providerToken;

    public function __construct(EntityManager $em, Browser $buzz, TransactionManager $transactionManager, $url, $providerToken, $vendorToken)
    {
        $this->em = $em;
        $this->buzz = $buzz;
        $this->transactionManager = $transactionManager;
        $this->url = $url;
        $this->providerToken = $providerToken;
        $this->vendorToken = $vendorToken;

        $this->buzz->getClient()->setTimeout(30);
    }

    public function requestRemote(Transaction $transaction, array $callbacks)
    {
        $endpoint = $this->url."/api/request/do.json";

        // persist the $transaction to store ID
        $this->transactionManager->persist($transaction, true);

        $boquette = $transaction->getCompte()->getBoquette();
        $user = $transaction->getCompte()->getUser();

        $content = array(
            'vendor_token' => $this->vendorToken,
            //'provider_token' => $this->providerToken, // ?? optional ??
            'recipient' => $user->getEmail(),
            'type' => 'email',
            'message' => "[Phy'sbook] ".$boquette->getNom().' - '.$user->getUsername(),
            'amount' => $transaction->getMontant()/100,
            'currency' => 'EUR',
            'expire_time' => 300,
            'confirm_url' => $callbacks['confirm_url'],
            'cancel_url' => $callbacks['cancel_url'],
            'expire_url' => $callbacks['expire_url'],
            'browser_success_url' => $callbacks['browser_success_url'],
            'browser_fail_url' => $callbacks['browser_fail_url'],
            'payment_recipient' => $boquette->getNom(),
            'request_recipient' => $user->getUsername(),
            'notify' => 'yes',
            'notify_collector' => 'no',
            'order_ref' => substr(uniqid(), 0, 6).'_'.$transaction->getId(),
        );

        $response = $this->buzz->post($endpoint, array(), $content);

        if ($response->getStatusCode() != 200) {
            return array(
                'success' => false,
                'errorCode' => $response->getStatusCode(),
                'errorMessage' => $response->getReasonPhrase()
            );
        }

        $content = json_decode($response->getContent(), true);

        if (array_key_exists('error', $content)) {
            $error = $content['error'];
            if ($error > 0) {
                return array(
                    'success' => false,
                    'errorCode' => $content['message'],
                    'errorMessage' => $content['message']
                );
            }
        }

        return array(
            'success' => true,
            'url' => $content['mobile_url']
        );
    }

    private function getCallSignature($params)
    {
        ksort($params); // Tri par ordre alphabétique sur le nom des paramètres.

        $sig = array();
        foreach ($params as $key => $val) {
            $sig[] .= $key.'='.$val;
        }

        return md5(implode('&', $sig).'&'.$this->providerToken);
    }
}