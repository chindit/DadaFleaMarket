<?php

namespace Dada\AdvertisementBundle\Controller;

use Dada\AdvertisementBundle\Entity\Advertisement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dada\AdvertisementBundle\Form\AdvertisementType;
use Symfony\Component\HttpFoundation\Request;

class AdvertController extends Controller
{
    public function addAction(Request $request)
    {
        $advert = new Advertisement();
        $form = $this->createForm(AdvertisementType::class, $advert);

        //Verify form validation
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();
            dump($advert);
        }

        return $this->render('DadaAdvertisementBundle::add.html.twig', array('form' => $form->createView(), 'ajaxUrl' => $this->get('router')->generate('dada_ajax_city_from_coords', array('latitude' => 'unikey-lat', 'longitude' => 'unikey-long'))));
    }
}
