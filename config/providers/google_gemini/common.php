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

use Gemini\Contracts\ClientContract;
use ModelflowAi\GoogleGeminiAdapter\ClientFactory;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.google_gemini.client_factory', ClientFactory::class)
        ->factory([ClientFactory::class, 'create'])
        ->call('withApiKey', ['%modelflow_ai.providers.google_gemini.credentials.api_key%']);

    $container->services()
        ->set('modelflow_ai.providers.google_gemini.client', ClientContract::class)
        ->factory([service('modelflow_ai.providers.google_gemini.client_factory'), 'make']);
};
