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

use ModelflowAi\Experts\ThreadFactory;
use ModelflowAi\Experts\ThreadFactoryInterface;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.experts.thread_factory', ThreadFactory::class)
        ->args([
            service('modelflow_ai.request_handler'),
        ])
        ->alias(ThreadFactoryInterface::class, 'modelflow_ai.experts.thread_factory');
};
