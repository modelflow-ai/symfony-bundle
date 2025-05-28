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

namespace ModelflowAi\Integration\Symfony\Configuration;

use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use ModelflowAi\Integration\Symfony\Config\CriteriaContainer;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class BundleConfiguration
{
    public function __construct(private readonly bool $isReferenceDumping)
    {
    }

    public function configureRootNode(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->append($this->createProvidersNode())
                ->append($this->createAdaptersNode())
                ->append($this->createEmbeddingsNode())
                ->append($this->createExpertsNode())
            ->end();
    }

    private function createProvidersNode(): ArrayNodeDefinition
    {
        $providersNode = new ArrayNodeDefinition('providers');

        $providersNode->children()
            ->append($this->createOpenAiProviderNode())
            ->append($this->createMistralProviderNode())
            ->append($this->createAnthropicProviderNode())
            ->append($this->createFireworksAiProviderNode())
            ->append($this->createGoogleGeminiProviderNode())
            ->append($this->createOllamaProviderNode())
            ->append($this->createCustomProvidersNode())
        ->end();

        return $providersNode;
    }

    private function createOpenAiProviderNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('openai');

        // @phpstan-ignore-next-line
        $node->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->arrayNode('credentials')
                ->isRequired()
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                ->end()
            ->end()
            ->append($this->createCriteriaNode([PrivacyCriteria::LOW]))
        ->end();

        return $node;
    }

    private function createMistralProviderNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('mistral');

        // @phpstan-ignore-next-line
        $node->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->arrayNode('credentials')
                ->isRequired()
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                ->end()
            ->end()
            ->append($this->createCriteriaNode([PrivacyCriteria::MEDIUM]))
        ->end();

        return $node;
    }

    private function createAnthropicProviderNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('anthropic');

        // @phpstan-ignore-next-line
        $node->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->arrayNode('credentials')
                ->isRequired()
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                ->end()
            ->end()
            ->integerNode('max_tokens')->defaultValue(1024)->end()
            ->append($this->createCriteriaNode([PrivacyCriteria::LOW]))
        ->end();

        return $node;
    }

    private function createFireworksAiProviderNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('fireworksai');

        // @phpstan-ignore-next-line
        $node->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->arrayNode('credentials')
                ->isRequired()
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                ->end()
            ->end()
            ->integerNode('max_tokens')->defaultValue(1024)->end()
            ->append($this->createCriteriaNode([PrivacyCriteria::LOW]))
        ->end();

        return $node;
    }

    private function createGoogleGeminiProviderNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('google_gemini');

        // @phpstan-ignore-next-line
        $node->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->arrayNode('credentials')
                ->isRequired()
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                ->end()
            ->end()
            ->append($this->createCriteriaNode([PrivacyCriteria::LOW]))
        ->end();

        return $node;
    }

    private function createOllamaProviderNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('ollama');

        // @phpstan-ignore-next-line
        $node->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->scalarNode('url')
                ->defaultValue('http://localhost:11434/api/')
                ->validate()
                    ->ifTrue(static fn ($value): bool => !\filter_var($value, \FILTER_VALIDATE_URL))
                    ->thenInvalid('The value has to be a valid URL')
                ->end()
                ->beforeNormalization()
                    ->ifString()
                    ->then(static fn ($value): string => \rtrim((string) $value, '/') . '/')
                ->end()
            ->end()
            ->append($this->createCriteriaNode([PrivacyCriteria::HIGH]))
        ->end();

        return $node;
    }

    private function createCustomProvidersNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('custom');

        // @phpstan-ignore-next-line
        $node->arrayPrototype()
            ->children()
                ->scalarNode('chat_factory')->end()
                ->scalarNode('completion_factory')->end()
                ->scalarNode('image_factory')->end()
                ->append($this->createCriteriaNode([]))
            ->end();

        return $node;
    }

    private function createAdaptersNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('adapters');

        // @phpstan-ignore-next-line
        $node->defaultValue([])
            ->info('You can configure your own adapter here or use a preconfigured one (see examples) and enable it.')
            ->example(DefaultConfigurationProvider::getDefaultValues())
            ->beforeNormalization()
                ->ifArray()
                ->then(function ($value) {
                    foreach ($value as $key => $item) {
                        $value[$key]['key'] = $key;
                    }

                    return $value;
                })
            ->end()
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifArray()
                    ->then(function ($value) {
                        $key = $value['key'];
                        unset($value['key']);

                        $explicitlyDisabled = ($value['enabled'] ?? null) === false;
                        $enabled = $value['enabled'] ?? false;
                        unset($value['enabled']);

                        if (!$explicitlyDisabled && 0 !== (\is_countable($value) ? \count($value) : 0)) {
                            $enabled = true;
                        }

                        $value = \array_merge(DefaultConfigurationProvider::getDefaultValues()[$key] ?? [], $value);
                        $value['enabled'] = $enabled;

                        \uksort($value, fn ($key1, $key2) => (
                            \array_search($key1, ModelflowAiBundle::DEFAULT_ADAPTER_KEY_ORDER, true) >
                            \array_search($key2, ModelflowAiBundle::DEFAULT_ADAPTER_KEY_ORDER, true)
                        ) ? 1 : -1);

                        return $value;
                    })
                ->end()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('provider')->isRequired()->end()
                    ->scalarNode('model')->isRequired()->end()
                    ->integerNode('priority')->defaultValue(0)->end()
                    ->booleanNode('chat')->defaultFalse()->end()
                    ->booleanNode('completion')->defaultFalse()->end()
                    ->booleanNode('stream')->defaultFalse()->end()
                    ->booleanNode('tools')->defaultFalse()->end()
                    ->booleanNode('image_to_text')->defaultFalse()->end()
                    ->booleanNode('text_to_image')->defaultFalse()->end()
                    ->append($this->createCriteriaNode([]))
                ->end()
            ->end();

        return $node;
    }

    private function createEmbeddingsNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('embeddings');

        // @phpstan-ignore-next-line
        $node->children()
            ->arrayNode('generators')
                ->defaultValue([])
                ->arrayPrototype()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('provider')->end()
                        ->scalarNode('model')->end()
                        ->arrayNode('splitter')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('type')->values(['default', 'service', 'none'])->defaultValue('default')->end()
                                ->scalarNode('service_id')
                                    ->info('Service ID of the EmbeddingSplitterInterface implementation')
                                ->end()
                                ->integerNode('max_length')->defaultValue(1000)->end()
                                ->scalarNode('separator')->defaultValue(' ')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cache')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('cache_pool')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('stores')
                ->arrayPrototype()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(fn (string $dsn) => [
                            'enabled' => true,
                            'dsn' => $dsn,
                        ])
                    ->end()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('dsn')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('request_handler')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('class')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('key')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    private function createExpertsNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('experts');

        // @phpstan-ignore-next-line
        $node->defaultValue([])
            ->info('You can configure your experts here.')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('description')->defaultValue('')->end()
                    ->scalarNode('instructions')->isRequired()->end()
                    ->arrayNode('response_format')
                        ->children()
                            ->enumNode('type')->values(['json_schema'])->isRequired()->end()
                            ->variableNode('schema')->end()
                        ->end()
                    ->end()
                    ->append($this->createCriteriaNode([]))
                ->end()
            ->end();

        return $node;
    }

    /**
     * @param CriteriaInterface[] $default
     */
    private function createCriteriaNode(array $default): ArrayNodeDefinition
    {
        $nodeDefinition = new ArrayNodeDefinition('criteria');
        $nodeDefinition
            ->defaultValue(\array_map(
                fn (CriteriaInterface $criteria) => $this->getCriteria($criteria),
                $default,
            ));
        $nodeDefinition
            ->beforeNormalization()
            ->ifArray()
            ->then(function ($value): array {
                $result = [];
                foreach ($value as $item) {
                    $result[] = $item instanceof CriteriaInterface ? $this->getCriteria($item) : $item;
                }

                return $result;
            })
            ->end();
        $nodeDefinition
            ->variablePrototype()
            ->validate()
            ->ifTrue(static fn ($value): bool => !$value instanceof CriteriaInterface)
            ->thenInvalid('The value has to be an instance of CriteriaInterface')
            ->end()
            ->end();

        return $nodeDefinition;
    }

    private function getCriteria(CriteriaInterface $criteria): CriteriaInterface
    {
        if ($this->isReferenceDumping) {
            return new CriteriaContainer($criteria);
        }

        return $criteria;
    }
}
