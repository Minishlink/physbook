<?php

namespace PJM\AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class IntranetExtension extends \Twig_Extension
{
    public function __construct(ContainerInterface $container = null)
    {
        if (isset($container)) {
            $this->container = $container;
        }
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('prix', array($this, 'prixFilter')),
            new \Twig_SimpleFilter('moyenPaiement', array($this, 'moyenPaiementFilter')),
            new \Twig_SimpleFilter('nombre', array($this, 'nombreFilter')),
            new \Twig_SimpleFilter('validCommande', array($this, 'validCommandeFilter')),
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecodeFilter')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'image',
                array($this, 'imageFunction'),
                array('is_safe' => array('html'))
            ),
        );
    }

    public function prixFilter($string)
    {
        $unit = "c";
        $price = (float) $string / 100; // centimes => euros
        if (strlen($string) > 2 || $string == "0") {
            $unit = "€";
            $string = number_format($price, 2, ',', ' ');
        }

        return $string.$unit;
    }

    public function nombreFilter($string)
    {
        return $string/10;
    }

    public function validCommandeFilter($string)
    {
        switch ($string) {
            case "1":
                $string = "En cours";
                break;
            case "0":
                $string = "Résiliée";
                break;
            default:
                $string = "En attente";
                break;
        }

        return $string;
    }

    public function moyenPaiementFilter($string)
    {
        $map = array(
            'smoney' => "S-Money",
            'cheque' => "Chèque",
            'monnaie' => "Monnaie"
        );

        return array_key_exists($string, $map)
            ? $map[$string]
            : $string;
    }

    public function jsonDecodeFilter($string) {
        return json_decode($string);
    }

    public function imageFunction($id, $ext, $alt = '')
    {
        $uploadDir = 'uploads/img'; // apparait dans PJM\AppBundle\Entity\Image
        $imgPath = $uploadDir.'/'.$id.'.'.$ext;
        $path = $this->container->get('templating.helper.assets')->getUrl($imgPath);
        return '<img src="'.$path.'" alt="'.$alt.'" />';
    }

    public function getName()
    {
        return 'intranet_extension';
    }
}
