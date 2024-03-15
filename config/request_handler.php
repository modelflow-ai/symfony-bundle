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

use ModelflowAi\Core\AIRequestHandler;
use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\ToolInfo\ToolExecutor;
use ModelflowAi\Core\ToolInfo\ToolExecutorInterface;
use ModelflowAi\Integration\Symfony\DecisionTree\AIModelDecisionTreeDecorator;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.request_handler.decision_tree', AIModelDecisionTreeDecorator::class)
        ->args([
            tagged_iterator('modelflow_ai.decision_tree.rule'),
        ])
        ->alias(AIModelDecisionTreeInterface::class, 'modelflow_ai.request_handler.decision_tree');

    $container->services()
        ->set('modelflow_ai.request_handler', AIRequestHandler::class)
        ->args([
            service('modelflow_ai.request_handler.decision_tree'),
        ])
        ->alias(AIRequestHandlerInterface::class, 'modelflow_ai.request_handler');

    $container->services()
        ->set('modelflow_ai.tool_executor', ToolExecutor::class)
        ->alias(ToolExecutorInterface::class, 'modelflow_ai.tool_executor');
};
