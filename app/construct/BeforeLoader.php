<?php

namespace app\construct;

use app\service\WebSocketService;
use Cclilshy\PRipple\Core\Kernel;
use Cclilshy\PRipple\Framework\Interface\ConstructInterface;

class BeforeLoader implements ConstructInterface
{
    /**
     * @param Kernel $kernel
     * @return void
     */
    public static function handle(Kernel $kernel): void
    {
        $kernel->push(WebSocketService::new(WebSocketService::class));
    }
}
