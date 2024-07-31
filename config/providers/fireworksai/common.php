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

use ModelflowAi\FireworksAiAdapter\ClientFactory;
use OpenAI\Client;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.fireworksai.client_factory', ClientFactory::class)
        ->factory([ClientFactory::class, 'create'])
        ->call('withApiKey', ['%modelflow_ai.providers.fireworksai.credentials.api_key%']);

    $container->services()
        ->set('modelflow_ai.providers.fireworksai.client', Client::class)
        ->factory([service('modelflow_ai.providers.fireworksai.client_factory'), 'make']);
};
