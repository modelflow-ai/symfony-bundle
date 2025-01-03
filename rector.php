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

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->skip([
            __DIR__ . '/config/providers/anthropic/common.php',
            __DIR__ . '/config/providers/fireworksai/common.php',
            __DIR__ . '/config/providers/google_gemini/common.php',
            __DIR__ . '/config/providers/mistral/common.php',
            __DIR__ . '/config/providers/ollama/common.php',
            __DIR__ . '/config/providers/openai/common.php',
        ]);

    $config = require __DIR__ . '/../../rector.php';
    $config($rectorConfig, __DIR__);
};
