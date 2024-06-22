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

use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\Completion\AICompletionRequestHandlerInterface;
use ModelflowAi\Integration\Symfony\DecisionTree\DecisionTreeDecorator;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;

/*
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.completion_request_handler.decision_tree', DecisionTreeDecorator::class)
        ->args([
            tagged_iterator(ModelflowAiBundle::TAG_COMPLETION_DECISION_TREE_RULE),
        ]);

    $container->services()
        ->set('modelflow_ai.completion_request_handler', AICompletionRequestHandler::class)
        ->args([
            service('modelflow_ai.completion_request_handler.decision_tree'),
        ])
        ->alias(AICompletionRequestHandlerInterface::class, 'modelflow_ai.completion_request_handler');
};
