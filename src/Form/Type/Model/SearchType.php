<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Enum\DateFormatEnum;
use App\Enum\DisplayModeEnum;
use App\Enum\VisibilityEnum;
use App\Model\Search\Search;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('term', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('createdAt', DateType::class, [
                'input' => 'datetime_immutable',
                'label' => false,
                'required' => false,
                'html5' => false,
                'widget' => 'single_text',
                'format' => $this->security->getUser()?->getDateFormatForForm() ?: DateFormatEnum::FORMAT_HYPHEN_YMD,
            ])
            ->add('searchInData', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('displayMode', ChoiceType::class, [
                'choices' => array_flip(DisplayModeEnum::getDisplayModeLabels()),
                'empty_data' => $builder->getData()->getDisplayMode()
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'csrf_protection' => false,
        ]);
    }
}
