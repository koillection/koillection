<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\ChoiceList;
use App\Entity\Field;
use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FieldType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = array_flip(DatumTypeEnum::getTypesLabels());
        $translator = $this->translator;

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $types,
                'expanded' => false,
                'multiple' => false,
                'label' => false,
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'choice_attr' => function ($choice, string $key, mixed $value) use ($translator) {
                    return ['title' => $translator->trans('global.visibilities.'.$value) . ' - ' . $translator->trans('global.visibilities.'.$value.'.description', domain: 'javascript')];
                },
                'required' => true,
            ])
            ->add('position', HiddenType::class, [
                'required' => false,
            ])
            ->add('choiceList', EntityType::class, [
                'class' => ChoiceList::class,
                'required' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            static function (FormEvent $event): void {
                $data = $event->getData();
                if (DatumTypeEnum::TYPE_CHOICE_LIST !== $data['type']) {
                    $data['choiceList'] = null;
                }

                $event->setData($data);
            }
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Field::class,
        ]);
    }
}
