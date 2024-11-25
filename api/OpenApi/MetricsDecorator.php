<?php

declare(strict_types=1);

namespace Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\Enum\ConfigurationEnum;
use App\Repository\ConfigurationRepository;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final readonly class MetricsDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {
    }

    #[\Override]
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $pathItem = new PathItem(
            ref: 'Metrics',
            get: new Operation(
                operationId: 'getMetrics',
                tags: ['Metrics'],
                responses: [
                    '200' => [
                        'description' => 'Get metrics',
                        'content' => [
                            'text/plain' => [],
                        ],
                    ],
                ],
                summary: 'Get metrics.',
            ),
        );
        $openApi->getPaths()->addPath('/api/metrics', $pathItem);

        return $openApi;
    }
}
