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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ModelflowAi\Embeddings\Store\EmbeddingsStoreFactory;
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStoreFactory;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStoreFactory;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.embeddings_store_factory', EmbeddingsStoreFactory::class)
        ->arg('$factories', tagged_iterator('modelflow_ai.embeddings_store_factory'));

    $container->services()
        ->set('modelflow_ai.embeddings_stores.filesystem_factory', FilesystemEmbeddingsStoreFactory::class)
        ->tag('modelflow_ai.embeddings_store_factory');

    $container->services()
        ->set('modelflow_ai.embeddings_stores.memory_factory', MemoryEmbeddingsStoreFactory::class)
        ->tag('modelflow_ai.embeddings_store_factory');

    $container->import('embeddings-stores/qdrant.php');
};
