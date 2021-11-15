<?php

namespace MD\Bundle\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BannerType extends AbstractType {

    public $placmentData = 1;

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('title', null, array('label' => 'Banner Title'))
                ->add('text', 'textarea', array('label' => 'Banner Text', 'required' => false))
                ->add('placement', 'choice', array(
                    'choices' => array(
                        1 => 'Home Page',
                        2 => 'About us',
                        8 => 'Our Strategy',
                        9 => 'Our Team',
                        10 => 'Media Center',
                        11 => 'News',
                        12 => 'Contact us',
                        3 => 'Global engineering & supply',
                        4 => 'Med. trade',
                        5 => 'Technology',
                        6 => '4Sale egypt',
                        7 => 'Global for real estate development',
                        13 => 'Hideen',
                    ),
                ))
                ->add('url', null, array('label' => 'Banner Url'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MD\Bundle\CMSBundle\Entity\Banner'
        ));
    }

    public function getName() {
        return 'md_bundle_cmsbundle_bannertype';
    }

}
