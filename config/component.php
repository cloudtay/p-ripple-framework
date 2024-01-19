<?php

use component\EnvComponent;
use component\ValidateComponent;
use component\ViewComponent;
use PRipple\Illuminate\Database\Component as DatabaseComponent;

return [
    EnvComponent::class,
    ViewComponent::class,
    DatabaseComponent::class,
    ValidateComponent::class,
];
