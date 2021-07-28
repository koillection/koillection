<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Entity\User;
use App\Enum\CurrencyEnum;
use App\Enum\DateFormatEnum;
use App\Enum\LocaleEnum;
use App\Enum\ThemeEnum;
use App\Enum\VisibilityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timezone', TimezoneType::class, [
                'required' => true
            ])
            ->add('dateFormat', ChoiceType::class, [
                'choices' => DateFormatEnum::getChoicesList(),
                'required' => true
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => array_flip(CurrencyEnum::getCurrencyLabels()),
                'required' => true
            ])
            ->add('locale', ChoiceType::class, [
                'choices' => array_flip(LocaleEnum::getLocaleLabels()),
                'required' => true
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'required' => true
            ])
            ->add('theme', ChoiceType::class, [
                'choices' => array_flip(ThemeEnum::getThemeLabels()),
                'multiple' => false,
                'expanded' => true
            ])
            ->add('darkModeEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('automaticDarkModeStartAt', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('automaticDarkModeEndAt', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('wishlistsFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('tagsFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('signsFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('albumsFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('loansFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('templatesFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('historyFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('statisticsFeatureEnabled', CheckboxType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
