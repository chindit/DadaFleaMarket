<?php

namespace Dada\AdvertisementBundle\Controller;

use Dada\AdvertisementBundle\Entity\Advertisement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dada\AdvertisementBundle\Form\AdvertisementType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdvertController extends Controller
{
    /**
     * Show and process new advertisement.
     *
     * @param Request $request Form submitted
     * @return \Symfony\Component\HttpFoundation\Response Render if !$request or redirect to homepageAction if success
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request){
        $advert = new Advertisement();
        $form = $this->createForm(AdvertisementType::class, $advert);

        //Verify form validation
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            foreach($advert->getImages() as $image){
                $image->setAdvert($advert);
            }
            $em->flush();
            $this->get('session')->getFlashBag()->add('info', 'Votre annonce a bien été ajoutée');
            $this->redirectToRoute('dada_advertisement_homepage');
        }

        return $this->render('DadaAdvertisementBundle::add.html.twig', array('form' => $form->createView(), 'ajaxUrl' => $this->get('router')->generate('dada_ajax_city_from_coords', array('latitude' => 'unikey-lat', 'longitude' => 'unikey-long'))));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function homepageAction($page){
        $em = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        //$listAdverst = $em->findByPage($page, $this->getUser());
    }
}
