<?php

namespace PJM\AppBundle\Services;

class EmoticonParser
{
    /**
     * Parse emoticons
     *
     * Detect emoticons and surround by a class : emoticons will be rendered in CSS
     *
     * @param string $text Text to analyze
     *
     * @return string Replaced text
     */
    public function parse($text)
    {
        return preg_replace_callback(
            '/([\s\0]+)(>?[:=;x8][-^]?[pdo3c<>#)(|][cv|]?)/i',
            function($matches) {
                return $matches[1].'<span class="css-emoticon'.((strlen($matches[2]) == 2) ? ' spaced-emoticon' : '').'">'.$matches[2].'</span>';
            },
            $text
        );
    }
}
