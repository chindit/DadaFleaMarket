<?php

namespace Dada\AdvertisementBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Dada\AdvertisementBundle\Form\ImageType;


class AdvertisementType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('images', CollectionType::class, array('entry_type' => ImageType::class, 'allow_add' => true, 'allow_delete' => true))
            ->add('price', IntegerType::class, array('label' => 'Prix (€)', 'attr' => array('step' => '0.01', 'min' => "0", 'value' => "0")))
            ->add('location', TextType::class, array('label' => 'Emplacement'))
            ->add('current_location', ButtonType::class, array('attr' => array('class' => 'btn btn-success'), 'label' => 'Utiliser ma position actuelle'))
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('category', EntityType::class, array('class' => 'DadaAdvertisementBundle:Category', 'choice_label' => 'name', 'multiple' => false, 'label' => 'Catégorie'))
            ->add('public', CheckboxType::class, array('attr' => array('checked' => true), 'label' => 'Publier l\'annonce'))
            ->add('submit', SubmitType::class, array('label' => 'Envoyer', 'attr' => array('class' => 'btn btn-primary')))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dada\AdvertisementBundle\Entity\Advertisement',
            'translation_domain' => 'forms'
        ));
    }
}
