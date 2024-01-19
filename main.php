<?php declare(strict_types=1);

namespace PRipple;

include_once __DIR__ . '/vendor/autoload.php';

use PRipple\Framework\Loader;

Loader::makeBuildProject(__DIR__)->kernel->launch();
