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

use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Factory;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\OllamaAdapterFactory;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.ollama.client_factory', Factory::class)
        ->factory([Ollama::class, 'factory'])
        ->call('withBaseUrl', ['%modelflow_ai.providers.ollama.url%']);

    $container->services()
        ->set('modelflow_ai.providers.ollama.client', ClientInterface::class)
        ->factory([service('modelflow_ai.providers.ollama.client_factory'), 'make']);

    $container->services()
        ->set('modelflow_ai.providers.ollama.adapter_factory', OllamaAdapterFactory::class)
        ->args([
            service('modelflow_ai.providers.ollama.client'),
        ]);
};
