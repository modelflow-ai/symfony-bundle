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

/**
 * PHPStan Bootstrap File - Symfony DI Helper Function Stubs.
 *
 * This file provides fallback definitions for Symfony's Dependency Injection
 * configurator helper functions used in service configuration files (config/*.php).
 *
 * PHPStan performs static analysis without executing code, so it cannot discover
 * these helper functions that are only available at runtime within Symfony's
 * ContainerConfigurator context. This bootstrap file defines stub implementations
 * that allow PHPStan to properly analyze service configuration files.
 *
 * The function_exists() guards ensure these stubs don't conflict with Symfony's
 * actual implementations when the code is executed at runtime.
 *
 * @internal this file is only used during static analysis and should not be
 *           included in production code
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\service')) {
    /**
     * Creates a service reference.
     */
    function service(string $serviceId): Reference
    {
        return new Reference($serviceId);
    }
}

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator')) {
    /**
     * Creates a tagged iterator argument.
     */
    function tagged_iterator(string $tag): TaggedIteratorArgument
    {
        return new TaggedIteratorArgument($tag);
    }
}

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\service_closure')) {
    /**
     * Creates a service closure argument.
     */
    function service_closure(string $serviceId): ServiceClosureArgument
    {
        return new ServiceClosureArgument(new Reference($serviceId));
    }
}

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\param')) {
    /**
     * Creates a parameter reference.
     */
    function param(string $name): string
    {
        return '%' . $name . '%';
    }
}
