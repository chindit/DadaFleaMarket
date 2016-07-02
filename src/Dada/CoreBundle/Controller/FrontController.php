<?php

namespace Dada\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontController extends Controller
{
    /**
     * Front controller.  Shows 6 last adverts
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(){
        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $adverts = $repo->findLast(6);
        return $this->render('DadaCoreBundle:Default:index.html.twig', array('adverts' => $adverts));
    }
}
