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
 * First generated : 07/24/2016 at 13:04
 */

namespace Dada\AdvertisementBundle\Controller;


use Dada\AdvertisementBundle\Entity\Category;
use Dada\AdvertisementBundle\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryController extends Controller{

    /**
     * Add a new category
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request){
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        //Verify form validation
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->get('session')->getFlashBag()->add('info', 'Votre catégorie a bien été ajoutée');
            return $this->redirectToRoute('dada_core_admin');
        }

        return $this->render('DadaAdvertisementBundle::add_category.html.twig', array('form' => $form->createView()));
    }

    /**
     * Edit a given Category
     *
     * @param Category $category
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Category $category, Request $request){
        $form = $this->createForm(CategoryType::class, $category);

        //Verify form validation
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->get('session')->getFlashBag()->add('info', 'Votre catégorie a bien été mise à jour');
            return $this->redirectToRoute('dada_core_admin');
        }

        return $this->render('DadaAdvertisementBundle::add_category.html.twig', array('form' => $form->createView()));
    }


    /**
     * Delete a given category
     *
     * @param Category $category
     * @return bool
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Category $category){dump($category);
        $this->getDoctrine()->getManager()->remove($category);
        $this->getDoctrine()->getManager()->flush();
        $response = new JsonResponse();
        $response->setContent('true');
        return $response;
    }
}