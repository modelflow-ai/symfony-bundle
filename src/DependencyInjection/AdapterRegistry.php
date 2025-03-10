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

use ModelflowAi\Chat\ChatPackage;
use ModelflowAi\Completion\CompletionPackage;
use ModelflowAi\Image\ImagePackage;
use ModelflowAi\Integration\Symfony\ModelflowAiBundle;

/**
 * @phpstan-import-type AdaptersConfigType from ModelflowAiBundle
 */
final readonly class AdapterRegistry
{
    /**
     * Get enabled adapters of a specific type.
     *
     * @param AdaptersConfigType $adapters All configured adapters
     * @param string $type The adapter type (chat, completion, text_to_image)
     *
     * @return array<string> List of enabled adapter keys
     */
    public static function getEnabledAdapters(array $adapters, string $type): array
    {
        $enabledAdapters = [];

        foreach ($adapters as $key => $adapter) {
            if ($adapter[$type] ?? false) {
                $enabledAdapters[] = $key;
            }
        }

        // Filter out adapters if the required package is not installed
        if ('chat' === $type && [] !== $enabledAdapters && !\class_exists(ChatPackage::class)) {
            return [];
        }

        if ('completion' === $type && [] !== $enabledAdapters && !\class_exists(CompletionPackage::class)) {
            return [];
        }

        if ('text_to_image' === $type && [] !== $enabledAdapters && !\class_exists(ImagePackage::class)) {
            return [];
        }

        return $enabledAdapters;
    }

    private function __construct()
    {
    }
}
