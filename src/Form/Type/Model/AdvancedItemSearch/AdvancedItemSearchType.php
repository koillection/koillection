<?php

declare(strict_types=1);

namespace App\Form\Type\Model\AdvancedItemSearch;

use App\Enum\DateFormatEnum;
use App\Enum\DisplayModeEnum;
use App\Enum\VisibilityEnum;
use App\Model\AdvancedItemSearch\AdvancedItemSearch;
use App\Model\Search\Search;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvancedItemSearchType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('blocks', CollectionType::class, [
                'entry_type' => BlockType::class,
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
            'data_class' => AdvancedItemSearch::class
        ]);
    }
}
