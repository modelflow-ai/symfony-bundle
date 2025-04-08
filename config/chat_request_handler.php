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
use ModelflowAi\Chat\ChatPackage;
use ModelflowAi\Chat\Middleware\Tools\ToolExecutionMiddleware;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;
use ModelflowAi\Chat\ToolInfo\ToolExecutorInterface;
use ModelflowAi\Integration\Symfony\DecisionTree\DecisionTreeDecorator;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    if (!\class_exists(ChatPackage::class)) {
        return;
    }

    $container->services()
        ->set('modelflow_ai.chat_request_handler.decision_tree', DecisionTreeDecorator::class)
        ->args([
            tagged_iterator(ModelflowAiBundle::TAG_CHAT_DECISION_TREE_RULE),
        ]);

    $container->services()
        ->set('modelflow_ai.chat.tool_executor', ToolExecutor::class)
        ->alias(ToolExecutorInterface::class, 'modelflow_ai.chat.tool_executor');

    $container->services()
        ->set('modelflow_ai.chat.middleware.tool_execution', ToolExecutionMiddleware::class)
        ->args([
            service('modelflow_ai.chat.tool_executor'),
            10,
        ])
        ->tag('modelflow_ai.chat.middleware');

    $container->services()
        ->set('modelflow_ai.chat_request_handler', AIChatRequestHandler::class)
        ->args([
            service('modelflow_ai.chat_request_handler.decision_tree'),
            tagged_iterator('modelflow_ai.chat.middleware'),
        ])
        ->alias(AIChatRequestHandlerInterface::class, 'modelflow_ai.chat_request_handler');
};
