<?php

namespace app\http\attribute;

use Attribute;
use Cclilshy\Container\AttributeBase;
use Cclilshy\Container\Container;
use Cclilshy\PRipple\Framework\Exception\JsonException;
use Cclilshy\PRipple\Framework\Session\Session;
use Exception;
use Override;
use Throwable;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
class PreventNotLoggedRequest extends AttributeBase
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
