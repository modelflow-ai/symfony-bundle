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

namespace ModelflowAi\Integration\Symfony\Tests\BundleTesting;

use ModelflowAi\Integration\Symfony\Tests\Application\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class BundleTestCase extends KernelTestCase
{
    public static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array{
     *     environment?: string,
     *     debug?: bool,
     *     configuration?: array<string, mixed>,
     * } $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        static::$class ??= static::getKernelClass();

        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;
        $configuration = $options['configuration'] ?? [];

        /** @var KernelInterface $kernel */
        $kernel = new static::$class($env, $debug, $configuration);

        return $kernel;
    }

    /**
     * @return array{
     *      configuration: array<string, mixed>,
     *      expects: array{
     *          bundles: string[],
     *          services: array<string, array{
     *              class?: string,
     *              tags?: array<string, array<string, mixed>>,
     *              aliases?: array<string, string>,
     *          }>,
     *          aliases: array<string, string>,
     *      },
     *  }
     */
    protected static function readMarkdownFile(string $file): array
    {
        /** @var string $content */
        $content = \file_get_contents($file);

        \preg_match('/## Configuration\n\n```yaml\n(.*?)\n```\n/s', $content, $configMatches);
        \preg_match('/## Expects\n\n```yaml\n(.*?)\n```\n/s', $content, $expectsMatches);

        /** @var array<string, mixed> $config */
        $config = Yaml::parse($configMatches[1] ?? '');
        /** @var array{
         * bundles: string[],
         * services: array<string, array{
         * class?: string,
         * tags?: array<string, array<string, mixed>>,
         * aliases?: array<string, string>,
         * }>,
         * aliases: array<string, string>,
         * } $expects */
        $expects = Yaml::parse($expectsMatches[1] ?? '');

        return [
            'configuration' => $config,
            'expects' => $expects,
        ];
    }
}
