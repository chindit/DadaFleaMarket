<?php

namespace Dada\ApiBundle\Controller;

use Dada\ApiBundle\Entity\Token;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{

    /**
     * Create a new API Key
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function requestAction(){
        $em = $this->getDoctrine()->getRepository('DadaApiBundle:Token');
        $api = $em->findByUser($this->getUser());  //$api is EXISTING token

        $now = new \DateTime();
        $expired = false;
        $exists = false;
        if(!empty($api) && $api[0]->getExpire() > $now){
            $exists = true;
            $this->get('session')->getFlashBag()->add('danger', 'Vous possédez déjà une clé d\'API!');
            $this->get('session')->getFlashBag()->add('info', 'Votre clé est: '.$api[0]->getToken());
        }
        if(!empty($api) && $api[0]->getExpire() < $now){
            $exists = true; $expired = true;
            $this->get('session')->getFlashBag()->add('danger', 'Votre clé est expirée!  Veuillez en re-générer une!');
            $this->getDoctrine()->getManager()->remove($api[0]);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('DadaApiBundle::generate.html.twig', array('api' => $api, 'exists' => $exists, 'expired' => $expired, 'requests' => $this->getParameter('api_number_queries'), 'minutes' => $this->getParameter('api_expire_time')));
    }

    public function generateAction(){
        $em = $this->getDoctrine()->getRepository('DadaApiBundle:Token');
        $api = $em->findByUser($this->getUser());  //$api is EXISTING token

        $now = new \DateTime();
        if(!empty($api)){
            return $this->redirectToRoute('dada_api_request');
        }

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $token = '';
        for($i = 0; $i < 50; $i++){
            $pos = mt_rand(0, strlen($chars)-1);
            $token .= $chars[$pos];
        }

        $tokenObj = new Token();
        $tokenObj->setUser($this->getUser());
        $tokenObj->setToken($token);
        $this->getDoctrine()->getManager()->persist($tokenObj);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('info', 'Votre clé d\'API a été générée avec succès.  Faites-en bon usage ;)');
        return $this->render('DadaApiBundle::generated.html.twig', array('token' => $token, 'expiration' => $tokenObj->getExpire()));

    }
}
