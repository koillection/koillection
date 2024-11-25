<?php

declare(strict_types=1);

namespace App\Form\Type\Entity\Admin;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationCheckboxType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', CheckboxType::class, [
                'required' => $options['required'],
                'model_transformer' => new CallbackTransformer(
                    static function ($value) {
                        return $value === '1';
                    },
                    static function ($value) {
                        return $value === true ? '1' : '0';
                    }
                ),
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
            'required' => false
        ]);
    }
}
