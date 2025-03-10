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

use ModelflowAi\AnthropicAdapter\AnthropicAdapterPackage;
use ModelflowAi\FireworksAiAdapter\FireworksAiAdapterPackage;
use ModelflowAi\GoogleGeminiAdapter\GoogleGeminiAdapterPackage;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use ModelflowAi\MistralAdapter\MistralAdapterPackage;
use ModelflowAi\OllamaAdapter\OllamaAdapterPackage;
use ModelflowAi\OpenaiAdapter\OpenAiAdapterPackage;

/**
 * @phpstan-import-type ProvidersConfigType from ModelflowAiBundle
 */
final readonly class ProviderValidator
{
    /**
     * Validate that required packages are installed for enabled providers.
     *
     * @param ProvidersConfigType $providers The enabled providers configuration
     *
     * @throws \Exception When a provider is enabled but its package is not installed
     */
    public static function validateProviders(array $providers): void
    {
        $validations = [
            'openai' => [
                'class' => OpenAiAdapterPackage::class,
                'package' => 'modelflow-ai/openai-adapter',
            ],
            'mistral' => [
                'class' => MistralAdapterPackage::class,
                'package' => 'modelflow-ai/mistral-adapter',
            ],
            'anthropic' => [
                'class' => AnthropicAdapterPackage::class,
                'package' => 'modelflow-ai/anthropic-adapter',
            ],
            'fireworksai' => [
                'class' => FireworksAiAdapterPackage::class,
                'package' => 'modelflow-ai/fireworksai-adapter',
            ],
            'google_gemini' => [
                'class' => GoogleGeminiAdapterPackage::class,
                'package' => 'modelflow-ai/google-gemini-adapter',
            ],
            'ollama' => [
                'class' => OllamaAdapterPackage::class,
                'package' => 'modelflow-ai/ollama-adapter',
            ],
        ];

        foreach ($validations as $provider => $validation) {
            if (isset($providers[$provider]) && !\class_exists($validation['class'])) {
                throw new \Exception(
                    \ucfirst($provider) . ' adapter is enabled but the ' . \ucfirst($provider) .
                    ' adapter library is not installed. Please install it with composer require ' .
                    $validation['package'],
                );
            }
        }
    }

    private function __construct()
    {
    }
}
