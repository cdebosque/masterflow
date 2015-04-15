<?php

namespace Masterflow\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RoutePermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){

        //echo "<pre>"; print_r($options['data']->getPossibleRoles()); echo "</pre>";

        $choices= array();
        if(!empty($options['data'])){
            foreach($options['data']->getPossibleRoles() as $role){
                $choices[$role->getId()] = $role->getNom();
            }
        }
        
        $builder
            ->add('roles', 'choice', array(
            'choices' => $choices,
            'expanded' => false,
            'multiple'  => true,
            'attr' => array('class' => 'form-control')
            //'constraints' => new Assert\Choice(array(1, 2)),
        ))->add('Enregistrer les permissions', 'submit', array(
            'attr' => array('class' => 'btn btn-primary')
        ));
    }

    public function getName(){
        return 'routePermissionsType';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Masterflow\Domain\RolePermissions',
        );
    }
}