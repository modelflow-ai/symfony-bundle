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

namespace ModelflowAi\Integration\Symfony\Tests\Application;

use ModelflowAi\Integration\Symfony\ModelflowAiBundle;
use ModelflowAi\Integration\Symfony\Tests\BundleTesting\BundleTestKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

class TestKernel extends BundleTestKernel
{
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new ModelflowAiBundle();
    }
}
