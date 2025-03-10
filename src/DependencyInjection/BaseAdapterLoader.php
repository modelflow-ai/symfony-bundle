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

use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @phpstan-import-type AdaptersConfigType from ModelflowAiBundle
 * @phpstan-import-type InternalProvidersConfigType from ModelflowAiBundle
 * @phpstan-import-type CustomProviderConfigType from ModelflowAiBundle
 * @phpstan-import-type AdapterConfigType from ModelflowAiBundle
 */
abstract readonly class BaseAdapterLoader
{
    /**
     * @param AdaptersConfigType $adapters
     * @param InternalProvidersConfigType $providers
     * @param array<string, CustomProviderConfigType> $customProviders
     */
    public function __construct(
        protected ContainerConfigurator $container,
        protected array $adapters,
        protected array $providers,
        protected array $customProviders,
        protected string $decisionTreeRuleTag,
    ) {
    }

    public function loadAdapter(string $key): void
    {
        $adapter = $this->adapters[$key] ?? null;
        if (!$adapter) {
            throw new \Exception(\sprintf('%s adapter %s is enabled but not configured.', $this->getAdapterType(), $key));
        }

        $provider = $this->providers[$adapter['provider']] ?? $this->customProviders[$adapter['provider']] ?? null;
        if (!$provider) {
            throw new \Exception(\sprintf(
                '%s adapter %s is enabled but the provider %s is not enabled.',
                $this->getAdapterType(),
                $key,
                $adapter['provider'],
            ));
        }

        $this->configureAdapter($key, $adapter, $provider['criteria']);
    }

    /**
     * Get the adapter type name (chat, completion, image).
     */
    abstract protected function getAdapterType(): string;

    /**
     * Configure services for this adapter.
     *
     * @param AdapterConfigType $adapter
     * @param CriteriaInterface[] $criteria
     */
    abstract protected function configureAdapter(string $key, array $adapter, array $criteria): void;

    /**
     * Get the factory service ID for this adapter type.
     *
     * @param array<string, CustomProviderConfigType> $customProviders
     */
    protected function getFactoryServiceId(string $provider, string $adapterType, array $customProviders): string
    {
        /** @var "chat_factory"|"completion_factory"|"image_factory" $factoryKey */
        $factoryKey = $adapterType . '_factory';

        return $customProviders[$provider][$factoryKey]
            ?? \sprintf('modelflow_ai.providers.%s.%s_adapter_factory', $provider, $adapterType);
    }
}
