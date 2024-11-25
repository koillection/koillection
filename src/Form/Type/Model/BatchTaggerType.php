<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Form\DataTransformer\JsonToTagTransformer;
use App\Model\BatchTagger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchTaggerType extends AbstractType
{
    public function __construct(
        private readonly JsonToTagTransformer $jsonToTagTransformer
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create('tags', TextType::class, [
                    'required' => true,
                    'model_transformer' => $this->jsonToTagTransformer,
                ])
            )
            ->add('recursive', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BatchTagger::class,
        ]);
    }
}
