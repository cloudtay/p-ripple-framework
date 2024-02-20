<?php declare(strict_types=1);

include_once __DIR__ . '/vendor/autoload.php';

use Cclilshy\PRipple\Framework\Loader;

Loader::makeBuildProject(__DIR__)->kernel->build()->loop();
