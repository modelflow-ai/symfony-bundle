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

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use Symfony\Component\DependencyInjection\Reference;

final readonly class ChatAdapterLoader extends BaseAdapterLoader
{
    protected function getAdapterType(): string
    {
        return 'chat';
    }

    protected function configureAdapter(string $key, array $adapter, array $criteria): void
    {
        $factory = $this->getFactoryServiceId($adapter['provider'], 'chat', $this->customProviders);

        // Configure the adapter service
        $this->container->services()
            ->set('modelflow_ai.chat_adapter.' . $key . '.adapter', AIChatAdapterInterface::class)
            ->factory([new Reference($factory), 'createChatAdapter'])
            ->args([
                $adapter,
            ]);

        // Add feature criteria based on adapter capabilities
        $featureCriteria = [];
        if ($adapter['image_to_text']) {
            $featureCriteria[] = FeatureCriteria::IMAGE_TO_TEXT;
        }
        if ($adapter['tools']) {
            $featureCriteria[] = FeatureCriteria::TOOLS;
        }
        if ($adapter['stream']) {
            $featureCriteria[] = FeatureCriteria::STREAM;
        }

        // Configure the decision rule service
        $this->container->services()
            ->set('modelflow_ai.chat_adapter.' . $key . '.rule', DecisionRule::class)
            ->args([
                new Reference('modelflow_ai.chat_adapter.' . $key . '.adapter'),
                \array_merge($criteria, $adapter['criteria'] ?? [], $featureCriteria), // @phpstan-ignore-line
            ])
            ->tag($this->decisionTreeRuleTag);
    }
}
