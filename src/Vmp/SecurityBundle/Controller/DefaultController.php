<?php

namespace Vmp\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/security")
     */
    public function indexAction()
    {
        return $this->render('VmpSecurityBundle:Default:index.html.twig');
    }
}
