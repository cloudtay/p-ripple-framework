<?php

use component\EnvComponent;
use component\ValidateComponent;
use component\ViewComponent;
use Cclilshy\PRipple\Database\Component as DatabaseComponent;

/**
 * 组件加载器列表 List of component loaders
 */
return [
    EnvComponent::class,
    ViewComponent::class,
    DatabaseComponent::class,
    ValidateComponent::class,
];
