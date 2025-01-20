<?php

declare(strict_types=1);

namespace App\Form\Type\Model\AdvancedItemSearch;

use App\Enum\AdvancedItemSearch\ConditionEnum;
use App\Enum\AdvancedItemSearch\OperatorEnum;
use App\Enum\AdvancedItemSearch\TypeEnum;
use App\Enum\DatumTypeEnum;
use App\Model\AdvancedItemSearch\Block;
use App\Model\AdvancedItemSearch\Filter;
use App\Repository\DatumRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function __construct(private readonly DatumRepository $datumRepository)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('condition', ChoiceType::class, [
                'choices' => array_merge(['' => ''], array_flip(ConditionEnum::getConditionLabels())),
                'required' => false,
                'label' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_merge(['' => ''], array_flip(TypeEnum::getTypeLabels())),
                'required' => true,
                'label' => false
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event): void {
                $form = $event->getForm();
                $data = $event->getData();

                $form
                    ->add('value', TextType::class, [
                        'required' => true,
                    ])
                ;

                if ($data['type'] === TypeEnum::TYPE_NAME) {
                    $form
                        ->add('operator', ChoiceType::class, [
                            'choices' => array_flip(OperatorEnum::getOperatorsByType('item_name')),
                            'required' => true,
                        ])
                    ;
                }

                if ($data['type'] === TypeEnum::TYPE_DATUM) {
                    $labels = [];
                    $labels[''] = '';

                    foreach ($this->datumRepository->findAllUniqueLabels() as $datum) {
                        $labels["{$datum['label']} <i>({$datum['type']})</i>"] = "{$datum['label']}_koillection_separator_{$datum['type']}";
                    }

                    list($label, $type) = explode('_koillection_separator_', $data['datum']);

                    $form
                        ->add('operator', ChoiceType::class, [
                            'choices' => array_flip(OperatorEnum::getOperatorsByType($type)),
                            'required' => true,
                        ])
                        ->add('datum', ChoiceType::class, [
                            'choices' => $labels,
                            'required' => true,
                            'label' => false,
                            'getter' => function (Filter $filter, FormInterface $form): ?string {
                                if ($filter->getDatumLabel() && $filter->getDatumType()) {
                                    return "{$filter->getDatumLabel()}_koillection_separator_({$filter->getDatumType()})";
                                }

                                return null;
                            },
                            'setter' => function (Filter $filter, ?string $value, FormInterface $form): void {
                                list($label, $type) = explode('_koillection_separator_', $value);

                                $filter
                                    ->setDatumLabel($label)
                                    ->setDatumType($type)
                                ;
                            },
                        ])
                    ;
                }
            }
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class
        ]);
    }
}
