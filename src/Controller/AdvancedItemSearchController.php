<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\AdvancedItemSearch\OperatorEnum;
use App\Enum\DatumTypeEnum;
use App\Enum\DisplayModeEnum;
use App\Form\Type\Model\AdvancedItemSearch\AdvancedItemSearchType;
use App\Model\AdvancedItemSearch\AdvancedItemSearch;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdvancedItemSearchController extends AbstractController
{
    #[Route(path: '/advanced-item-search', name: 'app_advanced_item_search_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request
    ): Response {
        $results = [];

        $search = new AdvancedItemSearch();
        $search->setDisplayMode($this->getUser()?->getSearchResultsDisplayMode() ?? DisplayModeEnum::DISPLAY_MODE_GRID);
        $form = $this->createForm(AdvancedItemSearchType::class, $search);

        $form->handleRequest($request);

        //dd($form->createView()->children['blocks']->children[0]->children['filters']->children[0]->children['datum']);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($search);
        }

        return $this->render('App/AdvancedItemSearch/index.html.twig', [
            'form' => $form,
            'search' => $search,
            'results' => $results
        ]);
    }

    #[Route(path: '/advanced-item-search/load-operator-and-value-inputs/{value}', name: 'app_advanced_item_search_load_operator_and_value_inputs', methods: ['GET', 'POST'])]
    public function loadOperatorAndValueInputs(string $value) : Response
    {
        list($label, $type) = explode('_koillection_separator_', $value);

        $operators = match ($type) {
            DatumTypeEnum::TYPE_TEXT => [
                OperatorEnum::OPERATOR_EQUAL => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_EQUAL),
                OperatorEnum::OPERATOR_CONTAINS => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_CONTAINS)
            ]
        };

        $operatorInput = $this->render('App/AdvancedItemSearch/_input_operator.html.twig', [
            'operators' => $operators,
        ])->getContent();

        $valueInput = match ($type) {
            DatumTypeEnum::TYPE_TEXT => $this->render('App/AdvancedItemSearch/_input_text.html.twig')->getContent()
        };

        return new JsonResponse([
            'operatorInput' => $operatorInput,
            'valueInput' => $valueInput
        ]);
    }
}
