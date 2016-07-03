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
 * First generated : 07/03/2016 at 12:16
 */

namespace Dada\AdvertisementBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller{

    /**
     * Remove old entries from the database.
     *
     * @return bool
     */
    public function cleanOldEntriesAction(){
        $maxAge = $this->getParameter('max_age');
        $em = $this->getDoctrine()->getManager();
        $advertsList = ($em->getRepository('DadaAdvertisementBundle:Advertisement')->cleanOldEntries($maxAge));
        foreach($advertsList as $advert){
            $advert->setPublic(false);
        }
        $em->flush();

        //Logging
        $this->get('logger')->info(count($advertsList).' annonces ont été supprimées');

        //Should be suppressed when calling function with CRON
        $this->get('session')->getFlashBag()->add('info', count($advertsList).' annonces ont été supprimées');

        //return true; //Should be used if calling function with CRON
        return $this->redirectToRoute('dada_advertisement_homepage');
    }
}