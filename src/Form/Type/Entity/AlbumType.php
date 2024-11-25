<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Album;
use App\Enum\VisibilityEnum;
use App\Form\DataTransformer\Base64ToImageTransformer;
use App\Repository\AlbumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumType extends AbstractType
{
    public function __construct(
        private readonly Base64ToImageTransformer $base64ToImageTransformer,
        private readonly AlbumRepository $albumRepository
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();

        $builder
            ->add('title', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'required' => true,
            ])
            ->add('childrenDisplayConfiguration', DisplayConfigurationType::class)
            ->add('photosDisplayConfiguration', DisplayConfigurationType::class)
            ->add('parent', EntityType::class, [
                'class' => Album::class,
                'choice_label' => 'title',
                'choices' => $this->albumRepository->findAllExcludingItselfAndChildren($entity),
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'empty_data' => '',
                'required' => false,
            ])
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
            'data_class' => Album::class,
        ]);
    }
}
