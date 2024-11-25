<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Collection;
use App\Entity\Item;
use App\Entity\Path;
use App\Enum\DatumTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [];

        $typesAvailableForScraping = match ($options['for_class']) {
            Item::class => DatumTypeEnum::AVAILABLE_FOR_ITEM_SCRAPING,
            Collection::class => DatumTypeEnum::AVAILABLE_FOR_SCRAPING,
            default => []
        };

        foreach ($typesAvailableForScraping as $type) {
            $types[DatumTypeEnum::getTypeLabel($type)] = $type;
        }

        $builder
            ->add('name', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
            ->add('path', TextType::class, [
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $types,
                'expanded' => false,
                'multiple' => false,
                'label' => false,
            ])
            ->add('position', HiddenType::class, [
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Path::class,
        ]);

        $resolver->setRequired([
            'for_class',
        ]);
    }
}
