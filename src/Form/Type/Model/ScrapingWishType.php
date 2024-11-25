<?php

declare(strict_types=1);

namespace App\Form\Type\Model;

use App\Entity\Scraper;
use App\Enum\ScraperTypeEnum;
use App\Model\ScrapingWish;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScrapingWishType extends AbstractType
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
                        ->setParameter('type', ScraperTypeEnum::TYPE_WISH)
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
            ->add('scrapPrice', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScrapingWish::class,
            'choices' => []
        ]);
    }
}
