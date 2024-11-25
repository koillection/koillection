<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\BreadcrumbElement;
use App\Service\BreadcrumbBuilder;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class BreadcrumbRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly BreadcrumbBuilder $breadcrumbBuilder
    ) {
    }

    public function buildBreadcrumb(array $root = [], ?object $entity = null, ?string $action = null, $parent = null): array
    {
        $breadcrumb = [];

        foreach ($root as $element) {
            $rootElement = new BreadcrumbElement();
            $rootElement
                ->setType(BreadcrumbElement::TYPE_ROOT)
                ->setRoute($element['route'])
                ->setLabel($element['trans'])
                ->setParams([])
            ;

            $breadcrumb[] = $rootElement;
        }

        if ($entity !== null) {
            $breadcrumb = [...$breadcrumb, ...$this->breadcrumbBuilder->build($entity, $parent)];
        }

        if (null !== $action) {
            $actionElement = new BreadcrumbElement();
            $actionElement
                ->setType(BreadcrumbElement::TYPE_ACTION)
                ->setLabel($action)
            ;
            $breadcrumb[] = $actionElement;
        }

        $last = array_pop($breadcrumb);
        $last->setClass('last');
        $breadcrumb[] = $last;

        return $breadcrumb;
    }

    public function renderBreadcrumb(Environment $environment, array $breadcrumb): string
    {
        return $environment->render('App/_partials/_breadcrumb/_base.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
