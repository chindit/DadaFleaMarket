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
 * First generated : 07/25/2016 at 22:06
 */

namespace Dada\ApiBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiController
 * Main controller for the API
 * @package Dada\ApiBundle\Controller
 */
class ApiController extends Controller{
    public function getCityAdvertsAction(Request $request){
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;

        //Check if town is set
        if(!$request->query->has('name')){
            //No city name -> error
            $return = array('status' => 400, 'msg' => 'No city name given');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }

        //Check if request exists in Cache
        $em = $this->getDoctrine()->getManager();
        $cache = $em->getRepository('DadaApiBundle:CacheTown')->findOneByName($request->query->get('name'));
        if(!empty($cache)){
            //Cache found -> processing
            $expiration = new \DateTime();
            $expiration->sub('P2H');
            if($cache->getCreated() < $expiration){
                //Cache outdated -> removing
                $em->remove($cache);
            }
            else{
                //Getting cache
                $adverts = unserialize($cache->getAdverts());
                if(!is_array($adverts)){
                    $return = array('status' => 500, 'msg' => 'Your query had not been processed due to an internal server error.  Please try again.');
                    $em->remove($cache); //Cache invalid -> deleting
                    $em->flush();
                    $reponse = new JsonResponse($return);
                    $reponse->setCharset('UTF-8');
                    return $reponse;
                }
                //Cache OK -> querying
                if($request->query->has('page')){
                    //Is page valid?
                    if(!is_int($request->query->get('page'))){
                        $return = array('status' => 405, 'msg' => 'Page identifier is invalid');
                        $reponse = new JsonResponse($return);
                        $reponse->setCharset('UTF-8');
                        return $reponse;
                    }
                    //Getting page
                    $pageFrom = ($request->query->get('page')-1)*$this->getParameter('nb_items_page');
                    $pageTo = $request->query->get('page')*$this->getParameter('nb_items_page');
                    if(count($adverts) < $pageFrom){
                        //Page is out of range
                        $return = array('status' => 404, 'msg' => 'The page you requested could not be found. :(');
                        $reponse = new JsonResponse($return);
                        $reponse->setCharset('UTF-8');
                        return $reponse;
                    }
                    //If here, page is NOT out of range (aka, page is valid)
                    $listAdvertsId = array_slice($adverts, $pageFrom, $this->getParameter('nb_items_page'));
                    $listAdverst = $em->getRepository('DadaAdvertisementBundle:Advertisement')->findByList($listAdvertsId);
                }
                //Returning response
                $response = array('status' => 200, 'msg' => 'Query valid.  Have a nice day');
            }
        }
        else{return 'a';}

    }

    /**
     * Check if API key exists and is valid
     *
     * @param Request $request
     * @return bool|JsonResponse
     */
    private function checkKey(Request $request){
        //1)Is Key present?
        if(!$request->query->has('key')){
            //Returning error
            $return = array('status' => 400, 'msg' => 'No API key found');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //2)Does key exists?
        $key = $request->query->get('key');
        $repo = $this->getDoctrine()->getRepository('DadaApiBundle:Token');
        $token = $repo->findByToken($key);
        if(empty($token)){
            //Key not found -> error!
            $return = array('status' => 401, 'msg' => 'The key you\'ve entered is invalid!');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //3)Is key expired?
        if($token[0]->getExpire() < new \DateTime()){
            //Key expired -> error
            $return = array('status' => 401, 'msg' => 'The key you\'ve entered has expired.  Please renew it.');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //4)If we reach this point, key is valid.
        return true;
    }
}