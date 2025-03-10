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

namespace ModelflowAi\Integration\Symfony\DependencyInjection;

use ModelflowAi\Completion\Adapter\AICompletionAdapterInterface;
use ModelflowAi\DecisionTree\DecisionRule;
use Symfony\Component\DependencyInjection\Reference;

final readonly class CompletionAdapterLoader extends BaseAdapterLoader
{
    protected function getAdapterType(): string
    {
        return 'completion';
    }

    protected function configureAdapter(string $key, array $adapter, array $criteria): void
    {
        $factory = $this->getFactoryServiceId($adapter['provider'], 'completion', $this->customProviders);

        // Configure the adapter service
        $this->container->services()
            ->set('modelflow_ai.completion_adapter.' . $key . '.adapter', AICompletionAdapterInterface::class)
            ->factory([new Reference($factory), 'createCompletionAdapter'])
            ->args([
                $adapter,
            ]);

        // Configure the decision rule service
        $this->container->services()
            ->set('modelflow_ai.completion_adapter.' . $key . '.rule', DecisionRule::class)
            ->args([
                new Reference('modelflow_ai.completion_adapter.' . $key . '.adapter'),
                \array_merge($criteria, $adapter['criteria']),
            ])
            ->tag($this->decisionTreeRuleTag);
    }
}
