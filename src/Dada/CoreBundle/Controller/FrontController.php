<?php

namespace Dada\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontController extends Controller
{
    public function indexAction()
    {
        return $this->render('DadaCoreBundle:Default:index.html.twig');
    }
}
