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

use ModelflowAi\Anthropic\Anthropic;
use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\Anthropic\Factory;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.anthropic.client_factory', Factory::class)
        ->factory([Anthropic::class, 'factory'])
        ->call('withApiKey', ['%modelflow_ai.providers.anthropic.credentials.api_key%']);

    $container->services()
        ->set('modelflow_ai.providers.anthropic.client', ClientInterface::class)
        ->factory([service('modelflow_ai.providers.anthropic.client_factory'), 'make']);
};
