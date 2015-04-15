<?php

namespace Masterflow\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('nom', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5))),
            'attr' => array('class' => 'form-control', 'placeholder' => 'Nom du type utilisateur commençant par ROLE_')
        ))
                ->add('description', 'text', array(
            'attr' => array('class' => 'form-control', 'placeholder' => 'Entrez la description du type utilisateur'),
            'required'  => false
        ))
                ->add('actif', 'checkbox', array(
            'attr' => array('class' => 'form-control'),
        ))
                ->add('Enregistrer le type utilisateur', 'submit', array(
            'attr' => array('class' => 'btn btn-primary')
        ));
    }

    public function getName(){
        return 'role';
    }
}