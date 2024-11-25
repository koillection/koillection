<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Loan;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoanType extends AbstractType
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lentAt', DateType::class, [
                'input' => 'datetime_immutable',
                'required' => true,
                'html5' => false,
                'widget' => 'single_text',
                'format' => $this->security->getUser()->getDateFormatForForm(),
            ])
            ->add('lentTo', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
        ]);
    }
}
