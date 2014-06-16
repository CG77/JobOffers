<?php
/**
 * Created by PhpStorm.
 * User: a.haddad
 * Date: 09/04/14
 * Time: 15:00
 */

namespace Job\OffersBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobPostType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('gender','choice',
                array('expanded' => true, 'multiple' => false,
                'choices' =>
                    array(
                        'mr'     => 'M.',
                        'mme'   => 'Mme'
                    ),
                    'data' => 'mr'
                )
            )
            ->add('firstName','text',array('required'=>false))
            ->add('lastName', 'text',array('required'=>false))
            ->add('email', 'text',array('required'=>false))
            ->add('cv', 'file',array('required'=>false))
            ->add('motivation','file',array('required'=>false))
            ->add('captcha', 'genemu_recaptcha',array('mapped'=>false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Job\OffersBundle\Entity\JobPost'
        ));
    }

    public function getName() {
        return 'offers_post_type';
    }
} 