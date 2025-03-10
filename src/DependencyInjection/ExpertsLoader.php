<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ModelflowAi\Integration\Symfony\DependencyInjection;

use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Experts\Expert;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type ExpertConfigType from ModelflowAiBundle
 */
final readonly class ExpertsLoader
{
    public function __construct(
        private ContainerConfigurator $container,
    ) {
        // Check if experts package is installed
        if (!\class_exists(Expert::class)) {
            throw new \Exception(
                'Experts package is enabled but the package is not installed. ' .
                'Please install it with composer require modelflow-ai/experts',
            );
        }
    }

    /**
     * @param ExpertConfigType $expert
     */
    public function loadExpert(string $key, array $expert): void
    {
        // Configure response format if specified
        $responseFormatService = null;

        /** @var array{
         *    type: "json_schema",
         *    schema: mixed,
         * }|null $responseFormat
         */
        $responseFormat = $expert['response_format'] ?? null;
        if ('json_schema' === ($responseFormat['type'] ?? null)) {
            $responseFormatId = 'modelflow_ai.experts.' . $key . '.response_format';
            $responseFormatService = new Reference($responseFormatId);

            $this->container->services()
                ->set($responseFormatId, JsonSchemaResponseFormat::class)
                ->args([
                    $responseFormat['schema'],
                ]);
        }

        // Configure the expert service
        $this->container->services()
            ->set('modelflow_ai.experts.' . $key, Expert::class)
            ->args([
                $expert['name'],
                $expert['description'],
                $expert['instructions'],
                $expert['criteria'],
                $responseFormatService,
            ]);
    }
}
