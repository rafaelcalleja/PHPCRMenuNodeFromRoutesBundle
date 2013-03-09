<?php

namespace RC\PHPCRMenuNodeFromRoutesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('RCPHPCRMenuNodeFromRoutesBundle:Default:index.html.twig', array('name' => $name));
    }
}
