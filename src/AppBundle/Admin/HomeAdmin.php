<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class HomeAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Craniosakrale Therapie', array('description' => 'Hier können Beiträge der Homepage erstellt werden.'))
                ->add('id', 'hidden', array('required' => false))
            ->end()
            ->with('Beitragseinstellungen')
                ->add('titel', 'text', array('label' => 'Titel',
                                             'help' => 'Hier kann der Titel eines Beitrags erstellt werden. <p>* Der Titel ist ein Pflichtfeld.</p>'))
                ->add('topline', 'textarea', array('required' => false,
                                                   'label' => 'Zwischentext',
                                                   'help' => 'Hier kann  ein Text zwischen dem Titel und dem Haupttext erstellt werden. Der Zwischentext ist optional.'))
                ->add('content', 'textarea', array('label' => 'Haupttext',
                                                   'help' => 'Hier kann der Haupttext des Beitrags erstellt werden. <p>* Der Haupttext ist ein Pflichtfeld.</p>'))
            //->add('botline', 'textarea', array('required' => false))
            ->end()
            ->with('Bildeinstellungen')
                ->add('image', null, array('label' => 'Bildauswahl',
                                           'help' => 'Alle Bilder sind über diese Drop-Down-Liste auswählbar. Das "x" auf der rechten Seite dieser Liste, hebt die aktuelle Auswahl auf.'))
                ->add('imagePosition', null, array('label' => 'Bildposition rechts?',
                                                   'help' => 'Sollte das Häkchen in der Box gesetzt werden, wird das Bild rechts vom Text positioniert. <p>Bleibt die Box leer, wird das Bild links vom Text positioniert. </p>'))
            ->end()
            //->with('Tragen Sie alle Antwortmöglichkeiten ein')
            //->end()

            //->with('Video')
            //    ->add('movie', 'textarea', array('required' => false, 'help' => 'Zur Videoauswahl den Namen der Videodatei angeben.'))
            //->end()
        ;
    }

    // Fields to be shown on filter forms // der Filter rechts oben
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('titel', null, array('label' => 'Titel'))
            ->add('image', null, array('label' => 'Bild'))
            ->add('imagePosition', null, array('label' => 'Bild rechts?'))
            ->add('topline', null, array('label' => 'Zwischentext'))
            ->add('content', null, array('label' => 'Haupttext'))
            //->add('botline')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper){}
}