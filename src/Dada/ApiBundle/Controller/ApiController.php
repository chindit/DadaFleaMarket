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


use Dada\AdvertisementBundle\Entity\Advertisement;
use Dada\AdvertisementBundle\Entity\Image;
use Dada\AdvertisementBundle\Entity\Town;
use Dada\ApiBundle\Entity\CacheCategory;
use Dada\ApiBundle\Entity\CacheTown;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiController
 * Main controller for the API
 * @package Dada\ApiBundle\Controller
 */
class ApiController extends Controller{


    /**
     * Return all adverts for a given city
     *
     * @param Request $request
     * @param $raw bool Returns data without JSON presentation
     * @return array|bool|JsonResponse
     */
    public function getCityAdvertsAction(Request $request, $raw = false){
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;

        //Check if town is set
        if(!$request->query->has('town')){
            //No city name -> error
            $return = array('status' => 400, 'msg' => 'No city name given');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }

        //Check if request exists in Cache
        $em = $this->getDoctrine()->getManager();
        $cache = $em->getRepository('DadaApiBundle:CacheTown')->findOneByName(urldecode($request->query->get('town')));
        if(!empty($cache)){
            $adverts = $this->checkCache($cache);
            if($adverts instanceof JsonResponse)
                return $adverts; //Bug
            if(is_array($adverts)){
                //If raw -> returning all Advertsiments
                if($raw){
                    return $adverts;
                }
                //Cache OK -> querying
                $listAdverts = $this->checkPage($request, $adverts);
                if(!is_array($listAdverts))
                    return $listAdverts; //Error
                $return = array('status' => 200, 'msg' => 'Query valid.  Have a nice day', 'data' => $listAdverts);
                $return['data']['nbResults'] = count($adverts);
                $return['data']['nbPages'] = ceil($return['data']['nbResults']/$this->getParameter('nb_items_page'));
                $reponse = new JsonResponse($return);
                $reponse->setCharset('UTF-8');
                return $reponse;
            }
        }

        //Cache is empty -> querying without it
        //1)Translate city to coords
        $coords = $this->get('dada.google.api')->getCoordsFromCityName(urldecode($request->query->get('name')));
        //2)Get Adverts
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('DadaAdvertisementBundle:Advertisement');
        $annonces = $repo->findByCoords($coords->lat, $coords->lng, $this->getParameter('default_search_radius'), -1, $this->getParameter('nb_items_page')); //$page is -1 to get all the results
        //3)Create cache
        $cache = new CacheTown();
        $cache->setAdverts(serialize($this->exportAdverts($annonces)));
        $cache->setName(urldecode($request->query->get('name')));
        $em->persist($cache);
        $em->flush();
        //3')If raw -> returning all Advertsiments
        if($raw){
            return $this->exportAdverts($annonces);
        }
        //4)Check page
        $listAdverts = $this->checkPage($request, $annonces);
        if(!is_array($listAdverts))
            return $listAdverts; //Error
        //5)Create response
        $return = array('status' => 200, 'msg' => 'Query valid.  Enjoy.', 'data' => $this->exportAdverts($listAdverts));
        //6)Adding page info
        $return['data']['nbResults'] = count($annonces);
        $return['data']['nbPages'] = ceil($return['data']['nbResults']/$this->getParameter('nb_items_page'));
        //7)Returning data
        $reponse = new JsonResponse($return);
        $reponse->setCharset('UTF-8');
        return $reponse;
    }

    /**
     * Return all adverts in the given Category
     *
     * @param Request $request
     * @return array|bool|JsonResponse
     */
    public function getCategoryAdvertsAction(Request $request){
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;

        //Check if category is set
        if(!$request->query->has('category')){
            //No category -> error
            $return = array('status' => 400, 'msg' => 'No category given');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $repoCategory = $em->getRepository('DadaAdvertisementBundle:Category');
        $category;
        //Can be either name or id
        if(is_numeric($request->query->get('category'))){
            $category = $repoCategory->find($request->query->get('category'));
        }
        else{
            $category = $repoCategory->findOneByName(urldecode($request->query->get('category')));
        }
        if(is_null($category)){
            $return = array('status' => 404, 'msg' => 'The category you requested could not be found. :(');
            $reponse = new JsonResponse($return);
            $reponse->setCharset('UTF-8');
            return $reponse;
        }

        //If category is OK -> retreiving adverts
        $cache = $em->getRepository('DadaApiBundle:CacheCategory')->findByCategory($category->getId());
        if(!empty($cache)){
            $cache = $cache[0];
            $adverts = $this->checkCache($cache);
            if($adverts instanceof JsonResponse)
                return $adverts; //Bug
            if(is_array($adverts)){
                //Cache OK -> querying
                $listAdverts = $this->checkPage($request, $adverts);
                if(!is_array($listAdverts))
                    return $listAdverts; //Error

                $return = array('status' => 200, 'msg' => 'Query valid.  Have a nice day', 'data' => $listAdverts);
                $return['data']['nbResults'] = count($adverts);
                $return['data']['nbPages'] = ceil($return['data']['nbResults']/$this->getParameter('nb_items_page'));
                $reponse = new JsonResponse($return);
                $reponse->setCharset('UTF-8');
                return $reponse;
            }
        }
        //No cache -> creating query
        $annonces = $em->getRepository('DadaAdvertisementBundle:Advertisement')->findByCategory($category);

        $cache = new CacheCategory();
        $cache->setAdverts(serialize($this->exportAdverts($annonces)));
        $cache->setCategory($category->getId());
        $em->persist($cache);
        $em->flush();
        //Check page
        $listAdverts = $this->checkPage($request, $annonces);
        if(!is_array($listAdverts))
            return $listAdverts; //Error
        //Create response
        $return = array('status' => 200, 'msg' => 'Query valid.  Enjoy.', 'data' => $this->exportAdverts($listAdverts));
        //Adding page info
        $return['data']['nbResults'] = count($annonces);
        $return['data']['nbPages'] = ceil($return['data']['nbResults']/$this->getParameter('nb_items_page'));
        //Returning data
        $reponse = new JsonResponse($return);
        $reponse->setCharset('UTF-8');
        return $reponse;
    }

    /**
     * Return all adverts with the given Category in range of given City
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTownCategoryAdvertsAction(Request $request){
        //- - - - - - - - - - - - - - - - - -
        //Standard checks
        //- - - - - - - - - - - - - - - - - -
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

        //Check if category is set
        if(!$request->query->has('category')){
            //No category -> error
            $return = array('status' => 400, 'msg' => 'No category given');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }

        //Retreiving Category
        $em = $this->getDoctrine()->getManager();
        $repoCategory = $em->getRepository('DadaAdvertisementBundle:Category');
        $category;
        //Can be either name or id
        if(is_numeric($request->query->get('category'))){
            $category = $repoCategory->find($request->query->get('category'));
        }
        else{
            $category = $repoCategory->findOneByName(urldecode($request->query->get('category')));
        }
        if(is_null($category)){
            $return = array('status' => 404, 'msg' => 'The category you requested could not be found. :(');
            $reponse = new JsonResponse($return);
            $reponse->setCharset('UTF-8');
            return $reponse;
        }
        //- - - - - - - - - - - - - - - - - -
        //End of standard checks
        //- - - - - - - - - - - - - - - - - -

        //Getting all adverts
        $annonces = $this->getCityAdvertsAction($request, true);

        //Parsing to get only searched category
        $output = array();
        foreach($annonces as $ad){
            foreach($ad['categories'] as $cat){
                if($cat['id'] == $category->getId()){
                    $output[] = $ad;
                    break;
                }
            }
        }

        //Checking pages for output
        $outputPaginated = $this->checkPage($request, $output);
        if(!is_array($outputPaginated))
            return $outputPaginated;
        $reponse = new JsonResponse($outputPaginated);
        $reponse->setCharset('UTF-8');
        return $reponse;
    }

    /**
     * Return a specific Advertisement via it's ID or Slug
     *
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function getAdvertAction(Request $request){
        //- - - - - - - - - - - - - - - - - -
        //Standard checks
        //- - - - - - - - - - - - - - - - - -
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;
        //- - - - - - - - - - - - - - - - - -
        //End of standard checks
        //- - - - - - - - - - - - - - - - - -

        if(!$request->query->has('ad')){
            $return = array('status' => 400, 'msg' => 'No identifier given');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }

        $repo = $this->getDoctrine()->getRepository('DadaAdvertisementBundle:Advertisement');
        $advert;
        if(is_numeric($request->query->get('ad'))){
            $advert = $repo->find($request->query->get('ad'));
        }
        else{
            $advert = $repo->findOneBySlug($request->query->get('ad'));
        }
        if(is_null($advert)){
            $return = array('status' => 404, 'msg' => 'The Advertisement you\'re looking for doesn\'t exist. :/');
            $reponse = new JsonResponse($return);
            $reponse->setCharset('UTF-8');
            return $reponse;
        }

        //Everything OK -> returning response
        $return = array('status' => 200, 'msg' => 'Query valid.  Enjoy.', 'data' => $this->exportAdverts(array($advert)));
        $response = new JsonResponse($return);
        $response->setCharset('UTF-8');
        return $response;

    }

    /**
     * Create a new Advertisement
     * Available only by POST method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addAdvertAction(Request $request){
        //- - - - - - - - - - - - - - - - - -
        //Standard checks
        //- - - - - - - - - - - - - - - - - -
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;
        //- - - - - - - - - - - - - - - - - -
        //End of standard checks
        //- - - - - - - - - - - - - - - - - -

        //Creating new Object
        $advert = new Advertisement();

        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);

        //Checking parameters
        $params = array('title' => 'string', 'description' => 'string', 'price' => 'numeric');
        foreach($params as $key => $value){
            if(!$request->request->has($key)){
                $return = array('status' => 400, 'msg' => 'Missing parameter «'.$key.'»!');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            $method = 'is_'.$value;
            if(!$method($request->request->get($key))){
                $return = array('status' => 400, 'msg' => 'Parameter «'.$key.'» is not a correct value!');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            else{
                $method = 'set'.ucfirst($key);
                $advert->$method($request->request->get($key));
            }
        }

        if(strlen($advert->getTitle()) < 10 || strlen($advert->getDescription()) < 10){
            $return = array('status' => 403, 'msg' => 'Title and/or description are too short.  Minimum 10 char long is required');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }

        //Standard parameters are OK.  Checking non standard ones.
        if(!$request->request->has('town')){
            $return = array('status' => 400, 'msg' => 'Missing parameter «Town»!');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        $townList = $request->request->get('town');
        //Setting array to process it more easily
        if(!is_array($townList)){
            $townList = array($townList);
        }
        //Setting towns
        $api = $this->get('dada.google.api');
        foreach($townList as $townItem){
            $coords = $api->getCoordsFromCityName($townItem);
            $town = new Town();
            $town->setName($townItem);
            $town->setAdvert($advert);
            $town->setLatitude($coords->lat);
            $town->setLongitude($coords->lng);
            $em->persist($town);
            $advert->addTown($town);
        }
        //Settings categories
        if(!$request->request->has('category')){
            $return = array('status' => 400, 'msg' => 'Missing parameter «Category»!');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        $categoryList = $request->request->get('category');
        //Setting array to process it more easily
        if(!is_array($categoryList)){
            $categoryList = array($categoryList);
        }
        $repoCateg = $em->getRepository('DadaAdvertisementBundle:Category');
        foreach($categoryList as $categItem){
            $categ = (is_numeric($categItem)) ? $repoCateg->find($categItem) : $repoCateg->findOneByName($categItem);
            if(is_null($categ)){
                $return = array('status' => 400, 'msg' => 'Category «'.$categItem.'» is invalid!');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            $advert->addCategory($categ);
        }
        //Setting images
        $imageList = $request->files->all();
        foreach($imageList as $img){
            $image = new Image();
            $image->setName(uniqid().'.'.$img->guessExtension());
            $filePath = $img->getRealPath();
            $imageSize = getimagesize($filePath);
            $image->setWidth($imageSize[0]);
            $image->setHeight($imageSize[1]);
            $image->setWeight(filesize($filePath));
            $advert->addImage($image);
            $img->move('../web/uploads/adverts/', $image->getName());
            $em->persist($image);
        }
        $advert->setPublic(true);

        $repoToken = $em->getRepository('DadaApiBundle:Token');
        $tokenUser = $repoToken->findOneByToken($request->request->get('key'));
        $user = $tokenUser->getUser();
        $em->initializeObject($user);

        $advert->setUser($user);

        //Flushing
        $em->flush();

        $return = array('status' => 200, 'msg' => 'Advert created!', 'data' => $this->exportAdverts(array($advert)));
        $response = new JsonResponse($return);
        $response->setCharset('UTF-8');
        return $response;
    }

    /**
     * Update an advert
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function modifyAdvertAction(Request $request){
        //- - - - - - - - - - - - - - - - - -
        //Standard checks
        //- - - - - - - - - - - - - - - - - -
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;
        //- - - - - - - - - - - - - - - - - -
        //End of standard checks
        //- - - - - - - - - - - - - - - - - -

        //Check advert
        $em = $this->getDoctrine()->getManager();
        $advert = (is_numeric($request->request->get('ad'))) ? $em->getRepository('DadaAdvertisementBundle:Advertisement')->find($request->request->get('ad')) : $em->getRepository('DadaAdvertisementBundle:Advertisement')->findOneBySlug($request->request->get('ad'));
        if(is_null($advert)){
            $return = array('status' => 404, 'msg' => 'The Advertisemen you\'re looking for doesn\'t exist :(');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //Check ownership
        $repoToken = $em->getRepository('DadaApiBundle:Token');
        $tokenUser = $repoToken->findOneByToken($request->request->get('key'));
        $user = $tokenUser->getUser();
        if($user->getId() != $advert->getUser()->getId()){
            //Checking only ID because we're working with PROXY Obj
            $return = array('status' => 403, 'msg' => 'Well… You\'re not the owner of this Ad.  Please get out immediately!  Bad hacker!');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //User OK
        //Title
        if($request->request->has('title')){
            $length = strlen($request->request->get('title'));
            if($length < 10 || $length > 255){
                $return = array('status' => 403, 'msg' => 'Title is too short or too long.  A length between 10 and 255 is required');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            //Ok -> setting value
            $advert->setTitle($request->request->get('title'));
        }
        //Setting description
        if($request->request->has('description')){
            $length = strlen($request->request->get('description'));
            if($length < 10){
                $return = array('status' => 403, 'msg' => 'Description is too short.  A minimum length of 10 is required');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            $advert->setDescription($request->request->get('description'));
        }
        //Price
        if($request->request->has('price')){
            if(!is_numeric($request->request->get('price'))){
                $return = array('status' => 403, 'msg' => 'A numeric value is required for the price.');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            if($request->request->get('price') < 0){
                $return = array('status' => 403, 'msg' => 'Price cannot be lower than 0');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            $advert->setPrice($request->request->get('price'));
        }
        //Status
        if($request->request->has('published')){
            if(!is_bool($request->request->get('published'))){
                $return = array('status' => 403, 'msg' => 'A boolean value is required for the publication status');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            $advert->setPublished((bool)$request->request->get('published'));
        }

        //Starting non conventional data

        //Categories
        if($request->request->has('category')){
            $updateList = $request->request->get('category');
            if(!is_array($updateList)){
                $updateList = array($updateList);
            }
            //Getting current categories in an array
            $idList = array();
            foreach($advert->getCategory() as $cat){
                $idList[] = $cat->getId();
            }
            //Processing categories.
            $repoCateg = $em->getRepository('DadaAdvertisementBundle:Category');
            foreach($updateList as $categItem){
                //Is categ OK?
                $categ = (is_numeric($categItem)) ? $repoCateg->find($categItem) : $repoCateg->findOneByName($categItem);
                if(is_null($categ)){
                    $return = array('status' => 400, 'msg' => 'Category «'.$categItem.'» is invalid!');
                    $response = new JsonResponse($return);
                    $response->setCharset('UTF-8');
                    return $response;
                }
                //Does categ already belongs to Advert?
                if(!in_array($categ->getId(), $idList)){
                    $advert->addCategory($categ);
                }
            }
        }

        //Towns
        if($request->request->has('town')){
            $updateList = $request->request->get('town');
            if(!is_array($updateList)){
                $updateList = array($updateList);
            }
            //Getting current categories in an array
            $idList = array();
            foreach($advert->getTown() as $city){
                $idList[] = $city->getId();
            }
            //Parsing towns
            $api = $this->get('dada.google.api');
            foreach($updateList as $townItem){
                $coords = $api->getCoordsFromCityName($townItem);
                $town = new Town();
                $town->setName($townItem);
                $town->setAdvert($advert);
                $town->setLatitude($coords->lat);
                $town->setLongitude($coords->lng);
                $em->persist($town);
                $advert->addTown($town);
            }
        }

        //Images
        if($request->files->count() > 0){
            $nbExisting = count($advert->getImages());
            if($nbExisting + $request->files->count() > 3){ //Too much pictures -> triggering error
                $return = array('status' => 400, 'msg' => 'Too much images for one advert.  Maximum is set to 3!');
                $response = new JsonResponse($return);
                $response->setCharset('UTF-8');
                return $response;
            }
            //Else -> it's OK => Setting images to Advert
            $imageList = $request->files->all();
            foreach($imageList as $img){
                $image = new Image();
                $image->setName(uniqid().'.'.$img->guessExtension());
                $filePath = $img->getRealPath();
                $imageSize = getimagesize($filePath);
                $image->setWidth($imageSize[0]);
                $image->setHeight($imageSize[1]);
                $image->setWeight(filesize($filePath));
                $advert->addImage($image);
                $img->move('../web/uploads/adverts/', $image->getName());
                $em->persist($image);
            }
        }

        //Everything is done.  Saving and thanking
        $em->flush();
        $return = array('status' => 200, 'msg' => 'Update done.  Thanks for using Dada\'s Flea Market API ;)', 'data' => $this->exportAdverts(array($advert)));
        $response = new JsonResponse($return);
        $response->setCharset('UTF-8');
        return $response;
    }

    /**
     * Delete some elements of the advert or the whole advert itself
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAdvertAction(Request $request){
        //- - - - - - - - - - - - - - - - - -
        //Standard checks
        //- - - - - - - - - - - - - - - - - -
        //Check if key is OK
        $validity = $this->checkKey($request);
        if(!is_bool($validity))
            return $validity;
        //Check advert
        $em = $this->getDoctrine()->getManager();
        $advert = (is_numeric($request->request->get('ad'))) ? $em->getRepository('DadaAdvertisementBundle:Advertisement')->find($request->request->get('ad')) : $em->getRepository('DadaAdvertisementBundle:Advertisement')->findOneBySlug($request->request->get('ad'));
        if(is_null($advert)){
            $return = array('status' => 404, 'msg' => 'The Advertisemen you\'re looking for doesn\'t exist :(');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //Check ownership
        $repoToken = $em->getRepository('DadaApiBundle:Token');
        $tokenUser = $repoToken->findOneByToken($request->request->get('key'));
        $user = $tokenUser->getUser();
        if($user->getId() != $advert->getUser()->getId()){
            //Checking only ID because we're working with PROXY Obj
            $return = array('status' => 403, 'msg' => 'Well… You\'re not the owner of this Ad.  Please get out immediately!  Bad hacker!');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //- - - - - - - - - - - - - - - - - -
        //End of standard checks
        //- - - - - - - - - - - - - - - - - -

        $return;

        //2 options: we delete whole advert or just some elements.
        if($request->request->has('category') || $request->request->has('town') || $request->request->has('image')){
            //Deleting an elem
            //Category
            if($request->request->has('category')){
                $updateList = $request->request->get('category');
                if(!is_array($updateList)){
                    $updateList = array($updateList);
                }
                //Getting current categories in an array
                $idList = array();
                foreach($advert->getCategory() as $cat){
                    $idList[] = $cat->getId();
                }
                //Processing categories.
                $repoCateg = $em->getRepository('DadaAdvertisementBundle:Category');
                foreach($updateList as $categItem){
                    //Is categ OK?
                    $categ = (is_numeric($categItem)) ? $repoCateg->find($categItem) : $repoCateg->findOneByName($categItem);
                    if(is_null($categ)){
                        $return = array('status' => 400, 'msg' => 'Category «'.$categItem.'» is invalid!');
                        $response = new JsonResponse($return);
                        $response->setCharset('UTF-8');
                        return $response;
                    }
                    //Does categ already belongs to Advert?
                    if(in_array($categ->getId(), $idList)){
                        $advert->removeCategory($categ);
                    }
                }
            }

            //Town
            if($request->request->has('town')){
                $updateList = $request->request->get('town');
                if(!is_array($updateList)){
                    $updateList = array($updateList);
                }

                //Parsing towns
                foreach($updateList as $townItem){
                    foreach($advert->getTown() as $town){
                        if($townItem == $town->getName()){
                            $advert->removeTown($town);
                            $em->remove($town);
                        }
                    }
                }
            }

            //Images
            if($request->request->has('image')){
                $imageList = (is_array($request->request->get('image'))) ? $request->request->get('image') : array($request->request->get('image'));
                foreach($imageList as $img){
                    foreach($advert->getImages() as $adImg){
                        if($adImg->getName() == $img){
                            unlink('../web/uploads/adverts/'.$adImg->getName());
                            $advert->removeImage($adImg);
                            $em->remove($adImg);
                        }
                    }
                }
            }

            $return = array('status' => 200, 'msg' => 'Elements successfully removed from advert.', 'data' => $this->exportAdverts(array($advert)));
        }
        else{
            //Deleting whole advert
            $em->remove($advert);
            $return = array('status' => 200, 'msg' => 'Advert successfully removed.  Sad end, isn\'t it?');
        }
        $em->flush();

        //Returning main response
        $response = new JsonResponse($return);
        $response->setCharset('UTF-8');
        return $response;
    }

    public function globalAction(Request $request){
        //No need to check key.  It will be done in sub-functions
        if($request->query->has('town') && $request->query->has('category')){
            return $this->getTownCategoryAdvertsAction($request);
        }
        if($request->query->has('town')){
            return $this->getCityAdvertsAction($request);
        }
        if($request->query->has('category')){
            return $this->getCategoryAdvertsAction($request);
        }
        if($request->query->has('ad')){
            return $this->getAdvertAction($request);
        }
    }

    /**
     * Parse a list of Advertisement and return a «JSON ready» array
     *
     * @param $list
     * @return array
     */
    private function exportAdverts($list){
        if(!is_array($list))
            throw new \InvalidArgumentException('An array was expected :(');
        $data = array();
        foreach($list as $advert){
            $item = array();
            $item['id'] = $advert->getId();
            $item['title'] = $advert->getTitle();
            $item['description'] = $advert->getDescription();
            $item['price'] = $advert->getPrice();
            $item['views'] = $advert->getViews();
            $item['created'] = $advert->getCreated();
            $item['public'] = $advert->getPublic();
            //Parsing images
            $item['images'] = array();
            foreach($advert->getImages() as $image){
                $item['images'][] = $image->getName();
            }
            //Parsing categories
            $item['categories'] = array();
            foreach($advert->getCategory() as $category){
                $arrayCateg = array();
                $arrayCateg['id'] = $category->getId();
                $arrayCateg['name'] = $category->getName();
                $item['categories'][] = $arrayCateg;
            }
            //Parsing towns
            foreach($advert->getTown() as $town){
                $item['towns'][] = $town->getName();
            }
            //Adding to general array
            $data[] = $item;
        }
        //Return
        return $data;
    }

    /**
     * Check if cache is valid or not
     *
     * @param $cache
     * @return bool|mixed|JsonResponse
     */
    private function checkCache($cache){
        //Cache found -> processing
        $expiration = new \DateTime();
        $expiration->sub(new \DateInterval('PT2H'));
        $em = $this->getDoctrine()->getManager();
        if($cache->getCreated() < $expiration){
            //Cache outdated -> removing
            $em->remove($cache);
            $em->flush();
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
            return $adverts;
        }
        return false;
    }

    /**
     * Check if API key exists and is valid
     *
     * @param Request $request
     * @return bool|JsonResponse
     */
    private function checkKey(Request $request){
        //1)Is Key present?
        if(!$request->query->has('key') && !$request->request->has('key')){
            //Returning error
            $return = array('status' => 400, 'msg' => 'No API key found');
            $response = new JsonResponse($return);
            $response->setCharset('UTF-8');
            return $response;
        }
        //2)Does key exists?
        $key = ($request->query->has('key')) ? $request->query->get('key') : $request->request->get('key');
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

    /**
     * Select only Adverts for $page and check validity of $page
     *
     * @param Request $request
     * @param $adverts
     * @return array|JsonResponse
     */
    private function checkPage(Request $request, $adverts){
        $page = 1;
        if($request->query->has('page')){
            //Is page valid?
            if(!is_numeric($request->query->get('page'))){
                $return = array('status' => 405, 'msg' => 'Page identifier is invalid');
                $reponse = new JsonResponse($return);
                $reponse->setCharset('UTF-8');
                return $reponse;
            }
            //Getting page
            $page = $request->query->get('page');
        }
        //Returning response
        $pageFrom = ($page-1)*$this->getParameter('nb_items_page');
        $pageTo = $page*$this->getParameter('nb_items_page');
        if(count($adverts) <= $pageFrom){
            //Page is out of range
            $return = array('status' => 404, 'msg' => 'The page you requested could not be found. :(');
            $reponse = new JsonResponse($return);
            $reponse->setCharset('UTF-8');
            return $reponse;
        }
        return array_slice($adverts, $pageFrom, $this->getParameter('nb_items_page'));
    }
}