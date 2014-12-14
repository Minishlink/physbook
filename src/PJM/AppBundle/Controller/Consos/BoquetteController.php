<?php

namespace PJM\AppBundle\Controller\Consos;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BoquetteController extends Controller
{
    protected $slug;

    public function getSolde($user = null)
    {
        if (!isset($user)) {
            $user = $this->getUser();
        }

        $utils = $this->get('pjm.services.utils');
        return $utils->getSolde($user, $this->slug);
    }

    public function getBoquette()
    {
        $utils = $this->get('pjm.services.utils');
        return $utils->getBoquette($this->slug);
    }

    public function getItem($itemSlug, $valid = true)
    {
        $em = $this->getDoctrine()->getManager();
        $baguette = $em
            ->getRepository('PJMAppBundle:Item')
            ->findOneBySlugAndValid($itemSlug, $valid);

        return $baguette;
    }
}
