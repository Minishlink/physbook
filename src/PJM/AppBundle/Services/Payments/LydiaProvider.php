<?php

namespace PJM\AppBundle\Services\Payments;

use Buzz\Browser;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Services\Consos\TransactionManager;
use Symfony\Component\HttpFoundation\Request;

class LydiaProvider
{
    /** @var Browser */
    private $buzz;

    /** @var TransactionManager */
    private $transactionManager;

    private $url;
    private $auth;

    public function __construct(Browser $buzz, TransactionManager $transactionManager, $url, array $auth)
    {
        $this->buzz = $buzz;
        $this->transactionManager = $transactionManager;
        $this->url = $url;
        $this->auth = $auth;

        $this->buzz->getClient()->setTimeout(30);
    }

    /**
     * @param Transaction $transaction
     * @param array $callbacks
     * @return array
     */
    public function requestRemote(Transaction $transaction, array $callbacks)
    {
        $endpoint = $this->url."/api/request/do.json";

        // persist the $transaction to store ID
        $transaction->setMoyenPaiement('lydia');
        $this->transactionManager->persist($transaction, true);

        $boquette = $transaction->getCompte()->getBoquette();
        $user = $transaction->getCompte()->getUser();

        $content = array(
            'vendor_token' => $this->getPublicVendorToken($boquette->getSlug()),
            'provider_token' => $this->auth['provider_token'], // ?? optional ??
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

        // from Lydia doc
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

        // from experimentation
        if (array_key_exists('status', $content)) {
            if($content['status'] === "error") {
                return array(
                    'success' => false,
                    'errorCode' => $content['code'],
                    'errorMessage' => $content['message']
                );
            }
        }

        return array(
            'success' => true,
            'url' => $content['mobile_url']
        );
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function confirmPayment(Request $request)
    {
        $transaction = $this->getTransactionFromRequest($request);
        if (!$transaction) {
            return false;
        }

        if ($transaction->getStatus() !== null) {
            // if already processed
            return false;
        }

        $transaction->setStatus('OK');
        $this->transactionManager->traiter($transaction);

        return true;
    }

    /**
     * @param Request $request
     * @param string $status
     * @return bool
     */
    public function cancelPayment(Request $request, $status)
    {
        $transaction = $this->getTransactionFromRequest($request);
        if (!$transaction) {
            return false;
        }

        $transaction->setStatus($status);
        $this->transactionManager->persist($transaction, true);

        return true;
    }

    /**
     * Get public vendor token (vendor_token) from Boquette slug
     *
     * @param $slug
     * @return mixed
     */
    private function getPublicVendorToken($slug)
    {
        switch($slug) {
            case 'pians':
                $auth = $this->auth['pians'];
                break;
            default:
                $auth = $this->auth['vie_courante'];
                break;
        }

        return $auth['public_token'];
    }

    /**
     * Get private vendor token (token_api) from public vendor token (vendor_token)
     * @param $publicVendorToken
     * @return bool
     */
    private function getPrivateVendorToken($publicVendorToken)
    {
        foreach ($this->auth as $type) {
            if (!(is_array($type) && array_key_exists('private_token', $type) && array_key_exists('public_token', $type)))
                continue;

            if ($type['public_token'] === $publicVendorToken)
                return $type['private_token'];
        }

        return false;
    }

    /**
     * @param Request $request
     * @return bool|Transaction
     */
    private function getTransactionFromRequest(Request $request)
    {
        $params = $this->getParamsCallback($request);
        if (!$params) {
            return false;
        }

        $transactionId = substr($params['order_ref'], 7);
        $transaction = $this->transactionManager->getById($transactionId);

        if (!($transaction instanceof Transaction)) {
            return false;
        }

        return $transaction;
    }

    private function getParamsCallback(Request $request)
    {
        $params = $request->request->all();
        $sig = $params['sig'];
        unset($params['sig']);

        if ($this->getCallSignature($params) !== $sig) {
            return false;
        }

        return $params;
    }

    /**
     * From Lydia API documentation v1.9.9
     *
     * @param array $params Every posted paramater of the request without signature 'sig'
     * @return string
     */
    private function getCallSignature(array $params)
    {
        ksort($params); // Tri par ordre alphabétique sur le nom des paramètres.

        $sig = array();
        foreach ($params as $key => $val) {
            $sig[] .= $key.'='.$val;
        }

        return md5(implode('&', $sig).'&'.$this->getPrivateVendorToken($params['vendor_token']));
    }
}
