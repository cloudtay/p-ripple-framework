<?php

namespace PRipple\Framework\Std;

use Cclilshy\PRipple\Http\Service\Response;
use PRipple\Framework\Route\Route;
use Cclilshy\PRipple\Http\Service\Request;

interface AttributeParser
{
    /**
     * 解析注解
     * @param Request $request
     * @return Response|null
     */
    public function handle(Request $request): Response|null;
}
