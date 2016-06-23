<?php

namespace Dada\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DadaUserBundle:Default:index.html.twig');
    }
}
