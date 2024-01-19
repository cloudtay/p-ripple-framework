<?php

namespace app\http\attribute;

use Attribute;
use Cclilshy\PRipple\Http\Service\Request;
use Core\Container\Container;
use Core\Container\Exception\Exception;
use Core\Standard\AttributeInterface;
use Override;
use PRipple\Framework\Core;
use PRipple\Framework\Session\Session;
use RedisException;
use Throwable;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
class EnableSession implements AttributeInterface
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
