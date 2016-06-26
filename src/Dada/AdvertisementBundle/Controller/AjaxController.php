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


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController extends Controller{
    public function getCityFromCoordsAction($latitude, $longitude){
        if(is_float($latitude) && is_float($longitude))
            throw new \InvalidArgumentException('App was expecting float as GPS coords. «'.gettype($latitude).'» and «'.gettype($longitude).'» given.');
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latitude.','.$longitude.'&key='.$this->getParameter('googlemaps_api');

        $result = file_get_contents($url);
        $response = new JsonResponse();
        $response->setContent($result);
        $response->setCharset('UTF-8');
        return $response;
    }
}