<?php

namespace PJM\AppBundle\Twig;

use HTMLPurifier;
use PJM\AppBundle\Twig\CitationExtension;
use PJM\AppBundle\Services\LinkParser;

class ShowExtension extends \Twig_Extension
{
    private $citation;
    private $purifier;
    private $linkParser;

    public function __construct(CitationExtension $citation, LinkParser $linkParser, HTMLPurifier $purifier)
    {
        $this->citation = $citation;
        $this->purifier = $purifier;
        $this->linkParser = $linkParser;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('show', array($this, 'showFilter'), array(
                'needs_environment' => true,
                'is_safe' => array('html'),
            )),
        );
    }

    /**
     * Wrappe le parser de nom d'utilisateur, de liens, d'emojis et applique le HTMLPurifier
     *
     * @param string $texte Texte à traiter
     *
     * @return string Texte traité
     */
    public function showFilter(\Twig_Environment $twig, $texte)
    {
        // TODO emojis

        return $this->citation->citationUsersFilter(
            $twig,
            $this->purifier->purify(
                nl2br(
                    $this->linkParser->parse($texte)
                )
            )
        );
    }

    public function getName()
    {
        return 'show_extension';
    }
}
