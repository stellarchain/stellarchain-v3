<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType {
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
          $builder
                ->add('username', TextType::class)
                ->add('password', PasswordType::class, [
                    'toggle' => true,
                ])
        ;
     }
}

