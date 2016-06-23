<?php

namespace Dada\AdvertisementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DadaAdvertisementBundle:Default:index.html.twig');
    }
}
