<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Transaction;
use PJM\AppBundle\Entity\Compte;

class RechargementController extends Controller
{
    /**
     * @ParamConverter("boquette", options={"mapping": {"boquette_slug": "slug"}})
     */
    public function getURLAction($montant, Boquette $boquette)
    {
        // on crée une transaction pour récupérer l'ID unique
        $transaction = new Transaction();
        $transaction->setMontant($montant);
        $transaction->setBoquette($boquette);
        $transaction->setMoyenPaiement("smoney");
        $transaction->setInfos($boquette->getCaisseSMoney);
        $transaction->setUser($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        $buzz = $this->container->get('buzz');
        $curl = $buzz->getClient();
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        $authToken = $this->container->getParameter('paiement.smoney.auth');
        $urlSMoney = $this->container->getParameter('paiement.smoney.url');

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $ua_checker = array(
          'android' => preg_match('/android/', $ua),
          'ios' => preg_match('/iphone|ipod|ipad/', $ua),
        );

        $agent = "web";
        if ($ua_checker['ios']) {
            $agent = "iphoneweb";
        }

        // #FUTURE https://mon-espace.s-money.fr/ecommerce/payments/smoney
        $headers = array(
            "Authorization" => $authToken,
        );
        $content = array(
            "amount" => $montant,
            "receiver" => $boquette->getCaisseSMoney(),
            "transactionId" => substr(uniqid(), 0, 6)."_".$transaction->getId(),
            "amountEditable" => false,
            "receiverEditable" => false,
            "agent" => $agent,
            "source" => "web",
            "identifier" => "",
            "message" => "[Phy'sbook] ".$this->getUser()->getUsername()." - ".$boquette->getNom()." (".$transaction->getId().")"
        );

        $response = $buzz->post($urlSMoney, $headers, $content);

        if ($response->getStatusCode() != 200) {
            // si échec
            $resData = array(
                'valid' => false,
            );

            $this->get('session')->getFlashBag()->add(
                'warning',
                'Erreur '.$response->getStatusCode().': '.$response->getReasonPhrase()
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
                );

                $this->get('session')->getFlashBag()->add(
                    'warning',
                    'Erreur '.$data['Code'].': '.$data['ErrorMessage']
                );
            }
        }

        $res = new JsonResponse();
        $res->setData($resData);
        return $res;
    }

    public function retourSMoneyAction(Request $request)
    {
        if ($request->request->get('transactionId')) {
            $transactionId = $request->request->get('transactionId');
            $status = $request->request->get('status');
            $errorCode = $request->request->get('errorCode');

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('PJMAppBundle:Transaction');
            $transaction = $repository->findOneById(substr($transactionId, 7));

            if (isset($transaction)) {
                if (null === $transaction->getStatus()) {
                    if ($status == "OK") {
                        $repository = $em->getRepository('PJMAppBundle:Compte');
                        $compte = $repository->findOneByUserAndBoquette(
                            $transaction->getUser(),
                            $transaction->getBoquette()
                        );

                        if ($compte === null) {
                            $compte = new Compte($transaction->getUser(), $transaction->getBoquette());
                        }

                        $compte->setSolde($compte->getSolde() + $transaction->getMontant());
                        $em->persist($compte);

                        $transaction->setStatus("OK");
                    } else {
                        if ($errorCode != null) {
                            $transaction->setStatus($errorCode);
                        } else {
                            $transaction->setStatus("NOK");
                        }
                    }

                    $em->persist($transaction);
                    $em->flush();

                    return new Response('OK');
                } else {
                    return new Response('Cette transaction a deja ete traitee.', 403);
                }
            }
        }

        return new Response('Transaction inconnue', 404);
    }

    public function redirectionDepuisSMoneyAction(Request $request)
    {
        $transactionId = $request->query->get('transactionId');

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PJMAppBundle:Transaction');
        $transaction = $repository->findOneById(substr($transactionId, 7));

        if (isset($transaction)) {
            if ($this->getUser() == $transaction->getUser()) {
                if (null !== $transaction->getStatus()) {
                    if ($transaction->getStatus() == "OK") {
                        // si le paiement a été complété
                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'Tu as bien rechargé ton compte de '.$transaction->showMontant().'€.'
                        );
                    } else {
                        // si le paiement a été annulé
                        $this->get('session')->getFlashBag()->add(
                            'danger',
                            'Le rechargement de '.$transaction->showMontant().'€ n\'a pu être effectué.'
                        );

                        switch ($transaction->getStatus()) {
                            case "623":
                                $source = "Phy'sbook";
                                break;
                            case "624":
                                $source = "S-Money";
                                break;
                            case "625":
                                $source = "Utilisateur";
                                break;
                            default:
                                $source = "inconnue";
                                break;
                        }

                        $this->get('session')->getFlashBag()->add(
                            'warning',
                            'Code d\'erreur : '.$transaction->getStatus().' ('.$source.')'
                        );
                    }
                } else {
                    // si l'utilisateur n'est pas allé plus loin que la page sur S-Money
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        'Il n\'y a pas eu de suite à ta demande de rechargement de '.$transaction->showMontant().'€.'
                    );
                }

                switch ($transaction->getBoquette()->getSlug()) {
                    case 'brags':
                        $action = "pjm_app_consos_brags_index";
                        break;
                    default:
                        throw new HttpException(
                            404,
                            "La boquette concernée (".$transaction->getBoquette()->getNom().") n'a pas de page."
                        );
                        break;
                }

                return $this->redirect($this->generateUrl($action));
            } else {
                throw new HttpException(403, "Tu n'es pas l'auteur de cette transaction.");
            }
        }

        throw new HttpException(404, "La transaction n'existe pas.");
    }
}
