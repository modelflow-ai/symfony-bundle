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

namespace ModelflowAi\Integration\Symfony\Tests\Functional;

use ModelflowAi\Integration\Symfony\Tests\BundleTesting\BundleTestCase;
use ModelflowAi\Integration\Symfony\Tests\BundleTesting\BundleTestKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class ModelflowAiBundleTest extends BundleTestCase
{
    /**
     * @return iterable<string, array{0: \SplFileInfo}>
     */
    public static function provideMarkdownFiles(): iterable
    {
        $path = __DIR__ . '/../Resources';

        if (!\is_dir($path)) {
            throw new \RuntimeException(\sprintf('Resources directory not found at: %s', $path));
        }

        $finder = self::createFinder($path);

        $files = \iterator_to_array($finder->files()->name('*.md'));

        if ([] === $files) {
            throw new \RuntimeException(\sprintf('No markdown files found in: %s', $path));
        }

        foreach ($files as $file) {
            yield $file->getBasename() => [$file];
        }
    }

    protected static function createFinder(string $path): Finder
    {
        return (new Finder())->in($path)->sortByName();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMarkdownFiles')]
    public function testBundleConfiguration(\SplFileInfo $file): void
    {
        $testCase = self::readMarkdownFile($file->getPathname());

        // Handle test cases that expect exceptions
        if (isset($testCase['expects']['exception'])) {
            $this->markdownTestExpectedException($testCase['configuration'], $testCase['expects']['exception']);

            return;
        }

        $containerBuilder = $this->buildContainerFromConfiguration($testCase['configuration']);

        $this->assertExpectedBundles($containerBuilder, $testCase['expects']['bundles'] ?? []);
        $this->assertExpectedServices($containerBuilder, $testCase['expects']['services'] ?? []);
        $this->assertExpectedAliases($containerBuilder, $testCase['expects']['aliases'] ?? []);
        $this->assertNotExpectedServices($containerBuilder, $testCase['expects']['not_services'] ?? []);
        $this->assertNotExpectedAliases($containerBuilder, $testCase['expects']['not_aliases'] ?? []);
    }

    /**
     * Test for configurations that are expected to throw exceptions
     * Verifies that the expected exception is thrown with the expected message.
     *
     * @param array<string, mixed> $configuration
     * @param array{
     *     message: string,
     * } $expectedException
     */
    public function markdownTestExpectedException(array $configuration, array $expectedException): void
    {
        $exceptionThrown = false;
        $exceptionMessage = '';

        try {
            $this->buildContainerFromConfiguration($configuration);
        } catch (\Exception $e) {
            $exceptionThrown = true;
            $exceptionMessage = $e->getMessage();
        }

        $this->assertTrue($exceptionThrown, 'Expected an exception when building container with invalid configuration');
        $this->assertStringContainsString(
            $expectedException['message'],
            $exceptionMessage,
            'Exception message should contain the expected text',
        );
    }

    /**
     * Build a container from the provided configuration.
     *
     * @param array<string, mixed> $configuration
     */
    private function buildContainerFromConfiguration(array $configuration): ContainerBuilder
    {
        /** @var BundleTestKernel $kernel */
        $kernel = static::createKernel([
            'configuration' => $configuration,
        ]);

        $buildContainer = \Closure::bind(function (): ContainerBuilder {
            $this->initializeBundles();

            return $this->buildContainer();
        }, $kernel, $kernel::class);

        $containerBuilder = $buildContainer();
        $containerBuilder->getCompilerPassConfig()->setRemovingPasses([]);
        $containerBuilder->compile();

        return $containerBuilder;
    }

    /**
     * Assert that expected bundles are registered.
     *
     * @param string[] $expectedBundles
     */
    private function assertExpectedBundles(ContainerBuilder $containerBuilder, array $expectedBundles): void
    {
        /** @var string[] $bundles */
        $bundles = $containerBuilder->getParameter('kernel.bundles');
        foreach ($expectedBundles as $bundle) {
            $this->assertContains($bundle, $bundles, "Bundle '$bundle' should be registered");
        }
    }

    /**
     * Assert that expected services exist with correct configuration.
     *
     * @param array<string, array{
     *     class?: string,
     *     tags?: array<string, array<string, mixed>>,
     *     aliases?: array<string, string>,
     *     arguments?: array<mixed>,
     *     factory?: array<mixed>,
     * }> $expectedServices
     */
    private function assertExpectedServices(ContainerBuilder $containerBuilder, array $expectedServices): void
    {
        foreach ($expectedServices as $id => $serviceDefinition) {
            $message = "Service '$id' should exist";
            $this->assertArrayHasKey($id, $containerBuilder->getDefinitions(), $message);

            $definition = $containerBuilder->getDefinition($id);

            if (isset($serviceDefinition['class'])) {
                $this->assertSame(
                    $serviceDefinition['class'],
                    $definition->getClass(),
                    "Service '$id' should have class '{$serviceDefinition['class']}'",
                );
            }

            if (isset($serviceDefinition['tags'])) {
                foreach ($serviceDefinition['tags'] as $tag) {
                    $expectedTagAttributes = [...$tag];
                    unset($expectedTagAttributes['name']);

                    /** @var string $name */
                    $name = $tag['name'];
                    $this->assertArrayHasKey(
                        $name,
                        $definition->getTags(),
                        "Service '$id' should have tag '$name'",
                    );

                    $tagAttributes = $definition->getTag($name);
                    $this->assertSame(
                        $expectedTagAttributes,
                        $tagAttributes[0],
                        "Service '$id' tag '$name' should have correct attributes",
                    );
                }
            }

            if (isset($serviceDefinition['arguments'])) {
                $arguments = $definition->getArguments();
                foreach ($arguments as $index => $argument) {
                    if ($argument instanceof Reference) {
                        $arguments[$index] = '@' . $argument->__toString();
                    }
                }
                $this->assertSame(
                    $serviceDefinition['arguments'],
                    $arguments,
                    "Service '$id' should have correct arguments",
                );
            }

            if (isset($serviceDefinition['factory'])) {
                $factory = $definition->getFactory();
                if (\is_array($factory)) {
                    $factory[0] = '@' . $factory[0]->__toString();
                }

                $this->assertSame(
                    $serviceDefinition['factory'],
                    $factory,
                    "Service '$id' should have correct factory",
                );
            }
        }
    }

    /**
     * Assert that expected aliases exist and point to correct services.
     *
     * @param array<string, string> $expectedAliases
     */
    private function assertExpectedAliases(ContainerBuilder $containerBuilder, array $expectedAliases): void
    {
        foreach ($expectedAliases as $alias => $targetId) {
            $message = "Alias '$alias' should exist";
            $this->assertArrayHasKey($alias, $containerBuilder->getAliases(), $message);

            $this->assertSame(
                $targetId,
                (string) $containerBuilder->getAlias($alias),
                "Alias '$alias' should point to service '$targetId'",
            );
        }
    }

    /**
     * Assert that services that should not exist don't exist.
     *
     * @param string[] $notExpectedServices
     */
    private function assertNotExpectedServices(ContainerBuilder $containerBuilder, array $notExpectedServices): void
    {
        foreach (\array_keys($notExpectedServices) as $id) {
            $this->assertArrayNotHasKey(
                $id,
                $containerBuilder->getDefinitions(),
                "Service '$id' should not exist",
            );
        }
    }

    /**
     * Assert that aliases that should not exist don't exist.
     *
     * @param string[] $notExpectedAliases
     */
    private function assertNotExpectedAliases(ContainerBuilder $containerBuilder, array $notExpectedAliases): void
    {
        foreach (\array_keys($notExpectedAliases) as $alias) {
            $this->assertArrayNotHasKey(
                $alias,
                $containerBuilder->getAliases(),
                "Alias '$alias' should not exist",
            );
        }
    }
}
