<?php

namespace app\middleware;

use Cclilshy\PRippleHttpService\Request;
use Generator;
use PRipple\Framework\Session\Session;
use PRipple\Framework\Std\MiddlewareStd;
use Throwable;

class VerifyLoginMiddleware implements MiddlewareStd
{
    /**
     * @param Request $request
     * @return Generator
     * @throws Throwable
     */
    public function handle(Request $request): Generator
    {
        /**
         * @var Session $session
         */
        $session = $request->resolve(Session::class);
        if (!$session->get('username')) {
            yield $request->respondJson([
                'code' => 0,
                'msg'  => 'Please log in'
            ]);
        }
    }
}
