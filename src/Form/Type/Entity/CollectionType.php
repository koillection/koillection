<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Collection;
use App\Entity\Template;
use App\Enum\DatumTypeEnum;
use App\Enum\ReservedLabelEnum;
use App\Enum\VisibilityEnum;
use App\Form\DataTransformer\Base64ToImageTransformer;
use App\Repository\CollectionRepository;
use App\Repository\DatumRepository;
use App\Repository\TemplateRepository;
use App\Service\FeatureChecker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as SymfonyCollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionType extends AbstractType
{
    public function __construct(
        private readonly Base64ToImageTransformer $base64ToImageTransformer,
        private readonly FeatureChecker $featureChecker,
        private readonly CollectionRepository $collectionRepository,
        private readonly TemplateRepository $templateRepository,
        private readonly DatumRepository $datumRepository
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();

        $builder
            ->add('title', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'required' => true,
            ])
            ->add('itemsDefaultTemplate', EntityType::class, [
                'class' => Template::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'empty_data' => '',
                'required' => false,
            ])
            ->add('childrenDisplayConfiguration', DisplayConfigurationType::class, [
                'hasLabel' => true,
                'hasShowVisibility' => true,
                'hasShowActions' => true,
                'hasShowNumberOfChildren' => true,
                'hasShowNumberOfItems' => true,
                'sorting' => ['form.item_sorting.default_value' => null,
                    'form.item_sorting.number_of_children' => ReservedLabelEnum::NUMBER_OF_CHILDREN,
                    'form.item_sorting.number_of_items' => ReservedLabelEnum::NUMBER_OF_ITEMS,
                    ...$this->datumRepository->findAllChildrenLabelsInCollection($entity, DatumTypeEnum::TEXT_TYPES)
                ],
                'columns' => [
                    'availableColumnLabels' => $this->datumRepository->findAllChildrenLabelsInCollection($entity, DatumTypeEnum::TEXT_TYPES),
                    'selectedColumnsLabels' => $entity->getChildrenDisplayConfiguration()->getColumns()
                ]
            ])
            ->add('itemsDisplayConfiguration', DisplayConfigurationType::class, [
                'hasLabel' => true,
                'hasShowVisibility' => true,
                'hasShowActions' => true,
                'hasShowItemQuantities' => true,
                'sorting' => [
                    'form.item_sorting.default_value' => null,
                    'form.item_sorting.quantity' => ReservedLabelEnum::QUANTITY,
                    ...$this->datumRepository->findAllItemsLabelsInCollection($entity, DatumTypeEnum::TEXT_TYPES)],
                'columns' => [
                    'availableColumnLabels' => $this->datumRepository->findAllItemsLabelsInCollection($entity, DatumTypeEnum::TEXT_TYPES),
                    'selectedColumnsLabels' => $entity->getItemsDisplayConfiguration()->getColumns()
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => Collection::class,
                'choice_label' => 'title',
                'choices' => $this->collectionRepository->findAllExcludingItselfAndChildren($entity),
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'empty_data' => '',
                'required' => false,
            ])
            ->add('data', SymfonyCollectionType::class, [
                'entry_type' => DatumType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add(
                $builder->create('file', TextType::class, [
                    'required' => false,
                    'label' => false,
                    'model_transformer' => $this->base64ToImageTransformer,
                ])
            )
            ->add('deleteImage', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('scrapedFromUrl', HiddenType::class, [
                'required' => false,
            ])
        ;

        if ($this->featureChecker->isFeatureEnabled('templates')) {
            $builder->add('template', EntityType::class, [
                'class' => Template::class,
                'choice_label' => 'name',
                'choices' => $this->templateRepository->findAll(),
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'required' => false,
                'mapped' => false,
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Collection::class,
        ]);
    }
}
