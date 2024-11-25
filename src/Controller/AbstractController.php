<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Album;
use App\Entity\ChoiceList;
use App\Entity\Collection;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Loan;
use App\Entity\Photo;
use App\Entity\Scraper;
use App\Entity\Tag;
use App\Entity\TagCategory;
use App\Entity\Template;
use App\Entity\User;
use App\Entity\Wish;
use App\Entity\Wishlist;
use App\Service\FeatureChecker;
use App\Service\RefreshCachedValuesQueue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AbstractController extends SymfonyAbstractController
{
    public function __construct(
        protected FeatureChecker $featureChecker,
        protected RefreshCachedValuesQueue $refreshCachedValuesQueue
    ) {
    }

    public function denyAccessUnlessFeaturesEnabled(array $features): void
    {
        foreach ($features as $feature) {
            if (!$this->featureChecker->isFeatureEnabled($feature)) {
                throw new AccessDeniedHttpException();
            }
        }
    }

    public function createDeleteForm(
        string $url,
        User|Album|Collection|Inventory|Item|Loan|Photo|TagCategory|Tag|Template|Wish|Wishlist|ChoiceList|Scraper|null $entity = null
    ): FormInterface {
        $params = [];
        if ($entity !== null) {
            $params['id'] = $entity->getId();
        }

        return $this->createFormBuilder()
            ->setAction($this->generateUrl($url, $params))
            ->setMethod(Request::METHOD_POST)
            ->getForm()
        ;
    }
}
