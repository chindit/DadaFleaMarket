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
 * First generated : 07/22/2016 at 13:23
 */

namespace Dada\AdvertisementBundle\Services\GoogleApi;

class GoogleApi{

    /**
     * GoogleAPI constructor. Basic constructor.
     * @param $api string API key for GoogleMaps
     */
    public function __construct($api){
        $this->api = $api;
    }

    public function getCoordsFromCityName($name, $isApi = false){
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($name).'&key='.$this->api;
        $result = file_get_contents($url);
        $reponse = json_decode($result);
        if($reponse->status != 'OK') {
            if ($isApi)
                return false;
            else
                throw new \InvalidArgumentException('City name given is invalid');
        }
        return $reponse->results[0]->geometry->location;
    }

    public function getCityFromCoords($latitude, $longitude){
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latitude.','.$longitude.'&key='.$this->api;

        $result = file_get_contents($url);
        $reponse = json_decode($result);
        if($reponse->status == 'OK')
            return $result;
        else
            return false;

    }

    private $api;
}