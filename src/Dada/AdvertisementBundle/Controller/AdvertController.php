<?php

namespace Dada\AdvertisementBundle\Controller;

use Dada\AdvertisementBundle\Entity\Advertisement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dada\AdvertisementBundle\Form\AdvertisementType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


class AdvertController extends Controller{
    /**
     * Show and process new advertisement.
     *
     * @param Request $request Form submitted
     * @return \Symfony\Component\HttpFoundation\Response Render if !$request or redirect to homepageAction if success
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request){

        //Checking Categories
        $nb = count($this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category')->findAll());
        if($nb == 0){
            $this->get('session')->getFlashBag()->add('danger', 'Aucune catégorie trouvée.  Veuillez en créer une');
            return $this->redirectToRoute('dada_advertisement_homepage');
        }

        $advert = new Advertisement($this->getUser());
        $form = $this->createForm(AdvertisementType::class, $advert);

        //Verify form validation
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->handleAdvertSumbission($advert);
            $this->get('session')->getFlashBag()->add('info', 'Votre annonce a bien été ajoutée');
            return $this->redirectToRoute('dada_advertisement_homepage');
        }

        return $this->render('DadaAdvertisementBundle::add.html.twig', array('form' => $form->createView(), 'ajaxUrl' => $this->get('router')->generate('dada_ajax_city_from_coords', array('latitude' => 'unikey-lat', 'longitude' => 'unikey-long'))));
    }

    /**
     * Persist entity when advert submitted
     *
     * @param Advertisement $advert
     * @return bool
     */
    private function handleAdvertSumbission(Advertisement $advert){
        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        foreach($advert->getImages() as $image){
            //Checking for «null» images
            if(is_null($image->getName())){
                throw new \InvalidArgumentException('L\'image soumise n\'est pas valide!');
            }
            $image->setAdvert($advert);
        }
        //Checking for empty category
        if(count($advert->getCategory()) == 0){
            throw new \InvalidArgumentException('Au moins une catégorie doit être sélectionnée!');
        }
        //Checking for empty town
        if(count($advert->getTown()) == 0){
            throw new \InvalidArgumentException('Au moins une ville doit être mentionnée!');
        }
        //Registering town
        foreach($advert->getTown() as $town){
            $town->setAdvert($advert);
            $coords = $this->get('dada.google.api')->getCoordsFromCityName($town->getName());
            $town->setLatitude($coords->lat);
            $town->setLongitude($coords->lng);
        }

        $em->flush();
        return true;
    }

    /**
     * Edit an advert
     *
     * @param Advertisement $advert Avert to edit
     * @param Request $request Form submission
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response Rendering
     */
    public function editAction(Advertisement $advert, Request $request){
        if($advert->getUser() != $this->getUser())
            throw new UnauthorizedHttpException('Cette annonce ne vous appartient pas!  Garnement va!');

        //Checking Categories
        $nb = count($this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category')->findAll());
        if($nb == 0){
            $this->get('session')->getFlashBag()->add('danger', 'Aucune catégorie trouvée.  Veuillez en créer une');
            return $this->redirectToRoute('dada_advertisement_homepage');
        }

        $form = $this->createForm(AdvertisementType::class, $advert);

        //Handling form
        //Verify form validation
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->handleAdvertSumbission($advert);
            $this->get('session')->getFlashBag()->add('info', 'Votre annonce a bien été mise à jour');
            return $this->redirectToRoute('dada_advertisement_homepage');
        }

        return $this->render('DadaAdvertisementBundle::add.html.twig', array('form' => $form->createView(), 'advert' => $advert, 'ajaxUrl' => $this->get('router')->generate('dada_ajax_city_from_coords', array('latitude' => 'unikey-lat', 'longitude' => 'unikey-long'))));
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
        $advert->setViews($advert->getViews() + 1);
        $this->getDoctrine()->getManager()->flush();

        //Rendering
        return $this->render('DadaAdvertisementBundle:Show:advert.html.twig', array('advert' => $advert, 'user' => $this->getUser()));
    }

}
