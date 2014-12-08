<?php

namespace PJM\AppBundle\Services;

use PJM\UserBundle\Entity\User;
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

        $from = $this->parameters['notificationEmail'];
        $to = $user->getEmail();

        $this->sendMessage($template, $context, $from, $to);
    }

    /**
    * @param string $templateName
    * @param array $context
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
