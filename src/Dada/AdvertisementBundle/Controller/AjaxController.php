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
 * First generated : 06/25/2016 at 19:15
 */

namespace Dada\AdvertisementBundle\Controller;


use Dada\AdvertisementBundle\Entity\Advertisement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AjaxController extends Controller{
    /**
     * Returns city name from GPS coordinates
     *
     * @param $latitude
     * @param $longitude
     * @return JsonResponse
     */
    public function getCityFromCoordsAction($latitude, $longitude){
        if(is_float($latitude) && is_float($longitude))
            throw new \InvalidArgumentException('App was expecting float as GPS coords. «'.gettype($latitude).'» and «'.gettype($longitude).'» given.');

        $googleApi = $this->get('dada.google.api');
        $result = $googleApi->getCityFromCoords($latitude, $longitude);
        $response = new JsonResponse();
        $response->setContent($result);
        $response->setCharset('UTF-8');
        return $response;
    }

    /**
     * Reverse advert's published status
     *
     * @param $advert Advert Advert
     * @return bool Exit status
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function reversePublishedStatusAction(Advertisement $advert){
        if($advert->getUser() != $this->getUser()){
            //throw new \UnauthorizedHttpException('Vous n\'êtes pas autorisé à modifier le status de cette annonce');
            $response = new JsonResponse();
            $response->setContent('false');
            return $response;
        }
        $advert->setPublic(!$advert->getPublic());
        $this->getDoctrine()->getManager()->flush();
        $response = new JsonResponse();
        $response->setContent('true');

        return $response;
    }

    /**
     * Delete (via Ajax) an Advert
     *
     * @param Advertisement $advert
     * @return bool Exit status
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAdvertAction(Advertisement $advert){
        if($advert->getUser() != $this->getUser()){
            //throw new \UnauthorizedHttpException('Vous n\'êtes pas autorisé à supprimer cette annonce');
            $response = new JsonResponse();
            $response->setContent('false');
            return $response;
        }
        $this->getDoctrine()->getManager()->remove($advert);
        $this->getDoctrine()->getManager()->flush();
        $response = new JsonResponse();
        $response->setContent('true');

        return $response;
    }
}