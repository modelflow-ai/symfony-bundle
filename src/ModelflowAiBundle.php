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

use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\Integration\Symfony\Configuration\BundleConfiguration;
use ModelflowAi\Integration\Symfony\DependencyInjection\AdapterRegistry;
use ModelflowAi\Integration\Symfony\DependencyInjection\ChatAdapterLoader;
use ModelflowAi\Integration\Symfony\DependencyInjection\CompletionAdapterLoader;
use ModelflowAi\Integration\Symfony\DependencyInjection\EmbeddingsLoader;
use ModelflowAi\Integration\Symfony\DependencyInjection\ExpertsLoader;
use ModelflowAi\Integration\Symfony\DependencyInjection\ImageAdapterLoader;
use ModelflowAi\Integration\Symfony\DependencyInjection\ProviderValidator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @phpstan-type OpenAiProviderConfigType array{
 *     enabled: bool,
 *     credentials: array{
 *         api_key: string
 *     },
 *     criteria: CriteriaInterface[]
 * }
 * @phpstan-type MistralProviderConfigType array{
 *     enabled: bool,
 *     credentials: array{
 *         api_key: string
 *     },
 *     criteria: CriteriaInterface[]
 * }
 * @phpstan-type AnthropicProviderConfigType array{
 *     enabled: bool,
 *     credentials: array{
 *         api_key: string
 *     },
 *     max_tokens: int,
 *     criteria: CriteriaInterface[]
 * }
 * @phpstan-type FireworksAiProviderConfigType array{
 *     enabled: bool,
 *     credentials: array{
 *         api_key: string
 *     },
 *     max_tokens: int,
 *     criteria: CriteriaInterface[]
 * }
 * @phpstan-type GoogleGeminiProviderConfigType array{
 *     enabled: bool,
 *     credentials: array{
 *         api_key: string
 *     },
 *     criteria: CriteriaInterface[]
 * }
 * @phpstan-type OllamaProviderConfigType array{
 *     enabled: bool,
 *     url: string,
 *     criteria: CriteriaInterface[]
 * }
 * @phpstan-type CustomProviderConfigType array{
 *      chat_factory?: string,
 *      completion_factory?: string,
 *      image_factory?: string,
 *      embeddings_factory?: string,
 *      criteria: CriteriaInterface[]
 *  }
 * @phpstan-type ProvidersConfigType array{
 *     openai?: OpenAiProviderConfigType,
 *     mistral?: MistralProviderConfigType,
 *     anthropic?: AnthropicProviderConfigType,
 *     fireworksai?: FireworksAiProviderConfigType,
 *     google_gemini?: GoogleGeminiProviderConfigType,
 *     ollama?: OllamaProviderConfigType,
 *     custom?: array<string, CustomProviderConfigType>
 * }
 * @phpstan-type InternalProvidersConfigType array{
 *     openai?: OpenAiProviderConfigType,
 *     mistral?: MistralProviderConfigType,
 *     anthropic?: AnthropicProviderConfigType,
 *     fireworksai?: FireworksAiProviderConfigType,
 *     google_gemini?: GoogleGeminiProviderConfigType,
 *     ollama?: OllamaProviderConfigType,
 * }
 * @phpstan-type AdapterConfigType array{
 *      enabled: bool,
 *      provider: string,
 *      model: string,
 *      chat: bool,
 *      completion: bool,
 *      priority: int,
 *      stream: bool,
 *      tools: bool,
 *      image_to_text: bool,
 *      text_to_image: bool,
 *      criteria: CriteriaInterface[]
 *  }
 * @phpstan-type AdaptersConfigType array<string, AdapterConfigType>
 * @phpstan-type EmbeddingGeneratorConfigType array{
 *     enabled: bool,
 *     provider: string,
 *     model: string,
 *     splitter: array{
 *         type: "default",
 *         max_length: int,
 *         separator: string
 *     }|array{
 *         type: "service",
 *         service_id?: string,
 *     }|array{
 *         type: "none"
 *     },
 *     cache?: array{
 *         enabled: bool,
 *         cache_pool: string
 *     }
 * }
 * @phpstan-type EmbeddingStoreConfigType array{
 *     enabled: bool,
 *     dsn: string,
 * }
 * @phpstan-type EmbeddingClassMappingType array{
 *     key: string
 * }
 * @phpstan-type EmbeddingRequestHandlerConfigType array{
 *     enabled: bool,
 *     mapping: array<string, EmbeddingClassMappingType>,
 * }
 * @phpstan-type EmbeddingsConfigType array{
 *     generators?: array<string, EmbeddingGeneratorConfigType>,
 *     stores?: array<string, EmbeddingStoreConfigType>,
 *     request_handler?: EmbeddingRequestHandlerConfigType
 * }
 * @phpstan-type ExpertConfigType array{
 *     name: string,
 *     description: string,
 *     instructions: string,
 *     criteria: CriteriaInterface[],
 *     response_format?: array{
 *         type: "json_schema",
 *         schema: mixed,
 *     }|null
 * }
 * @phpstan-type ExpertsConfigType array<string, ExpertConfigType>
 * @phpstan-type BundleConfigType array{
 *     providers?: ProvidersConfigType,
 *     adapters?: AdaptersConfigType,
 *     embeddings?: EmbeddingsConfigType,
 *     experts?: ExpertsConfigType,
 * }
 * @phpstan-type ExtractedProvidersConfigType array{
 *     providers: InternalProvidersConfigType,
 *     customProviders: array<string, CustomProviderConfigType>,
 * }
 * @phpstan-type ExtractedAdaptersConfigType array{
 *     adapters: AdaptersConfigType,
 *     chatAdapters: string[],
 *     completionAdapters: string[],
 *     imageAdapters: string[],
 * }
 * @phpstan-type ExtractedEmbeddingsConfigType EmbeddingsConfigType
 */
class ModelflowAiBundle extends AbstractBundle
{
    // Constants
    final public const TAG_IMAGE_DECISION_TREE_RULE = 'modelflow_ai.image_request_handler.decision_tree.rule';
    final public const TAG_CHAT_DECISION_TREE_RULE = 'modelflow_ai.chat_request_handler.decision_tree.rule';
    final public const TAG_COMPLETION_DECISION_TREE_RULE = 'modelflow_ai.completion_request_handler.decision_tree.rule';

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

    protected string $extensionAlias = 'modelflow_ai';

    public function configure(DefinitionConfigurator $definition): void
    {
        $isReferenceDumping = $this->isReferenceDumping();

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $definition->rootNode();

        $bundleConfig = new BundleConfiguration($isReferenceDumping);
        $bundleConfig->configureRootNode($rootNode);
    }

    /**
     * @param BundleConfigType $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Set provider parameters
        $this->setProviderParameters($config['providers'] ?? [], $container);

        // Load commands
        $container->import(\dirname(__DIR__) . '/config/commands.php');

        // Extract and validate configurations
        $providersConfig = $this->extractProvidersConfig($config['providers'] ?? []);
        $adaptersConfig = $this->extractAdaptersConfig($config['adapters'] ?? []);
        $embeddingsConfig = $this->extractEmbeddingsConfig($config['embeddings'] ?? []);
        $expertsConfig = $config['experts'] ?? [];

        // Load configuration files
        $configFiles = $this->getConfigurationFiles($adaptersConfig, $embeddingsConfig, $providersConfig);
        foreach (\array_unique($configFiles) as $configFile) {
            $filePath = \dirname(__DIR__) . '/config/providers/' . $configFile;
            if (!\file_exists($filePath)) {
                throw new \RuntimeException(\sprintf('Missing provider config file: %s', $filePath));
            }

            $container->import($filePath);
        }

        // Set parameters
        $container->parameters()
            ->set('modelflow_ai.adapters', $adaptersConfig['adapters'])
            ->set('modelflow_ai.providers', $providersConfig['providers']);

        // Validate providers
        ProviderValidator::validateProviders($providersConfig['providers']);

        // Load handler configurations and setup adapters
        $this->setupChatAdapters($container, $adaptersConfig, $providersConfig);
        $this->setupCompletionAdapters($container, $adaptersConfig, $providersConfig);
        $this->setupImageAdapters($container, $adaptersConfig, $providersConfig);
        $this->setupEmbeddings($container, $embeddingsConfig, $providersConfig);
        $this->setupExperts($container, $expertsConfig);
    }

    private function isReferenceDumping(): bool
    {
        // @phpstan-ignore-next-line
        $arguments = $argv ?? $_SERVER['argv'] ?? null;

        $container = $this->container ?? null;
        if ($container && $arguments) {
            /** @var KernelInterface $kernel */
            $kernel = $container->get('kernel');
            $application = new Application($kernel);
            $command = $application->find($arguments[1] ?? null);

            return 'config:dump-reference' === $command->getName();
        }

        return false;
    }

    /**
     * @param ProvidersConfigType $config
     */
    private function setProviderParameters(array $config, ContainerConfigurator $container): void
    {
        $providerConfig = $this->flattenArray($config);
        foreach ($providerConfig as $key => $value) {
            $container->parameters()
                ->set('modelflow_ai.providers.' . $key, $value);
        }
    }

    /**
     * @param ProvidersConfigType $config
     *
     * @return ExtractedProvidersConfigType
     */
    private function extractProvidersConfig(array $config): array
    {
        $providers = \array_filter($config, static fn (array $provider) => $provider['enabled'] ?? false);

        $result = [
            'providers' => $providers,
            'customProviders' => $config['custom'] ?? [],
        ];

        unset($result['providers']['custom']);

        return $result;
    }

    /**
     * @param AdaptersConfigType $config
     *
     * @return ExtractedAdaptersConfigType
     */
    private function extractAdaptersConfig(array $config): array
    {
        $adapters = \array_filter($config, static fn (array $adapter) => $adapter['enabled']);

        return [
            'adapters' => $adapters,
            'chatAdapters' => AdapterRegistry::getEnabledAdapters($adapters, 'chat'),
            'completionAdapters' => AdapterRegistry::getEnabledAdapters($adapters, 'completion'),
            'imageAdapters' => AdapterRegistry::getEnabledAdapters($adapters, 'text_to_image'),
        ];
    }

    /**
     * @param EmbeddingsConfigType $config
     *
     * @return ExtractedEmbeddingsConfigType
     */
    private function extractEmbeddingsConfig(array $config): array
    {
        return [
            'generators' => $config['generators'] ?? [],
            'stores' => $config['stores'] ?? [],
            'request_handler' => $config['request_handler'] ?? ['enabled' => false, 'mapping' => []],
        ];
    }

    /**
     * Get necessary configuration files based on configured adapters.
     *
     * @param ExtractedAdaptersConfigType $adaptersConfig
     * @param ExtractedEmbeddingsConfigType $embeddingsConfig
     * @param ExtractedProvidersConfigType $providersConfig
     *
     * @return string[]
     */
    private function getConfigurationFiles(array $adaptersConfig, array $embeddingsConfig, array $providersConfig): array
    {
        $configFiles = [];

        // Add adapter config files
        foreach ($adaptersConfig['adapters'] as $adapter) {
            $provider = $adapter['provider'];
            if (\array_key_exists($provider, $providersConfig['customProviders'])) {
                continue;
            }

            $configFiles[] = $provider . '/common.php';

            if ($adapter['chat']) {
                $configFiles[] = $provider . '/chat.php';
            }

            if ($adapter['completion']) {
                $configFiles[] = $provider . '/completion.php';
            }

            if ($adapter['text_to_image']) {
                $configFiles[] = $provider . '/image.php';
            }
        }

        // Add embedding config files
        foreach ($embeddingsConfig['generators'] ?? [] as $generator) {
            $provider = $generator['provider'];
            if (\array_key_exists($provider, $providersConfig['customProviders'])) {
                continue;
            }

            $configFiles[] = $provider . '/common.php';
            $configFiles[] = $provider . '/embeddings.php';
        }

        return $configFiles;
    }

    /**
     * @param ExtractedAdaptersConfigType $adaptersConfig
     * @param ExtractedProvidersConfigType $providersConfig
     */
    private function setupChatAdapters(
        ContainerConfigurator $container,
        array $adaptersConfig,
        array $providersConfig,
    ): void {
        $chatAdapters = $adaptersConfig['chatAdapters'];

        $container->import(\dirname(__DIR__) . '/config/chat_request_handler.php');

        if (empty($chatAdapters)) {
            return;
        }

        $loader = new ChatAdapterLoader(
            $container,
            $adaptersConfig['adapters'],
            $providersConfig['providers'],
            $providersConfig['customProviders'],
            self::TAG_CHAT_DECISION_TREE_RULE,
        );

        foreach ($chatAdapters as $key) {
            $loader->loadAdapter($key);
        }
    }

    /**
     * @param ExtractedAdaptersConfigType $adaptersConfig
     * @param ExtractedProvidersConfigType $providersConfig
     */
    private function setupCompletionAdapters(
        ContainerConfigurator $container,
        array $adaptersConfig,
        array $providersConfig,
    ): void {
        $completionAdapters = $adaptersConfig['completionAdapters'];

        $container->import(\dirname(__DIR__) . '/config/completion_request_handler.php');

        if (empty($completionAdapters)) {
            return;
        }

        $loader = new CompletionAdapterLoader(
            $container,
            $adaptersConfig['adapters'],
            $providersConfig['providers'],
            $providersConfig['customProviders'],
            self::TAG_COMPLETION_DECISION_TREE_RULE,
        );

        foreach ($completionAdapters as $key) {
            $loader->loadAdapter($key);
        }
    }

    /**
     * @param ExtractedAdaptersConfigType $adaptersConfig
     * @param ExtractedProvidersConfigType $providersConfig
     */
    private function setupImageAdapters(
        ContainerConfigurator $container,
        array $adaptersConfig,
        array $providersConfig,
    ): void {
        $imageAdapters = $adaptersConfig['imageAdapters'];

        $container->import(\dirname(__DIR__) . '/config/image_request_handler.php');

        if (empty($imageAdapters)) {
            return;
        }

        $loader = new ImageAdapterLoader(
            $container,
            $adaptersConfig['adapters'],
            $providersConfig['providers'],
            $providersConfig['customProviders'],
            self::TAG_IMAGE_DECISION_TREE_RULE,
        );

        foreach ($imageAdapters as $key) {
            $loader->loadAdapter($key);
        }
    }

    /**
     * @param ExtractedEmbeddingsConfigType $embeddingsConfig
     * @param ExtractedProvidersConfigType $providersConfig
     */
    private function setupEmbeddings(ContainerConfigurator $container, array $embeddingsConfig, array $providersConfig): void
    {
        $generators = $embeddingsConfig['generators'] ?? [];
        $stores = $embeddingsConfig['stores'] ?? [];
        $requestHandler = $embeddingsConfig['request_handler'] ?? ['enabled' => false];

        $container->import(\dirname(__DIR__) . '/config/embeddings.php');

        if (empty($generators) && empty($stores) && !$requestHandler['enabled']) {
            return;
        }

        $embeddingsLoader = new EmbeddingsLoader($container, $providersConfig['customProviders']);

        // Load generators first
        $hasEnabledGenerator = false;
        foreach ($generators as $key => $embedding) {
            if ($embedding['enabled']) {
                $hasEnabledGenerator = true;
            }
            $embeddingsLoader->loadGenerator($key, $embedding);
        }

        // Load stores
        foreach ($stores as $key => $store) {
            $embeddingsLoader->loadStore($key, $store);
        }

        // Load request handler if enabled
        if ($requestHandler['enabled']) {
            // Ensure there's at least one generator available for similarity searches
            if (!$hasEnabledGenerator) {
                throw new \Exception(
                    'EmbeddingsRequestHandler is enabled but no embedding generator is configured.
                    You must enable at least one generator in embeddings.generators configuration.',
                );
            }

            // @phpstan-ignore-next-line
            $embeddingsLoader->loadRequestHandler($requestHandler);
        }
    }

    /**
     * @param ExpertsConfigType $expertsConfig
     */
    private function setupExperts(ContainerConfigurator $container, array $expertsConfig): void
    {
        $container->import(\dirname(__DIR__) . '/config/experts.php');

        if ([] === $expertsConfig) {
            return;
        }

        $expertsLoader = new ExpertsLoader($container);

        foreach ($expertsConfig as $key => $expert) {
            $expertsLoader->loadExpert($key, $expert);
        }
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
                $result = [...$result, ...$this->flattenArray($value, $prefix . $key . '.')];
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }
}
