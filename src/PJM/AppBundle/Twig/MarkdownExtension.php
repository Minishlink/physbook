<?php

namespace PJM\AppBundle\Twig;

use PJM\AppBundle\Services\Markdown;

class MarkdownExtension extends \Twig_Extension
{
    public function __construct(Markdown $markdown)
    {
        $this->markdown = $markdown;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'md2html',
                array($this, 'markdownToHtml'),
                array('is_safe' => array('html'))
            ),
        );
    }

    public function markdownToHtml($string)
    {
        return $this->markdown->toHtml($string);
    }

    public function getName()
    {
        return 'markdown_extension';
    }
}
