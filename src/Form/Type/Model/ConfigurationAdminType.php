<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Enum\ConfigurationEnum;
use App\Form\Type\Entity\Admin\ConfigurationCheckboxType;
use App\Form\Type\Entity\Admin\ConfigurationChoiceType;
use App\Form\Type\Entity\Admin\ConfigurationTextareaType;
use App\Model\ConfigurationAdmin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationAdminType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('thumbnailsFormat', ConfigurationChoiceType::class, [
                'choices' => array_flip(ConfigurationEnum::getThumbnailFormatsLabels()),
                'placeholder' => ConfigurationEnum::getThumbnailFormatsDefaultLabel(),
                'required' => false,
            ])
            ->add('customLightThemeCss', ConfigurationTextareaType::class, [
                'required' => false,
            ])
            ->add('customDarkThemeCss', ConfigurationTextareaType::class, [
                'required' => false,
            ])
            ->add('enableMetrics', ConfigurationCheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigurationAdmin::class,
        ]);
    }
}
