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

/*
 * @internal
 */

use ModelflowAi\FireworksAiAdapter\Completion\FireworksAiCompletionAdapterFactory;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.fireworksai.completion_adapter_factory', FireworksAiCompletionAdapterFactory::class)
        ->args([
            service('modelflow_ai.providers.fireworksai.client'),
            '%modelflow_ai.providers.fireworksai.max_tokens%',
        ]);
};
