<?php
/**
 * DadaFleaMarket : Copyright © 2016 Chindit
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * First generated : 07/22/2016 at 11:32
 */

namespace Dada\AdvertisementBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller{

    public function townAction(Request $request){
        $query = $request->get('search-town');

        //Empty requests not allowed
        if(empty($query))
            return $this->redirectToRoute('dada_core_homepage');

        $googleMaps = $this->get('dada_advert.googleAPI');

        //Coords for desired location
        $coords = $googleMaps->getCoordsFromCityName($query);

        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $annonces = $repo->findByCoords($coords->lat, $coords->lng, $this->getParameter('default_search_radius'), 1, $this->getParameter('nb_items_page'));
        $nbAnnonces = $repo->getNbAdverts($coords->lat, $coords->lng, $this->getParameter('default_search_radius'));
        $listeCateg = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category')->findAll();

        return $this->render('DadaAdvertisementBundle::search.html.twig', array('query' => $query, 'nbResults' => $nbAnnonces, 'results' => $annonces, 'lat' => $coords->lat, 'lng' => $coords->lng, 'pagination' => array('total' => ceil($nbAnnonces/$this->getParameter('nb_items_page')), 'current' => 1), 'listeCateg' => $listeCateg));
    }

    public function townPageAction($lat, $long, $query, $page){
        //Checking if args ar OK
        if(!is_numeric($lat) || !is_numeric($long) || !is_numeric($page))
            throw new \InvalidArgumentException('Des coordonnées correctes sont attendues!');

        //Querying
        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $annonces = $repo->findByCoords($lat, $long, $this->getParameter('default_search_radius'), $page, $this->getParameter('nb_items_page'));
        $nbAnnonces = $repo->getNbAdverts($lat, $long, $this->getParameter('default_search_radius'));
        $listeCateg = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category')->findAll();

        return $this->render('DadaAdvertisementBundle::search.html.twig', array('query' => $query, 'nbResults' => $nbAnnonces, 'results' => $annonces, 'lat' => $lat, 'lng' => $long, 'pagination' => array('total' => ceil($nbAnnonces/$this->getParameter('nb_items_page')), 'current' => $page), 'listeCateg' => $listeCateg));
    }

    public function categAction(Request $request){
        $lat = $request->get('lat');
        $long = $request->get('lng');
        $query = $request->get('query');
        $categ = $request->get('categ');
        if(!is_numeric($lat) || !is_numeric($long))
            throw new \InvalidArgumentException('Des coordonnées correctes sont attendues!');

        //Checking OBJ existence
        $categObj = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category')->find($categ);

        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $annonces = $repo->findByCoords($lat, $long, $this->getParameter('default_search_radius'), 1, $this->getParameter('nb_items_page'), $categObj);
        $nbAnnonces = $repo->getNbAdverts($lat, $long, $this->getParameter('default_search_radius'), $categObj);

        return $this->render('DadaAdvertisementBundle::search.html.twig', array('query' => $query, 'nbResults' => $nbAnnonces, 'results' => $annonces, 'lat' => $lat, 'lng' => $long, 'pagination' => array('total' => ceil($nbAnnonces/$this->getParameter('nb_items_page')), 'current' => 1), 'categ' => $categObj));
    }

    public function categPageAction($lat, $long, $query, $categ, $page){
        if(!is_numeric($lat) || !is_numeric($long) || !is_numeric($page))
            throw new \InvalidArgumentException('Des coordonnées correctes sont attendues!');
        //Checking OBJ existence
        $categObj = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Category')->find($categ);

        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $annonces = $repo->findByCoords($lat, $long, $this->getParameter('default_search_radius'), $page, $this->getParameter('nb_items_page'), $categObj);
        $nbAnnonces = $repo->getNbAdverts($lat, $long, $this->getParameter('default_search_radius'), $categObj);

        return $this->render('DadaAdvertisementBundle::search.html.twig', array('query' => $query, 'nbResults' => $nbAnnonces, 'results' => $annonces, 'lat' => $lat, 'lng' => $long, 'pagination' => array('total' => ceil($nbAnnonces/$this->getParameter('nb_items_page')), 'current' => $page), 'categ' => $categObj));
    }
}