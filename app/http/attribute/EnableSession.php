<?php

namespace app\http\attribute;

use Attribute;
use Cclilshy\Container\AttributeBase;
use Cclilshy\Container\Container;
use Cclilshy\Container\Exception\Exception;
use Cclilshy\PRipple\Framework\Core;
use Cclilshy\PRipple\Framework\Session\Session;
use Cclilshy\PRipple\Http\Service\Request;
use Override;
use RedisException;
use Throwable;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
class EnableSession extends AttributeBase
{
    /**
     * @param Container $container
     * @return void
     * @throws Exception
     * @throws RedisException
     * @throws Throwable
     */
    #[Override] public function buildAttribute(Container $container): void
    {
        $request = $container->make(Request::class);
        /**
         * 自动构建Session
         * @var Core $webApplication
         */
        $webApplication = $request->make(Core::class);
        if (!$sessionID = $request->cookieArray['P_SESSION_ID'] ?? null) {
            $sessionID = md5(microtime(true) . $request->hash);
            $request->response->setCookie(
                'P_SESSION_ID',
                $sessionID,
                $webApplication->config['SESSION_EXPIRE'] ?? 7200
            );
        }
        $session = $webApplication->sessionManager->buildSession($sessionID);
        $request->inject(Session::class, $session);
        $request->defer(fn() => $session->save());
    }
}
