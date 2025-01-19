<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\DisplayModeEnum;
use App\Form\Type\Model\AdvancedItemSearch\AdvancedItemSearchType;
use App\Form\Type\Model\SearchType;
use App\Model\AdvancedItemSearch\AdvancedItemSearch;
use App\Model\AdvancedItemSearch\Block;
use App\Model\Search\Search;
use App\Repository\AlbumRepository;
use App\Repository\CollectionRepository;
use App\Repository\ItemRepository;
use App\Repository\TagRepository;
use App\Repository\WishlistRepository;
use App\Service\Autocompleter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route(path: '/search', name: 'app_search_index', methods: ['GET', 'POST'])]
    #[Route(path: '/user/{username}/search', name: 'app_shared_search_index', methods: ['GET'])]
    public function index(
        Request $request,
        CollectionRepository $collectionRepository,
        ItemRepository $itemRepository,
        TagRepository $tagRepository,
        AlbumRepository $albumRepository,
        WishlistRepository $wishlistRepository,
        ManagerRegistry $managerRegistry
    ): Response {
        $results = [];

        $search = new Search();
        $search->setDisplayMode($this->getUser()?->getSearchResultsDisplayMode() ?? DisplayModeEnum::DISPLAY_MODE_GRID);

        $form = $this->createForm(SearchType::class, $search, [
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $results['collections'] = $collectionRepository->findForSearch($search);
            $results['items'] = $itemRepository->findForSearch($search);

            if ($this->featureChecker->isFeatureEnabled('tags')) {
                $results['tags'] = $tagRepository->findForSearch($search);
            }

            if ($this->featureChecker->isFeatureEnabled('albums')) {
                $results['albums'] = $albumRepository->findForSearch($search);
            }

            if ($this->featureChecker->isFeatureEnabled('wishlists')) {
                $results['wishlists'] = $wishlistRepository->findForSearch($search);
            }

            if ($this->getUser() && $this->getUser()->getSearchResultsDisplayMode() !== $search->getDisplayMode()) {
                $this->getUser()->setSearchResultsDisplayMode($search->getDisplayMode());
                $managerRegistry->getManager()->flush();
            }
        }

        return $this->render('App/Search/index.html.twig', [
            'form' => $form,
            'results' => $results,
            'search' => $search
        ]);
    }

    #[Route(path: '/advanced-item-search', name: 'app_search_advanced_item_search', methods: ['GET', 'POST'])]
    public function advancedItemSearch(
        Request $request
    ): Response {
        $results = [];

        $search = new AdvancedItemSearch();
        $search->setDisplayMode($this->getUser()?->getSearchResultsDisplayMode() ?? DisplayModeEnum::DISPLAY_MODE_GRID);
        $form = $this->createForm(AdvancedItemSearchType::class, $search);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($search);
        }

        return $this->render('App/Search/advanced-item-search.html.twig', [
            'form' => $form,
            'search' => $search,
            'results' => $results
        ]);
    }

    #[Route(path: '/search/autocomplete/{term}', name: 'app_search_autocomplete', methods: ['GET', 'POST'])]
    #[Route(path: '/user/{username}/search/autocomplete/{term}', name: 'app_shared_search_autocomplete', methods: ['GET'])]
    public function autocomplete(Autocompleter $autocompleter, string $term): Response
    {
        $results = $autocompleter->findForAutocomplete($term);

        return new JsonResponse($results);
    }
}
