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
 * First generated : 07/02/2016 at 15:25
 */

namespace Dada\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This class overrides default FOS Registration form to add a «city» field.
 *
 * Class RegistrationType
 * @package Dada\UserBundle
 */
class RegistrationType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('city', );
    }

    public function getParent(){
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';

    }

    public function getBlockPrefix(){
        return 'dada_user_registration';
    }
}