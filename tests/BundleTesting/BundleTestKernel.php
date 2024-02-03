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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

abstract class BundleTestKernel extends Kernel
{
    use MicroKernelTrait {
        configureContainer as protected parentConfigureContainer;
    }

    /**
     * @param array<string, array<string, mixed>> $configuration
     */
    public function __construct(
        string $environment,
        bool $debug,
        private readonly array $configuration = [],
    ) {
        parent::__construct($environment, $debug);

        (new Filesystem())->remove($this->getCacheDir());
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        foreach ($this->configuration as $extension => $config) {
            $container->loadFromExtension($extension, $config);
        }
    }

    public function getCacheDir(): string
    {
        $cacheDir = parent::getCacheDir();

        return $cacheDir . '/' . \md5((string) \json_encode($this->configuration));
    }
}
