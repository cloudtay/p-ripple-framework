<?php

namespace app\http\attribute;

use Attribute;
use Core\Container\Container;
use Core\Container\Exception\Exception;
use Core\Standard\AttributeInterface;
use Override;
use PRipple\Framework\Exception\JsonException;
use PRipple\Framework\Session\Session;
use Throwable;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
class PreventNotLoggedRequest implements AttributeInterface
{
    /**
     * 依赖注入支持递归
     * @param Container $container
     * @throws Exception
     * @throws Throwable
     */
    #[EnableSession]
    #[Override] public function buildAttribute(Container $container): void
    {
        /**
         * @var Session $session
         */
        $session = $container->make(Session::class);
        if (!$session->get('username')) {
            throw new JsonException('Please log in to view', [], -1);
        }
    }
}
