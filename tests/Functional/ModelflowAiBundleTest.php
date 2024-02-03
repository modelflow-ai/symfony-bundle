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
use Symfony\Component\Finder\Finder;

class ModelflowAiBundleTest extends BundleTestCase
{
    /**
     * @return iterable<string, array{0: \SplFileInfo}>
     */
    public static function provideMarkdownFiles(): iterable
    {
        $finder = static::createFinder(__DIR__ . '/../Resources');
        foreach ($finder->files()->name('*.md') as $file) {
            yield $file->getBasename() => [$file];
        }
    }

    protected static function createFinder(string $path): Finder
    {
        return (new Finder())->in($path)->sortByName();
    }

    /**
     * @dataProvider provideMarkdownFiles
     */
    public function testNoConfiguration(\SplFileInfo $file): void
    {
        $case = self::readMarkdownFile($file->getPathname());

        /** @var BundleTestKernel $kernel */
        $kernel = static::createKernel([
            'configuration' => $case['configuration'],
        ]);

        $buildContainer = \Closure::bind(function (): ContainerBuilder {
            $this->initializeBundles();

            return $this->buildContainer();
        }, $kernel, $kernel::class);
        $containerBuilder = $buildContainer();
        $containerBuilder->getCompilerPassConfig()->setRemovingPasses([]);
        $containerBuilder->compile();

        /** @var string[] $bundles */
        $bundles = $containerBuilder->getParameter('kernel.bundles');
        foreach ($case['expects']['bundles'] as $bundle) {
            $this->assertContains($bundle, $bundles);
        }

        foreach ($case['expects']['services'] as $id => $serviceDefinition) {
            $this->assertArrayHasKey($id, $containerBuilder->getDefinitions());
            $definition = $containerBuilder->getDefinition($id);
            if (isset($serviceDefinition['class'])) {
                $this->assertSame($serviceDefinition['class'], $definition->getClass());
            }
            if (isset($serviceDefinition['tags'])) {
                foreach ($serviceDefinition['tags'] as $tag) {
                    $expectedTagAttributes = [...$tag];
                    unset($expectedTagAttributes['name']);

                    /** @var string $name */
                    $name = $tag['name'];
                    $this->assertArrayHasKey($name, $definition->getTags());
                    $tagAttributes = $definition->getTag($name);
                    $this->assertSame($expectedTagAttributes, $tagAttributes[0]);
                }
            }
        }

        foreach ($case['expects']['aliases'] as $alias => $id) {
            $this->assertArrayHasKey($alias, $containerBuilder->getAliases());
            $this->assertSame($id, (string) $containerBuilder->getAlias($alias));
        }
    }
}
