<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Entity\Path;
use App\Entity\Scraper;
use App\Enum\ScraperTypeEnum;
use App\Model\ScrapingCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScrapingCollectionType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'required' => false,
                'default_protocol' => 'http'
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('scraper', EntityType::class, [
                'class' => Scraper::class,
                'query_builder' => static function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('s')
                        ->where('s.type = :type')
                        ->setParameter('type', ScraperTypeEnum::TYPE_COLLECTION)
                        ->orderBy('s.name', 'ASC')
                    ;
                },
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false,
                'choice_name' => null,
                'required' => true,
            ])
            ->add('scrapName', CheckboxType::class, [
                'required' => false,
            ])
            ->add('scrapImage', CheckboxType::class, [
                'required' => false,
            ])
        ;

        $formModifier = static function (FormInterface $form, ?Scraper $scraper = null): void {
            $choices = $scraper instanceof \App\Entity\Scraper ? $scraper->getDataPaths() : [];
            $form->add('dataToScrap', EntityType::class, [
                'class' => Path::class,
                'choice_label' => 'name',
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) use ($formModifier): void {
                $data = $event->getData();
                $formModifier($event->getForm(), $data->getScraper());
            }
        );

        $builder->get('scraper')->addEventListener(
            FormEvents::POST_SUBMIT,
            static function (FormEvent $event) use ($formModifier): void {
                $scraper = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $scraper);
            }
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScrapingCollection::class,
            'choices' => []
        ]);
    }
}
