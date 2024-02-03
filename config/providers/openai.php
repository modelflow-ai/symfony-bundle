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

use ModelflowAi\OpenaiAdapter\OpenaiAdapterFactory;
use OpenAI\Client;
use OpenAI\Factory;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.openai.client_factory', Factory::class)
        ->factory([\OpenAI::class, 'factory'])
        ->call('withApiKey', ['%modelflow_ai.providers.openai.credentials.api_key%']);

    $container->services()
        ->set('modelflow_ai.providers.openai.client', Client::class)
        ->factory([service('modelflow_ai.providers.openai.client_factory'), 'make']);

    $container->services()
        ->set('modelflow_ai.providers.openai.adapter_factory', OpenaiAdapterFactory::class)
        ->args([
            service('modelflow_ai.providers.openai.client'),
        ]);
};
