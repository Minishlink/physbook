<?php

namespace PJM\AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

class Image
{
    private $assets;

    public function __construct(AssetsHelper $assets)
    {
        $this->assets = $assets;
    }

    public function html($id, $ext, $alt = '')
    {
        $uploadDir = 'uploads/img'; // apparait dans PJM\AppBundle\Entity\Image
        $imgPath = $uploadDir.'/'.$id.'.'.$ext;
        $path = $this->assets->getUrl($imgPath);

        return '<img src="'.$path.'" alt="'.$alt.'" />';
    }
}
