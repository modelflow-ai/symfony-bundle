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

use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use Symfony\Component\DependencyInjection\Reference;

final readonly class ImageAdapterLoader extends BaseAdapterLoader
{
    protected function getAdapterType(): string
    {
        return 'image';
    }

    protected function configureAdapter(string $key, array $adapter, array $criteria): void
    {
        $factory = $this->getFactoryServiceId($adapter['provider'], 'image', $this->customProviders);

        // Configure the adapter service
        $this->container->services()
            ->set('modelflow_ai.image_adapter.' . $key . '.adapter', AIImageAdapterInterface::class)
            ->factory([new Reference($factory), 'createImageAdapter'])
            ->args([
                $adapter,
            ]);

        // Configure the decision rule service
        $this->container->services()
            ->set('modelflow_ai.image_adapter.' . $key . '.rule', DecisionRule::class)
            ->args([
                new Reference('modelflow_ai.image_adapter.' . $key . '.adapter'),
                \array_merge($criteria, $adapter['criteria']),
            ])
            ->tag($this->decisionTreeRuleTag);
    }
}
