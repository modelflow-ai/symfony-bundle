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

namespace ModelflowAi\Integration\Symfony;

use ModelflowAi\AnthropicAdapter\AnthropicAdapterFactory;
use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Criteria\FeatureCriteria;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use ModelflowAi\Embeddings\EmbeddingsPackage;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Experts\Expert;
use ModelflowAi\Experts\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use ModelflowAi\Image\ImagePackage;
use ModelflowAi\Integration\Symfony\Config\CriteriaContainer;
use ModelflowAi\Integration\Symfony\Criteria\ModelCriteria;
use ModelflowAi\Integration\Symfony\Criteria\ProviderCriteria;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\MistralAdapterFactory;
use ModelflowAi\OllamaAdapter\OllamaAdapterFactory;
use ModelflowAi\OpenaiAdapter\OpenaiAdapterFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\KernelInterface;

class ModelflowAiBundle extends AbstractBundle
{
    final public const TAG_IMAGE_DECISION_TREE_RULE = 'modelflow_ai.image_request_handler.decision_tree.rule';

    protected string $extensionAlias = 'modelflow_ai';

    final public const DEFAULT_ADAPTER_KEY_ORDER = [
        'enabled',
        'model',
        'provider',
        'chat',
        'completion',
        'tools',
        'image_to_text',
        'text_to_image',
        'criteria',
        'priority',
    ];

    final public const DEFAULT_VALUES = [
        'gpt4' => [
            'provider' => ProviderCriteria::OPENAI->value,
            'model' => ModelCriteria::GPT4->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => true,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::GPT4,
                ProviderCriteria::OPENAI,
                CapabilityCriteria::SMART,
            ],
        ],
        'gpt3.5' => [
            'provider' => ProviderCriteria::OPENAI->value,
            'model' => ModelCriteria::GPT3_5->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => true,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::GPT3_5,
                ProviderCriteria::OPENAI,
                CapabilityCriteria::INTERMEDIATE,
            ],
        ],
        'dall_e_3' => [
            'provider' => ProviderCriteria::OPENAI->value,
            'model' => ModelCriteria::DALL_E_3->value,
            'chat' => false,
            'completion' => false,
            'stream' => false,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => true,
            'criteria' => [
                ModelCriteria::DALL_E_3,
                ProviderCriteria::OPENAI,
                CapabilityCriteria::BASIC,
            ],
        ],
        'dall_e_2' => [
            'provider' => ProviderCriteria::OPENAI->value,
            'model' => ModelCriteria::DALL_E_2->value,
            'chat' => false,
            'completion' => false,
            'stream' => false,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => true,
            'criteria' => [
                ModelCriteria::DALL_E_2,
                ProviderCriteria::OPENAI,
                CapabilityCriteria::BASIC,
            ],
        ],
        'mistral_tiny' => [
            'provider' => ProviderCriteria::MISTRAL->value,
            'model' => Model::TINY->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::MISTRAL_TINY,
                ProviderCriteria::MISTRAL,
                CapabilityCriteria::BASIC,
            ],
        ],
        'mistral_small' => [
            'provider' => ProviderCriteria::MISTRAL->value,
            'model' => Model::SMALL->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::MISTRAL_SMALL,
                ProviderCriteria::MISTRAL,
                CapabilityCriteria::INTERMEDIATE,
            ],
        ],
        'mistral_medium' => [
            'provider' => ProviderCriteria::MISTRAL->value,
            'model' => Model::MEDIUM->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::MISTRAL_MEDIUM,
                ProviderCriteria::MISTRAL,
                CapabilityCriteria::ADVANCED,
            ],
        ],
        'mistral_large' => [
            'provider' => ProviderCriteria::MISTRAL->value,
            'model' => Model::LARGE->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => true,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::MISTRAL_LARGE,
                ProviderCriteria::MISTRAL,
                CapabilityCriteria::SMART,
            ],
        ],
        'llama2' => [
            'provider' => ProviderCriteria::OLLAMA->value,
            'model' => ModelCriteria::LLAMA2->value,
            'chat' => true,
            'completion' => true,
            'stream' => true,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::LLAMA2,
                ProviderCriteria::OLLAMA,
                CapabilityCriteria::BASIC,
            ],
        ],
        'nexusraven' => [
            'provider' => ProviderCriteria::OLLAMA->value,
            'model' => ModelCriteria::NEXUSRAVEN->value,
            'chat' => true,
            'completion' => true,
            'stream' => true,
            'tools' => false,
            'image_to_text' => false,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::NEXUSRAVEN,
                ProviderCriteria::OLLAMA,
                CapabilityCriteria::BASIC,
            ],
        ],
        'llava' => [
            'provider' => ProviderCriteria::OLLAMA->value,
            'model' => ModelCriteria::LLAVA->value,
            'chat' => true,
            'completion' => true,
            'stream' => true,
            'tools' => false,
            'image_to_text' => true,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::LLAVA,
                ProviderCriteria::OLLAMA,
                CapabilityCriteria::BASIC,
            ],
        ],
        'claude_3_opus' => [
            'provider' => ProviderCriteria::ANTHROPIC->value,
            'model' => ModelCriteria::CLAUDE_3_OPUS->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => false,
            'image_to_text' => true,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::CLAUDE_3_OPUS,
                ProviderCriteria::ANTHROPIC,
                CapabilityCriteria::SMART,
            ],
        ],
        'claude_3_sonnet' => [
            'provider' => ProviderCriteria::ANTHROPIC->value,
            'model' => ModelCriteria::CLAUDE_3_SONNET->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => false,
            'image_to_text' => true,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::CLAUDE_3_SONNET,
                ProviderCriteria::ANTHROPIC,
                CapabilityCriteria::ADVANCED,
            ],
        ],
        'claude_3_haiku' => [
            'provider' => ProviderCriteria::ANTHROPIC->value,
            'model' => ModelCriteria::CLAUDE_3_HAIKU->value,
            'chat' => true,
            'completion' => false,
            'stream' => true,
            'tools' => false,
            'image_to_text' => true,
            'text_to_image' => false,
            'criteria' => [
                ModelCriteria::CLAUDE_3_HAIKU,
                ProviderCriteria::ANTHROPIC,
                CapabilityCriteria::BASIC,
            ],
        ],
    ];

    private function getCriteria(CriteriaInterface $criteria, bool $isReferenceDumping): CriteriaInterface
    {
        if ($isReferenceDumping) {
            return new CriteriaContainer($criteria);
        }

        return $criteria;
    }

    /**
     * @param CriteriaInterface[] $default
     */
    public function createCriteriaNode(array $default, bool $isReferenceDumping): ArrayNodeDefinition
    {
        $nodeDefinition = new ArrayNodeDefinition('criteria');
        $nodeDefinition
            ->defaultValue(\array_map(
                fn (CriteriaInterface $criteria) => $this->getCriteria(PrivacyCriteria::LOW, $isReferenceDumping),
                $default,
            ));
        $nodeDefinition
            ->beforeNormalization()
                ->ifArray()
                ->then(function ($value) use ($isReferenceDumping): array {
                    $result = [];
                    foreach ($value as $item) {
                        if ($item instanceof CriteriaInterface) {
                            $result[] = $this->getCriteria($item, $isReferenceDumping);
                        } else {
                            $result[] = $item;
                        }
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

    public function configure(DefinitionConfigurator $definition): void
    {
        // @phpstan-ignore-next-line
        $arguments = $argv ?? $_SERVER['argv'] ?? null;

        $isReferenceDumping = false;
        $container = $this->container ?? null;
        if ($container && $arguments) {
            /** @var KernelInterface $kernel */
            $kernel = $container->get('kernel');
            $application = new Application($kernel);
            $command = $application->find($arguments[1] ?? null);
            $isReferenceDumping = 'config:dump-reference' === $command->getName();
        }

        $adapters = [];

        // @phpstan-ignore-next-line
        $definition->rootNode()
            ->children()
                ->arrayNode('providers')
                    ->children()
                        ->arrayNode('openai')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('credentials')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('api_key')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->append($this->createCriteriaNode([PrivacyCriteria::LOW], $isReferenceDumping))
                            ->end()
                        ->end()
                        ->arrayNode('mistral')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('credentials')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('api_key')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->append($this->createCriteriaNode([PrivacyCriteria::MEDIUM], $isReferenceDumping))
                            ->end()
                        ->end()
                        ->arrayNode('anthropic')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('credentials')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('api_key')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->integerNode('max_tokens')->defaultValue(1024)->end()
                                ->append($this->createCriteriaNode([PrivacyCriteria::LOW], $isReferenceDumping))
                            ->end()
                        ->end()
                        ->arrayNode('ollama')
                            ->children()
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
                                ->append($this->createCriteriaNode([PrivacyCriteria::HIGH], $isReferenceDumping))
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('adapters')
                    ->defaultValue([])
                    ->info('You can configure your own adapter here or use a preconfigured one (see examples) and enable it.')
                    ->example(self::DEFAULT_VALUES)
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(static function ($value) use (&$adapters): array {
                            foreach ($value as $key => $item) {
                                $value[$key]['key'] = $key;
                                $adapters[$key] = $item;
                            }

                            return $value;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifArray()
                            ->then(static function ($value): array {
                                $key = $value['key'];
                                unset($value['key']);

                                $explicitlyDisabled = ($value['enabled'] ?? null) === false;
                                $enabled = $value['enabled'] ?? false;
                                unset($value['enabled']);

                                if (!$explicitlyDisabled && 0 !== \count($value)) {
                                    $enabled = true;
                                }

                                $value = \array_merge(self::DEFAULT_VALUES[$key] ?? [], $value);
                                $value['enabled'] = $enabled;

                                \uksort($value, fn ($key1, $key2) => (\array_search($key1, self::DEFAULT_ADAPTER_KEY_ORDER, true) > \array_search($key2, self::DEFAULT_ADAPTER_KEY_ORDER, true)) ? 1 : -1);

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
                            ->append($this->createCriteriaNode([], $isReferenceDumping))
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('embeddings')
                    ->children()
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
                    ->end()
                ->end()
                ->arrayNode('experts')
                    ->defaultValue([])
                    ->info('You can configure your experts here.')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('description')->isRequired()->end()
                            ->scalarNode('instructions')->isRequired()->end()
                            ->arrayNode('response_format')
                                ->children()
                                    ->enumNode('type')->values(['json_schema'])->isRequired()->end()
                                    ->variableNode('schema')->end()
                                ->end()
                            ->end()
                            ->append($this->createCriteriaNode([], $isReferenceDumping))
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $result = \array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array{
     *     providers?: array{
     *         openai: array{
     *             enabled: bool,
     *             credentials: array{
     *                 api_key: string
     *             },
     *             criteria: CriteriaInterface[]
     *         },
     *         mistral: array{
     *             enabled: bool,
     *             credentials: array{
     *                 api_key: string
     *             },
     *             criteria: CriteriaInterface[]
     *         },
     *         anthropic: array{
     *             enabled: bool,
     *             credentials: array{
     *                 api_key: string
     *             },
     *             max_tokens: int,
     *             criteria: CriteriaInterface[]
     *         },
     *         ollama: array{
     *             enabled: bool,
     *             url: string,
     *             criteria: CriteriaInterface[]
     *         }
     *     },
     *     adapters?: array<string, array{
     *         enabled: bool,
     *         provider: string,
     *         model: string,
     *         chat: bool,
     *         completion: bool,
     *         priority: int,
     *         stream: bool,
     *         tools: bool,
     *         image_to_text: bool,
     *         text_to_image: bool,
     *         criteria: CriteriaInterface[]
     *     }>,
     *     embeddings?: array{
     *         generators: array<string, array{
     *             enabled: bool,
     *             provider: string,
     *             model: string,
     *             splitter: array{
     *                 max_length: int,
     *                 separator: string
     *             },
     *             cache: array{
     *                 enabled: bool,
     *                 cache_pool: string
     *             }
     *         }>
     *     },
     *     experts?: array<array{
     *         name: string,
     *         description: string,
     *         instructions: string,
     *         criteria: CriteriaInterface[],
     *         response_format?: array{
     *             type: string,
     *             schema: mixed
     *        }|null
     *     }>,
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $providerConfig = $this->flattenArray($config['providers'] ?? []);
        foreach ($providerConfig as $key => $value) {
            $container->parameters()
                ->set('modelflow_ai.providers.' . $key, $value);
        }

        $container->import(\dirname(__DIR__) . '/config/request_handler.php');
        $container->import(\dirname(__DIR__) . '/config/commands.php');

        $adapters = \array_filter($config['adapters'] ?? [], fn (array $adapter) => $adapter['enabled']);
        $providers = \array_filter($config['providers'] ?? [], fn (array $provider) => $provider['enabled']);

        $generators = $config['embeddings']['generators'] ?? [];

        $chatAdapters = [];
        $completionAdapters = [];
        $imageAdapters = [];
        $configFiles = [];
        foreach ($adapters as $key => $adapter) {
            $configFiles[] = $adapter['provider'] . '/common.php';

            if ($adapter['chat']) {
                $chatAdapters[] = $key;
                $configFiles[] = $adapter['provider'] . '/chat.php';
            }

            if ($adapter['completion']) {
                $completionAdapters[] = $key;
                $configFiles[] = $adapter['provider'] . '/completion.php';
            }

            if ($adapter['text_to_image']) {
                $imageAdapters[] = $key;
                $configFiles[] = $adapter['provider'] . '/image.php';
            }
        }

        foreach ($generators as $generator) {
            $configFiles[] = $generator['provider'] . '/embeddings.php';
        }

        if (\count($generators) > 0 && !\class_exists(EmbeddingsPackage::class)) {
            throw new \Exception('Embeddings package is enabled but the package is not installed. Please install it with composer require modelflow-ai/embeddings');
        }

        if ([] !== $imageAdapters && !\class_exists(ImagePackage::class)) {
            throw new \Exception('Image adapters are enabled but the image adapter library is not installed. Please install it with composer require modelflow-ai/image');
        }

        $container->parameters()
            ->set('modelflow_ai.adapters', $adapters)
            ->set('modelflow_ai.providers', $providers);

        if (($providers['openai']['enabled'] ?? false) && !\class_exists(OpenaiAdapterFactory::class)) {
            throw new \Exception('OpenAi adapter is enabled but the OpenAi adapter library is not installed. Please install it with composer require modelflow-ai/openai-adapter');
        }

        if (($providers['mistral']['enabled'] ?? false) && !\class_exists(MistralAdapterFactory::class)) {
            throw new \Exception('Mistral adapter is enabled but the Mistral adapter library is not installed. Please install it with composer require modelflow-ai/mistral-adapter');
        }

        if (($providers['anthropic']['enabled'] ?? false) && !\class_exists(AnthropicAdapterFactory::class)) {
            throw new \Exception('Anthropic adapter is enabled but the Anthropic adapter library is not installed. Please install it with composer require modelflow-ai/anthropic-adapter');
        }

        if (($providers['ollama']['enabled'] ?? false) && !\class_exists(OllamaAdapterFactory::class)) {
            throw new \Exception('Ollama adapter is enabled but the Ollama adapter library is not installed. Please install it with composer require modelflow-ai/ollama-adapter');
        }

        foreach (\array_unique($configFiles) as $configFile) {
            $container->import(\dirname(__DIR__) . '/config/providers/' . $configFile);
        }

        foreach ($chatAdapters as $key) {
            $adapter = $adapters[$key] ?? null;
            if (!$adapter) {
                throw new \Exception('Chat adapter ' . $key . ' is enabled but not configured.');
            }

            $provider = $providers[$adapter['provider']] ?? null;
            if (!$provider) {
                throw new \Exception('Chat adapter ' . $key . ' is enabled but the provider ' . $adapter['provider'] . ' is not enabled.');
            }

            $container->services()
                ->set('modelflow_ai.chat_adapter.' . $key . '.adapter', AIModelAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $adapter['provider'] . '.chat_adapter_factory'), 'createChatAdapter'])
                ->args([
                    $adapter,
                ]);

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

            $container->services()
                ->set('modelflow_ai.chat_adapter.' . $key . '.rule', DecisionRule::class)
                ->args([
                    service('modelflow_ai.chat_adapter.' . $key . '.adapter'),
                    \array_merge($provider['criteria'], $adapter['criteria'], $featureCriteria),
                ])
                ->tag('modelflow_ai.decision_tree.rule');
        }

        foreach ($completionAdapters as $key) {
            $adapter = $adapters[$key] ?? null;
            if (!$adapter) {
                throw new \Exception('Text adapter ' . $key . ' is enabled but not configured.');
            }

            $provider = $providers[$adapter['provider']] ?? null;
            if (!$provider) {
                throw new \Exception('Text adapter ' . $key . ' is enabled but the provider ' . $adapter['provider'] . ' is not enabled.');
            }

            $container->services()
                ->set('modelflow_ai.text_adapter.' . $key . '.adapter', AIModelAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $adapter['provider'] . '.completion_adapter_factory'), 'createCompletionAdapter'])
                ->args([
                    $adapter,
                ]);

            $container->services()
                ->set('modelflow_ai.text_adapter.' . $key . '.rule', DecisionRule::class)
                ->args([
                    service('modelflow_ai.text_adapter.' . $key . '.adapter'),
                    \array_merge($provider['criteria'], $adapter['criteria']),
                ])
                ->tag('modelflow_ai.decision_tree.rule');
        }

        if ([] !== $imageAdapters) {
            $container->import(\dirname(__DIR__) . '/config/image.php');
        }

        foreach ($imageAdapters as $key) {
            $adapter = $adapters[$key] ?? null;
            if (!$adapter) {
                throw new \Exception('Image adapter ' . $key . ' is enabled but not configured.');
            }

            $provider = $providers[$adapter['provider']] ?? null;
            if (!$provider) {
                throw new \Exception('Image adapter ' . $key . ' is enabled but the provider ' . $adapter['provider'] . ' is not enabled.');
            }

            $container->services()
                ->set('modelflow_ai.image_adapter.' . $key . '.adapter', AIImageAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $adapter['provider'] . '.image_adapter_factory'), 'createImageAdapter'])
                ->args([
                    $adapter,
                ]);

            $container->services()
                ->set('modelflow_ai.image_adapter.' . $key . '.rule', DecisionRule::class)
                ->args([
                    service('modelflow_ai.image_adapter.' . $key . '.adapter'),
                    \array_merge($provider['criteria'], $adapter['criteria']),
                ])
                ->tag(self::TAG_IMAGE_DECISION_TREE_RULE);
        }

        foreach ($generators as $key => $embedding) {
            $adapterId = $key . '.adapter';
            $container->services()
                ->set($adapterId, EmbeddingAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $embedding['provider'] . '.embedding_adapter_factory'), 'createEmbeddingGenerator'])
                ->args([
                    $embedding,
                ]);

            if ($embedding['cache']['enabled']) {
                $container->services()
                    ->set($adapterId . '.cache', CacheEmbeddingAdapter::class)
                    ->args([
                        service($adapterId),
                        service($embedding['cache']['cache_pool']),
                    ]);

                $adapterId .= '.cache';
            }

            $container->services()
                ->set($key . '.splitter', EmbeddingSplitter::class)
                ->args([
                    $embedding['splitter']['max_length'],
                    $embedding['splitter']['separator'],
                ]);

            $container->services()
                ->set($key . '.formatter', EmbeddingFormatter::class);

            $container->services()
                ->set($key . '.generator', EmbeddingGenerator::class)
                ->args([
                    service($key . '.splitter'),
                    service($key . '.formatter'),
                    service($adapterId),
                ]);
        }

        $experts = $config['experts'] ?? [];
        if (\count($experts) > 0) {
            if (!\class_exists(Expert::class)) {
                throw new \Exception('Experts package is enabled but the package is not installed. Please install it with composer require modelflow-ai/experts');
            }

            $container->import(\dirname(__DIR__) . '/config/experts.php');
        }

        foreach ($experts as $key => $expert) {
            $responseFormatService = null;
            $responseFormat = $expert['response_format'] ?? null;
            if (null !== $responseFormat && 'json_schema' === $responseFormat['type']) {
                $responseFormatId = 'modelflow_ai.experts.' . $key . '.response_format';
                $responseFormatService = service($responseFormatId);
                $container->services()
                    ->set($responseFormatId, JsonSchemaResponseFormat::class)
                    ->args([
                        $responseFormat['schema'],
                    ]);
            }

            $container->services()
                ->set('modelflow_ai.experts.' . $key, Expert::class)
                ->args([
                    $expert['name'],
                    $expert['description'],
                    $expert['instructions'],
                    $expert['criteria'],
                    $responseFormatService,
                ]);
        }
    }
}
