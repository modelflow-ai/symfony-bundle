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

use ModelflowAi\Embeddings\Store\Qdrant\QdrantEmbeddingsStoreFactory;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    if (!\class_exists(QdrantEmbeddingsStoreFactory::class)) {
        return;
    }

    $container->services()
        ->set('modelflow_ai.embeddings_stores.qdrant_factory', QdrantEmbeddingsStoreFactory::class)
        ->tag('modelflow_ai.embeddings_store_factory');
};
