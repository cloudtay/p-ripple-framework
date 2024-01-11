<?php

namespace app\middleware;

use Cclilshy\PRippleHttpService\Request;
use Cclilshy\PRippleWeb\Std\MiddlewareStd;
use Generator;
use Override;

class VerifyLogin implements MiddlewareStd
{

    #[Override] public function handle(Request $request): Generator
    {
        // TODO: Implement handle() method.
    }
}
