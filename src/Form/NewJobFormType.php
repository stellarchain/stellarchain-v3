<?php

namespace App\Form;

use App\Entity\Job;
use App\Entity\JobCategory;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewJobFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add(
                'salary',
                NumberType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please add a budget',
                        ]),
                    ]
                ]
            )
            ->add('description', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter description of the job',
                    ]),
                ],
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please choose location',
                    ]),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => JobCategory::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please choose category',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job::class,
        ]);
    }
}
