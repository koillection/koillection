<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Entity\User;
use App\Enum\CurrencyEnum;
use App\Enum\DateFormatEnum;
use App\Enum\ThemeEnum;
use App\Enum\VisibilityEnum;
use App\Service\LocaleHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function __construct(
        private readonly LocaleHelper $localeHelper
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('timezone', TimezoneType::class, [
                'required' => true,
            ])
            ->add('dateFormat', ChoiceType::class, [
                'choices' => DateFormatEnum::getChoicesList(),
                'required' => true,
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => array_flip(CurrencyEnum::getCurrencyLabels()),
                'required' => true,
            ])
            ->add('locale', ChoiceType::class, [
                'choices' => array_flip($this->localeHelper->getLocaleLabels()),
                'required' => true,
            ])
            ->add('searchInDataByDefaultEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('displayItemsNameInGridView', CheckboxType::class, [
                'required' => false,
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'required' => true,
            ])
            ->add('theme', ChoiceType::class, [
                'choices' => array_flip(ThemeEnum::getThemesLabels()),
                'required' => true,
            ])
            ->add('wishlistsFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('tagsFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('signsFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('albumsFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('loansFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('templatesFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('historyFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('statisticsFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('scrapingFeatureEnabled', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
