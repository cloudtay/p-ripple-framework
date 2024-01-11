<?php declare(strict_types=1);

namespace PRipple;

include_once __DIR__ . '/vendor/autoload.php';

use PRipple\Framework\WebApplication;

WebApplication::makeBuildProject(__DIR__)->kernel->launch();
