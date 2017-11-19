<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class FotografenAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Fotografendetails')
                ->add('id', 'hidden', array('required' => false))
                ->add('vorname', 'text', array(
                    'label' => 'Vorname',
                    'help' => 'Hier kann der Vorname des Fotografen erstellt werden. <p>* Der Vorname ist ein Pflichtfeld.</p>'))
                ->add('name', 'text', array(
                    'label' => 'Name',
                    'help' => 'Hier kann der Name des Fotografen erstellt werden. <p>* Der Name ist ein Pflichtfeld.</p>'))
                ->add('shortdescr', 'textarea', array('required' => false,
                    'label' => 'Kurze Beschreibung des Fotografen',
                    'help' => 'Hier kann eine kurze Beschreibung des Fotografen hinzugefügt werden.'))
                ->add('longdescr', 'textarea', array('required' => false,
                    'label' => 'Lange Beschreibung des Fotografen',
                    'help' => 'Hier kann eine lange Beschreibung des Fotografen hinzugefügt werden.'))
                ->add('previmage', null, array('required' => false,
                    'label' => 'Vorschaubildauswahl',
                    'help' => 'Alle Bilder sind über diese Drop-Down-Liste auswählbar. Das "x" auf der rechten Seite dieser Liste, hebt die aktuelle Auswahl auf.'))
            ->end()
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
            ->add('vorname', null, array('label' => 'Vorname'))
            ->add('name', null, array('label' => 'Name'))
            ->add('shortdescr', null, array('label' => 'Kurzbeschreibung'))
            ->add('longdescr', null, array('label' => 'Langbeschreibung'))
            ->add('previmage', null, array('label' => 'Vorschaubild'))

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