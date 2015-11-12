<?php

namespace PJM\AppBundle\Services;

use PJM\AppBundle\Entity\User;
use PJM\AppBundle\Entity\Compte;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $parameters;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    public function sendAlerteSolde(Compte $compte)
    {
        $user = $compte->getUser();
        $context = array(
            'user' => $user,
            'boquette' => $compte->getBoquette(),
            'dette' => -$compte->getSolde(),
        );

        $this->sendNotification($user, $context, $this->parameters['template']['alerteSolde']);
    }

    protected function sendNotification(User $user, $context, $template = null)
    {
        if (!isset($template)) {
            $template = $this->parameters['template']['layout'];
        }

        $from = array(
            $this->parameters['notificationEmail'] => $this->parameters['notificationSender'],
        );
        $to = array(
            $user->getEmail() => $user,
        );

        $this->sendMessage($template, $context, $from, $to);
    }

    /**
     * Envoie un mail de notification@physbook.fr.
     *
     * @param object User $user    L'utilisateur inscrit
     * @param array       $context Un tableau des variables utilisées dans le template
     * @param string [$template = null] Un template autre que le layout par défaut
     */
    public function send(User $user, $context, $template = null)
    {
        if (!isset($template)) {
            $template = $this->parameters['template']['layout'];
        }

        $from = array(
            $this->parameters['notificationEmail'] => $this->parameters['notificationSender'],
        );
        $to = array(
            $user->getEmail() => $user->getPrenom().' '.$user->getNom(),
        );

        $this->sendMessage($template, $context, $from, $to);
    }

    /**
     * Envoie un simple $message de notification@physbook.fr à $mail.
     */
    public function sendMessageToEmail($message, $email)
    {
        $template = $this->parameters['template']['message'];

        $from = array(
            $this->parameters['notificationEmail'] => $this->parameters['notificationSender'],
        );

        $to = array(
            $email => $email,
        );

        $this->sendMessage($template, array('message' => $message), $from, $to);
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
            ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }
}
