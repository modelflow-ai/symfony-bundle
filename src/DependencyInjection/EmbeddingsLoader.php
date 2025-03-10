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
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type EmbeddingGeneratorConfigType from ModelflowAiBundle
 * @phpstan-import-type EmbeddingStoreConfigType from ModelflowAiBundle
 */
final readonly class EmbeddingsLoader
{
    public function __construct(
        private ContainerConfigurator $container,
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

        // Configure the embedding adapter
        $this->container->services()
            ->set($adapterId, EmbeddingAdapterInterface::class)
            ->factory(
                [
                    new Reference('modelflow_ai.providers.' . $embedding['provider'] . '.embedding_adapter_factory'),
                    'createEmbeddingAdapter',
                ],
            )
            ->args([
                $embedding,
            ]);

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
            ]);
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

        $this->container->services()
            ->set('modelflow_ai.embeddings.store.' . $key, EmbeddingsStoreInterface::class)
            ->factory([new Reference('modelflow_ai.embeddings_store_factory'), 'create'])
            ->args([
                $store['dsn'],
            ]);
    }
}
