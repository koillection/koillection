<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Enum\HistoryFilterEnum;
use App\Model\Search\SearchHistory;
use App\Service\FeatureChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchHistoryType extends AbstractType
{
    public function __construct(
        private readonly FeatureChecker $featureChecker
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [];
        $types[HistoryFilterEnum::FILTER_CLASS_COLLECTION] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_COLLECTION);
        $types[HistoryFilterEnum::FILTER_CLASS_ITEM] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_ITEM);

        if ($this->featureChecker->isFeatureEnabled('tags')) {
            $types[HistoryFilterEnum::FILTER_CLASS_TAG] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_TAG);
            $types[HistoryFilterEnum::FILTER_CLASS_TAG_CATEGORY] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_TAG_CATEGORY);
        }

        if ($this->featureChecker->isFeatureEnabled('albums')) {
            $types[HistoryFilterEnum::FILTER_CLASS_ALBUM] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_ALBUM);
            $types[HistoryFilterEnum::FILTER_CLASS_PHOTO] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_PHOTO);
        }

        if ($this->featureChecker->isFeatureEnabled('wishlists')) {
            $types[HistoryFilterEnum::FILTER_CLASS_WISHLIST] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_WISHLIST);
            $types[HistoryFilterEnum::FILTER_CLASS_WISH] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_WISH);
        }

        $types[HistoryFilterEnum::FILTER_CLASS_TEMPLATE] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_TEMPLATE);
        $types[HistoryFilterEnum::FILTER_CLASS_CHOICE_LIST] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_CHOICE_LIST);
        $types[HistoryFilterEnum::FILTER_CLASS_INVENTORY] = HistoryFilterEnum::getLabel(HistoryFilterEnum::FILTER_CLASS_INVENTORY);

        $builder
            ->add('term', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('classes', ChoiceType::class, [
                'choices' => array_flip($types),
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('types', ChoiceType::class, [
                'choices' => array_flip(HistoryFilterEnum::TYPES_TRANS_KEYS),
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchHistory::class,
            'csrf_protection' => false,
        ]);
    }
}
