<?php

namespace App\Form;

use App\Entity\Job;
use App\Entity\JobCategory;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewJobFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('salary')
            ->add('description')
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'choice_value' => 'id'
            ])
            ->add('category', EntityType::class, [
                'class' => JobCategory::class,
                'choice_label' => 'name',
                'choice_value' => 'id'
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job::class,
        ]);
    }
}
