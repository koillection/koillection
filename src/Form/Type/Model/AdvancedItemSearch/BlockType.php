<?php

declare(strict_types=1);

namespace App\Form\Type\Model\AdvancedItemSearch;

use App\Enum\AdvancedItemSearch\ConditionEnum;
use App\Model\AdvancedItemSearch\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('condition', ChoiceType::class, [
                'choices' => array_flip(ConditionEnum::getConditionLabels()),
                'required' => true
            ])
            ->add('filters', CollectionType::class, [
                'entry_type' => FilterType::class,
                'entry_options' => ['label' => false],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Block::class
        ]);
    }
}
