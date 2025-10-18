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

use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\EmbeddingsPackage;
use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\EmbeddingsRequestHandlerInterface;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandlerInterface;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandlerInterface;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Splitter\NoOpEmbeddingSplitter;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type EmbeddingGeneratorConfigType from ModelflowAiBundle
 * @phpstan-import-type EmbeddingStoreConfigType from ModelflowAiBundle
 * @phpstan-import-type EmbeddingRequestHandlerConfigType from ModelflowAiBundle
 * @phpstan-import-type CustomProviderConfigType from ModelflowAiBundle
 */
final class EmbeddingsLoader
{
    /**
     * @var string[]
     */
    private array $defaultGeneratorSet = [];

    /**
     * @var string[]
     */
    private array $defaultStoreSet = [];

    /**
     * @param array<string, CustomProviderConfigType> $customProviders
     */
    public function __construct(
        private readonly ContainerConfigurator $container,
        private readonly array $customProviders = [],
    ) {
        // Check if embeddings package is installed
        if (!\class_exists(EmbeddingsPackage::class)) {
            throw new \Exception(
                'Embeddings package is enabled but the package is not installed. ' .
                'Please install it with composer require modelflow-ai/embeddings',
            );
        }
    }

    /**
     * @param EmbeddingGeneratorConfigType $embedding
     */
    public function loadGenerator(string $key, array $embedding): void
    {
        $prefix = 'modelflow_ai.embeddings.' . $key;
        $adapterId = $prefix . '.adapter';

        // Get the factory service ID - check for custom factory first
        $factoryServiceId = $this->getEmbeddingFactoryServiceId($embedding['provider']);

        // Configure the embedding adapter
        $this->container->services()
            ->set($adapterId, EmbeddingAdapterInterface::class)
            ->factory(
                [
                    new Reference($factoryServiceId),
                    'createEmbeddingAdapter',
                ],
            )
            ->args([
                $embedding,
            ])
            ->tag('modelflow_ai.embeddings.adapter', ['key' => $key]);

        // Add cache if enabled
        if ($embedding['cache']['enabled'] ?? false) {
            $this->container->services()
                ->set($adapterId . '.cache', CacheEmbeddingAdapter::class)
                ->args([
                    new Reference($adapterId),
                    new Reference($embedding['cache']['cache_pool']),
                ]);

            $adapterId .= '.cache';
        }

        // Configure the splitter
        if ('service' === $embedding['splitter']['type']) {
            if (!isset($embedding['splitter']['service_id'])) {
                throw new \Exception('Embedding splitter service ID is not set');
            }
            $this->container->services()->alias($prefix . '.splitter', $embedding['splitter']['service_id']);
        } elseif ('none' === $embedding['splitter']['type']) {
            $this->container->services()
                ->set($prefix . '.splitter', NoOpEmbeddingSplitter::class);
        } else {
            $this->container->services()
                ->set($prefix . '.splitter', EmbeddingSplitter::class)
                ->args([
                    $embedding['splitter']['max_length'],
                    $embedding['splitter']['separator'],
                ]);
        }

        // Configure the formatter and generator
        $this->container->services()
            ->set($prefix . '.formatter', EmbeddingFormatter::class);

        $this->container->services()
            ->set($prefix . '.generator', EmbeddingGenerator::class)
            ->args([
                new Reference($prefix . '.splitter'),
                new Reference($prefix . '.formatter'),
                new Reference($adapterId),
            ])
            ->tag('modelflow_ai.embeddings.generator', ['key' => $key]);

        // If this is marked as the default generator, alias it
        if ('default' === $key || ([] === $this->defaultGeneratorSet && $embedding['enabled'])) {
            $this->container->services()
                ->alias('modelflow_ai.embeddings.default_generator', $prefix . '.generator');
            $this->defaultGeneratorSet[] = $key;
        }
    }

    /**
     * @param EmbeddingStoreConfigType $store
     */
    public function loadStore(string $key, array $store): void
    {
        // @phpstan-ignore-next-line
        if (!isset($store['dsn'])) {
            throw new \Exception('Embedding store DSN is not set');
        }

        $serviceId = 'modelflow_ai.embeddings.store.' . $key;

        $this->container->services()
            ->set($serviceId, EmbeddingsStoreInterface::class)
            ->factory([new Reference('modelflow_ai.embeddings_store_factory'), 'create'])
            ->args([
                $store['dsn'],
            ])
            ->tag('modelflow_ai.embeddings.store', ['key' => $key]);

        // If this is marked as the default store, alias it
        if ('default' === $key || ([] === $this->defaultStoreSet && $store['enabled'])) {
            $this->container->services()
                ->alias('modelflow_ai.embeddings.default_store', $serviceId);
            $this->defaultStoreSet[] = $key;
        }
    }

    /**
     * @param EmbeddingRequestHandlerConfigType $config
     */
    public function loadRequestHandler(array $config): void
    {
        if (!$config['enabled']) {
            return;
        }

        // Register class to key mapping
        $classToKeyMapping = [];
        // @phpstan-ignore-next-line
        if (isset($config['mapping'])) {
            foreach ($config['mapping'] as $class => $mappingConfig) {
                $classToKeyMapping[$class] = $mappingConfig['key'];
            }
        }

        // Set up the store handler
        $this->container->services()
            ->set('modelflow_ai.embeddings.store_handler', EmbeddingsStoreHandlerInterface::class)
            ->class(EmbeddingsStoreHandler::class)
            ->args([
                new TaggedIteratorArgument('modelflow_ai.embeddings.generator', indexAttribute: 'key'),
                new TaggedIteratorArgument('modelflow_ai.embeddings.store', indexAttribute: 'key'),
                new TaggedIteratorArgument('modelflow_ai.embeddings.adapter', indexAttribute: 'key'),
                $classToKeyMapping ?: [],
            ]);

        // Set up the similarity handler
        $this->container->services()
            ->set('modelflow_ai.embeddings.similarity_handler', EmbeddingsSimilarityHandlerInterface::class)
            ->class(EmbeddingsSimilarityHandler::class)
            ->args([
                new TaggedIteratorArgument('modelflow_ai.embeddings.store', indexAttribute: 'key'),
                new TaggedIteratorArgument('modelflow_ai.embeddings.adapter', indexAttribute: 'key'),
            ]);

        // Set up the request handler
        $this->container->services()
            ->set('modelflow_ai.embeddings.request_handler', EmbeddingsRequestHandlerInterface::class)
            ->class(EmbeddingsRequestHandler::class)
            ->args([
                new Reference('modelflow_ai.embeddings.store_handler'),
                new Reference('modelflow_ai.embeddings.similarity_handler'),
            ]);

        // Create alias for the request handler
        $this->container->services()
            ->alias(EmbeddingsRequestHandlerInterface::class, 'modelflow_ai.embeddings.request_handler');
    }

    /**
     * Get the factory service ID for embedding adapter.
     * Checks for custom factory first, falls back to default provider factory.
     */
    private function getEmbeddingFactoryServiceId(string $provider): string
    {
        // Fall back to default provider factory
        return $this->customProviders[$provider]['embeddings_factory'] ?? \sprintf('modelflow_ai.providers.%s.embedding_adapter_factory', $provider);
    }
}
