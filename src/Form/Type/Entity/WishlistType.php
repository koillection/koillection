<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Wishlist;
use App\Enum\VisibilityEnum;
use App\Form\DataTransformer\Base64ToImageTransformer;
use App\Repository\WishlistRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WishlistType extends AbstractType
{
    public function __construct(
        private readonly Base64ToImageTransformer $base64ToImageTransformer,
        private readonly WishlistRepository $wishlistRepository
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'required' => true,
            ])
            ->add('parent', EntityType::class, [
                'class' => Wishlist::class,
                'choice_label' => 'name',
                'choices' => $this->wishlistRepository->findAllExcludingItselfAndChildren($entity),
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'empty_data' => '',
                'required' => false,
            ])
            ->add('childrenDisplayConfiguration', DisplayConfigurationType::class)
            ->add(
                $builder->create('file', TextType::class, [
                    'required' => false,
                    'label' => false,
                    'model_transformer' => $this->base64ToImageTransformer,
                ])
            )
            ->add('deleteImage', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wishlist::class,
        ]);
    }
}
