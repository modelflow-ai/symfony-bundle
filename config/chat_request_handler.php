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

use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;
use ModelflowAi\Chat\ToolInfo\ToolExecutorInterface;
use ModelflowAi\Integration\Symfony\DecisionTree\DecisionTreeDecorator;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.chat_request_handler.decision_tree', DecisionTreeDecorator::class)
        ->args([
            tagged_iterator(ModelflowAiBundle::TAG_CHAT_DECISION_TREE_RULE),
        ]);

    $container->services()
        ->set('modelflow_ai.chat_request_handler', AIChatRequestHandler::class)
        ->args([
            service('modelflow_ai.chat_request_handler.decision_tree'),
        ])
        ->alias(AIChatRequestHandlerInterface::class, 'modelflow_ai.chat_request_handler');

    $container->services()
        ->set('modelflow_ai.tool_executor', ToolExecutor::class)
        ->alias(ToolExecutorInterface::class, 'modelflow_ai.tool_executor');
};
