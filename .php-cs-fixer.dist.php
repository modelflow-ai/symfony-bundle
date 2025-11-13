<?php

declare(strict_types=1);

$phpCsConfig = require dirname(__FILE__, 3) . '/.php-cs-fixer.dist.php';

$finder = (new PhpCsFixer\Finder())
    ->in(dirname(__FILE__))
    ->notPath('config/reference.php')
    ->ignoreVCSIgnored(true);

return $phpCsConfig->setFinder($finder);
