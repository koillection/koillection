<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\DisplayConfiguration;
use App\Enum\DisplayModeEnum;
use App\Enum\SortingDirectionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayConfigurationType extends AbstractType
{
    private array $preSubmitColumns = [];

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayMode', ChoiceType::class, [
                'choices' => array_flip(DisplayModeEnum::getDisplayModeLabels()),
                'required' => true,
            ])
        ;

        if ($options['hasLabel']) {
            $builder->add('label', TextType::class, [
                'attr' => ['length' => 255],
                'required' => false,
            ]);
        }

        if ($options['hasShowVisibility']) {
            $builder->add('showVisibility', CheckboxType::class, [
                'required' => false,
            ]);
        }

        if ($options['hasShowActions']) {
            $builder->add('showActions', CheckboxType::class, [
                'required' => false,
            ]);
        }

        if ($options['hasShowNumberOfChildren']) {
            $builder->add('showNumberOfChildren', CheckboxType::class, [
                'required' => false,
            ]);
        }

        if ($options['hasShowNumberOfItems']) {
            $builder->add('showNumberOfItems', CheckboxType::class, [
                'required' => false,
            ]);
        }

        if ($options['hasShowItemQuantities']) {
            $builder->add('showItemQuantities', CheckboxType::class, [
                'required' => false,
            ]);
        }

        if (!empty($options['sorting'])) {
            $sortingProperties = [];
            foreach ($options['sorting'] as $key => $label) {
                if (\is_array($label)) {
                    $sortingProperties[$label['label']] = $label['label'];
                } else {
                    $sortingProperties[$key] = $label;
                }
            }

            $builder
                ->add('sortingProperty', ChoiceType::class, [
                    'choices' => $sortingProperties,
                    'required' => true,
                ])
                ->add('sortingDirection', ChoiceType::class, [
                    'choices' => array_flip(SortingDirectionEnum::getSortingDirectionLabels()),
                    'required' => true,
                ])
            ;
        }

        $availableColumnLabels = $options['columns']['availableColumnLabels'];
        if (!empty($availableColumnLabels)) {
            // Extract possible columns for a collection based on items
            $columns = [];
            foreach ($availableColumnLabels as $label) {
                $columns[$label['label']] = $label['label'];
            }

            // Move already selected columns to the top of the array
            $alreadySelectedColumns = [];
            if ($options['columns']['selectedColumnsLabels']) {
                $alreadySelectedColumns = array_reverse($options['columns']['selectedColumnsLabels']);
            }

            foreach ($alreadySelectedColumns as $alreadySelectedColumn) {
                unset($columns[$alreadySelectedColumn]);
                array_unshift($columns, [$alreadySelectedColumn => $alreadySelectedColumn]);
            }

            $builder
                ->add('columns', ChoiceType::class, [
                    'choices' => $columns,
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                ])
            ;

            $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event) use ($availableColumnLabels): void {
                $displayConfiguration = $event->getData();
                $found = false;
                foreach ($availableColumnLabels as $label) {
                    if ($label['label'] === $displayConfiguration->getSortingProperty()) {
                        $displayConfiguration->setSortingType($label['type']);
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $displayConfiguration->setSortingType(null);
                }
            });

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                if (isset($event->getData()['columns'])) {
                    $this->preSubmitColumns = $event->getData()['columns'];
                }
            });

            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();
                $data->setColumns($this->preSubmitColumns);

                $event->setData($data);
            });
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DisplayConfiguration::class,
            'hasLabel' => false,
            'hasShowVisibility' => false,
            'hasShowActions' => false,
            'hasShowNumberOfChildren' => false,
            'hasShowNumberOfItems' => false,
            'hasShowItemQuantities' => false,
            'sorting' => [],
            'columns' => [
                'hasColumns' => true,
                'availableColumnLabels' => [],
                'selectedColumnsLabels' => []
            ],
        ]);
    }
}
