<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class LikeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entityType', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type('string')
                ]
            ])
            ->add('entityId', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type('integer')
                ]
            ]);
    }
}
