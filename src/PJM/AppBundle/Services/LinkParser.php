<?php

namespace PJM\AppBundle\Services;

class LinkParser
{
    /**
     * Parse links
     *
     * Remplace des liens textuels par des liens HTML
     *
     * @param string $texte Texte à traiter
     *
     * @return string Texte traité
     */
    public function parse($texte)
    {
        return preg_replace_callback(
            '/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[.\!\/\\w]*))?)/i',
            function($matches) {
                return '<a href="'.$matches[0].'">'.$matches[2].substr($matches[4], 0, 10).((strlen($matches[4]) > 10) ? '(...)' : '').'</a>';
            },
            $texte
        );
    }
}
