<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Entity\User;
use App\Form\DataTransformer\Base64ToImageTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType as SymfonyPasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function __construct(
        private readonly Base64ToImageTransformer $base64ToImageTransformer
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create('file', TextType::class, [
                    'required' => false,
                    'label' => false,
                    'model_transformer' => $this->base64ToImageTransformer,
                ])
            )
            ->add('deleteAvatar', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => SymfonyPasswordType::class,
                'required' => false,
                'invalid_message' => 'error.password.not_matching',
            ])
            ->add('username', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'attr' => ['length' => 255],
                'required' => true,
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
