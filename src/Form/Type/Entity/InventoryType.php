<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Inventory;
use App\Form\DataTransformer\StringToInventoryContentTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryType extends AbstractType
{
    public function __construct(
        private readonly StringToInventoryContentTransformer $stringToInventoryContentTransformer
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
            ->add(
                $builder->create('content', HiddenType::class, [
                    'required' => false,
                    'model_transformer' => $this->stringToInventoryContentTransformer,
                ])
            )
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventory::class,
        ]);
    }
}
