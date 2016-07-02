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

        $advert = new Advertisement($this->getUser());
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
            return $this->redirectToRoute('dada_advertisement_homepage');
        }

        return $this->render('DadaAdvertisementBundle::add.html.twig', array('form' => $form->createView(), 'ajaxUrl' => $this->get('router')->generate('dada_ajax_city_from_coords', array('latitude' => 'unikey-lat', 'longitude' => 'unikey-long'))));
    }

    /**
     * Shows list of user's adverts
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function homepageAction($page){
        $em = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');

        //Retreiving Adverts
        $listAdverts = $em->findByPageAndUser($page, $this->getUser(), $this->getParameter('nb_items_page'));

        //Setting page to 1 if !results
        if(empty($listAdverts) && $page > 1){
            $listAdverts = $em->findByPageAndUser(1, $this->getUser(), $this->getParameter('nb_items_page'));
        }

        //Rendering
        return $this->render('DadaAdvertisementBundle::homepage.html.twig', array('adverts' => $listAdverts, 'page' => $page));
    }

    /**
     * Generate pagination
     *
     * @param $page int Current page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderHomepagePaginationAction($page){
        $em = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $nbPages = $em->findPageCount($this->getUser(), $this->getParameter('nb_items_page'));

        return $this->render('DadaAdvertisementBundle:Show:pagination.html.twig', array('pagination' => array('total' => $nbPages, 'current' => $page), 'pathName' => 'dada_advertisement_homepage'));
    }


    /**
     * Render $advert and increment number of views
     *
     * @param Advertisement $advert
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAdvertAction(Advertisement $advert){
        //Increasing views
        $advert->setViews($advert->getViews()+1);
        $this->getDoctrine()->getManager()->flush();

        //Rendering
        return $this->render('DadaAdvertisementBundle:Show:advert.html.twig', array('advert' => $advert, 'user' => $this->getUser()));
    }
}
