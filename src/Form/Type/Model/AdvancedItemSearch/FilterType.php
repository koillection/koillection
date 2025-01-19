<?php

declare(strict_types=1);

namespace App\Form\Type\Model\AdvancedItemSearch;

use App\Enum\AdvancedItemSearch\ConditionEnum;
use App\Enum\AdvancedItemSearch\OperatorEnum;
use App\Enum\AdvancedItemSearch\TypeEnum;
use App\Model\AdvancedItemSearch\Block;
use App\Model\AdvancedItemSearch\Filter;
use App\Repository\DatumRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
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
        $data = [];
        foreach ($this->datumRepository->findAllUniqueLabels() as $datum) {
            $data["{$datum['label']} <i>({$datum['type']})</i>"] = "{$datum['label']}_koillection_separator_({$datum['type']})";
        }

        $builder
            ->add('condition', ChoiceType::class, [
                'choices' => array_flip(ConditionEnum::getConditionLabels()),
                'required' => true,
                'label' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_flip(TypeEnum::getTypeLabels()),
                'required' => true,
                'label' => false
            ])
            ->add('datum', ChoiceType::class, [
                'choices' => $data,
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
            ->add('operator', ChoiceType::class, [
                'choices' => array_flip(OperatorEnum::getOperatorLabels()),
                'required' => true,
                'label' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class
        ]);
    }
}
