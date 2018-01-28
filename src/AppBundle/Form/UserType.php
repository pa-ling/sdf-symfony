<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email',array('label' => 'E-Mail:','attr' => array('class' => 'input-text','data-rule' => 'email')))
            ->add('username', 'text',array('label' => 'Name:','attr' => array('class' => 'input-text','data-rule' => 'minlen:4')))
            ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'first_options'  => array('label' => 'Passwort:', 'attr' => array('class' => 'input-text','data-rule' => 'minlen:4')),
                    'second_options' => array('label' => 'Passwort wiederholen:', 'attr' => array('class' => 'input-text','data-rule' => 'required')),
                    )
                 )
            ->add('save', 'submit', array('label' => 'REGISTER','attr' => array('class' => 'input-btn')))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\Sonata\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'user';
    }
}