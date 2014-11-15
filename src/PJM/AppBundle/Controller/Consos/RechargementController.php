<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use PJM\AppBundle\Entity\Transaction;

class RechargementController extends Controller
{
    public function getURLAction($montant, $caisseSMoney, $boquette)
    {
        // on crée une transaction pour récupérer l'ID unique
        $transaction = new Transaction($montant, $caisseSMoney, $boquette, $this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        $buzz = $this->container->get('buzz');
        $curl = $buzz->getClient();
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        $authToken = $this->container->getParameter('paiement.smoney.auth');
        $urlSMoney = $this->container->getParameter('paiement.smoney.url');

        // #FUTURE ***REMOVED***
        $headers = array(
            "Authorization" => $authToken,
        );
        $content = array(
            "amount" => $montant,
            "receiver" => $caisseSMoney,
            "transactionId" => "a_".$transaction->getId(),
            "amountEditable" => false,
            "receiverEditable" => false,
            "agent" => "web",
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

    public function retourSMoneyAction(Request $request)
    {

    }

    public function redirectionDepuisSMoneyAction($transactionId)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PJMAppBundle:Transaction');
        $transaction = $repository->findOneById(substr($transactionId, 2));

        if (isset($transaction)) {
            if ($this->getUser() == $transaction->getUser()) {
                if (null !== $transaction->getValide()) {
                    if ($transaction->getValide() === true) {
                        // si le paiement a été complété
                        $messages[] = array(
                            'niveau' => 'success',
                            'contenu' => 'Tu as bien rechargé ton compte de '.$transaction->showMontant().'€.'
                        );
                    } else {
                        // si le paiement a été annulé
                        $messages[] = array(
                            'niveau' => 'warning',
                            'contenu' => 'Le rechargement de '.$transaction->showMontant().'€ n\'a pu être effectué.'
                        );
                    }
                } else {
                    // si l'utilisateur n'est pas allé plus loin que la page sur S-Money
                    $messages[] = array(
                        'niveau' => 'info',
                        'contenu' => 'Il n\'y a pas eu de suite à ta demande de rechargement de '.$transaction->showMontant().'€.'
                    );
                }

                switch ($transaction->getBoquette()) {
                    case 'brags':
                        $action = 'PJMAppBundle:Consos/Brags:index';
                        break;
                    default:
                        throw new HttpException(
                            404,
                            "La boquette concernée (".$transaction->getBoquette().") n'a pas de page."
                        );
                        break;
                }

                return $this->forward($action, array(
                    'messages' => $messages
                ));
            } else {
                throw new HttpException(403, "Tu n'es pas l'auteur de cette transaction.");
            }
        }

        throw new HttpException(404, "La transaction n'existe pas.");
    }
}
