<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\Collection;
use App\Entity\Item;
use App\Entity\Template;
use App\Enum\VisibilityEnum;
use App\Form\DataTransformer\JsonToItemTransformer;
use App\Form\DataTransformer\JsonToTagTransformer;
use App\Form\DataTransformer\UrlToImageTransformer;
use App\Repository\CollectionRepository;
use App\Repository\TemplateRepository;
use App\Service\FeatureChecker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as SymfonyCollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function __construct(
        private readonly JsonToTagTransformer $jsonToTagTransformer,
        private readonly JsonToItemTransformer $jsonToItemTransformer,
        private readonly UrlToImageTransformer $urlToImageTransformer,
        private readonly FeatureChecker $featureChecker,
        private readonly CollectionRepository $collectionRepository,
        private readonly TemplateRepository $templateRepository
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['length' => 255],
                'required' => true,
            ])
            ->add('quantity', IntegerType::class, [
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add( // Used for scraping
                $builder->create('fileUrl', HiddenType::class, [
                    'required' => false,
                    'label' => false,
                    'model_transformer' => $this->urlToImageTransformer,
                    'getter' => static function () {
                        return null;
                    },
                    'setter' => static function (Item &$item, ?File $file): void {
                        if ($file instanceof File) {
                            $item->setFile($file);
                        }
                    },
                ])
            )
            ->add('collection', EntityType::class, [
                'class' => Collection::class,
                'choice_label' => 'title',
                'choices' => $this->collectionRepository->findAll(),
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'required' => true,
            ])
            ->add('data', SymfonyCollectionType::class, [
                'entry_type' => DatumType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => array_flip(VisibilityEnum::getVisibilityLabels()),
                'required' => true,
            ])
            ->add(
                $builder->create('relatedItems', HiddenType::class, [
                    'required' => false,
                    'model_transformer' => $this->jsonToItemTransformer,
                ])
            )
            ->add('scrapedFromUrl', HiddenType::class, [
                'required' => false,
            ])
        ;

        if ($this->featureChecker->isFeatureEnabled('tags')) {
            $builder->add(
                $builder->create('tags', TextType::class, [
                    'required' => false,
                    'model_transformer' => $this->jsonToTagTransformer,
                ])
            );
        }

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
            'data_class' => Item::class,
        ]);
    }
}
