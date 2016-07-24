<?php

namespace Dada\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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

    /**
     * Index of administration
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function adminAction(){
        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category');
        $nbCateg = $repo->findAll();

        return $this->render('DadaCoreBundle:Admin:index.html.twig', array('nb_categ' => count($nbCateg), 'categ' => $nbCateg));
    }
}
