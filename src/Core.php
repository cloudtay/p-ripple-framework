<?php declare(strict_types=1);
/*
 * Copyright (c) 2023 cclilshy
 * Contact Information:
 * Email: jingnigg@gmail.com
 * Website: https://cc.cloudtay.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * 版权所有 (c) 2023 cclilshy
 *
 * 特此免费授予任何获得本软件及相关文档文件（“软件”）副本的人，不受限制地处理
 * 本软件，包括但不限于使用、复制、修改、合并、出版、发行、再许可和/或销售
 * 软件副本的权利，并允许向其提供本软件的人做出上述行为，但须符合以下条件：
 *
 * 上述版权声明和本许可声明应包含在本软件的所有副本或主要部分中。
 *
 * 本软件按“原样”提供，不提供任何形式的保证，无论是明示或暗示的，
 * 包括但不限于适销性、特定目的的适用性和非侵权性的保证。在任何情况下，
 * 无论是合同诉讼、侵权行为还是其他方面，作者或版权持有人均不对
 * 由于软件或软件的使用或其他交易而引起的任何索赔、损害或其他责任承担责任。
 */

namespace Cclilshy\PRipple\Framework;

use Cclilshy\Container\Container;
use Cclilshy\PRipple\Core\Event\Event;
use Cclilshy\PRipple\Core\Output;
use Cclilshy\PRipple\Framework\Exception\JsonException;
use Cclilshy\PRipple\Framework\Exception\RouteExcept;
use Cclilshy\PRipple\Framework\Route\Route;
use Cclilshy\PRipple\Framework\Route\RouteMap;
use Cclilshy\PRipple\Framework\Session\SessionManager;
use Cclilshy\PRipple\Http\Service\HttpWorker;
use Cclilshy\PRipple\Http\Service\Request;
use Cclilshy\PRipple\Http\Service\Response;
use Cclilshy\PRipple\PRipple;
use Cclilshy\PRipple\Utils\IO;
use Generator;
use Illuminate\Support\Facades\View;
use ReflectionException;
use Throwable;

/**
 * Class WebApplication
 * 低耦合的方式避免Worker
 * 绑定路由规则并遵循HttpWorker的规范将处理器注入到Worker中
 */
class Core extends Container
{
    public SessionManager $sessionManager;
    public array          $config;
    private HttpWorker    $httpWorker;
    private RouteMap      $routeMap;
    private const array TYPES = [
        'css'         => 'text/css',
        'js'          => 'application/javascript',
        'html'        => 'text/html',
        'png'         => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif'         => 'image/gif'
    ];

    /**
     * WebApplication constructor.
     * @param HttpWorker $httpWorker
     * @param RouteMap   $routeMap
     * @param array      $config
     */
    public function __construct(HttpWorker $httpWorker, RouteMap $routeMap, array $config)
    {
        parent::__construct();
        $this->httpWorker = $httpWorker;
        $this->routeMap   = $routeMap;
        $this->config     = $config;
        foreach ($this->config as $key => $value) {
            PRipple::config($key, $value);
        }
        switch (strtolower($config['SESSION_TYPE'] ?? 'file')) {
            case 'file':
                if ($filePath = $config['SESSION_PATH'] ?? null) {
                    $this->sessionManager = new SessionManager(['FILE_PATH' => $filePath]);
                    $this->inject(SessionManager::class, $this->sessionManager);
                }
                break;
            case 'redis':
                $this->sessionManager = new SessionManager([
                    'REDIS_NAME' => $config['SESSION_REDIS_NAME'] ?? 'default',
                ], SessionManager::TYPE_REDIS);
                $this->inject(SessionManager::class, $this->sessionManager);
                break;
        }

        $this->inject(HttpWorker::class, $httpWorker);
        $this->inject(RouteMap::class, $routeMap);
        $this->inject(Core::class, $this);
    }

    /**
     * 加载HttpWorker
     * @param HttpWorker $httpWorker
     * @param RouteMap   $routeMap
     * @param array      $config
     * @return void
     */
    public static function install(HttpWorker $httpWorker, RouteMap $routeMap, array $config): void
    {
        $webApplication = new Core($httpWorker, $routeMap, $config);

        /**
         * @throw Throwable
         */
        $httpWorker->defineRequestHandler(
            fn(Request $request) => $webApplication->requestHandler($request)
        );

        /**
         * @throw Throwable
         */
        $httpWorker->defineExceptionHandler(
            fn(Event $event, Request $request) => $webApplication->exceptionHandler($event->data, $request)
        );
    }

    /**
     * 请求处理
     * @param Request $request
     * @return Generator
     * @throws ReflectionException
     * @throws RouteExcept
     * @throws Throwable
     */
    private function requestHandler(Request $request): Generator
    {
        $request->inject(Request::class, $request);
        $request->inject(Core::class, $this);
        if (isset($this->sessionManager)) {
            $request->inject(SessionManager::class, $this->sessionManager);
        }
        $target = trim($request->path, FS);
        if (!$router = $this->routeMap->match($request->method, $target)) {
            if ($publicPath = PRipple::getArgument('HTTP_PUBLIC')) {
                if (is_dir($publicPath) && is_file($publicPath . FS . $target)) {
                    $body = IO::fileGetContents($publicPath . FS . $target);
                    $mime = Core::TYPES[pathinfo($target, PATHINFO_EXTENSION)] ?? 'text/plain';
                    return yield $request->response->setStatusCode(200)->setHeader('Content-Type', $mime)->setBody($body);
                } else {
                    throw new RouteExcept('404 Not Found', 404);
                }
            }
        } else {
            if (!class_exists($router->getClass())) {
                throw new RouteExcept("500 Internal Server Error: class {$router->getClass()} does not exist", 500);
            } elseif (!method_exists($router->getClass(), $router->getMethod())) {
                throw new RouteExcept("500 Internal Server Error: method {$router->getMethod()} does not exist", 500);
            }
            $request->inject(Route::class, $router);
            $blocking = false;
            foreach (Facades\Config::get('http', [])['middlewares'] as $middleware) {
                if ($response = $request->callUserFunction([$request->make($middleware), 'handle'])) {
                    yield $response;
                    $blocking = true;
                }
            }
            foreach ($router->getMiddlewares() as $middleware) {
                if ($response = $request->make($middleware)->handle($request)) {
                    yield $response;
                    $blocking = true;
                }
            }
            foreach ($request->callUserFunction([$router->getClass(), $router->getMethod()]) as $response) {
                if ($response instanceof Response) {
                    if (!$blocking) {
                        yield $response;
                    }
                } else {
                    yield $response;
                }
            }
        }
    }

    /**
     * 异常处理
     * @param mixed   $error
     * @param Request $request
     * @return void
     * @throws Throwable
     */
    private function exceptionHandler(mixed $error, Request $request): void
    {
        if ($error instanceof JsonException) {
            $request->respondJson([
                'code' => $error->getCode(),
                'msg'  => $error->getMessage(),
                'data' => $error->data,
            ]);
            $request->client->send($request->response->__toString());
            return;
        }

        $errorInfo = [
            'title'  => $error->getMessage(),
            'traces' => $error->getTrace(),
            'file'   => $error->getFile(),
            'line'   => $error->getLine(),
        ];
        if (in_array($request->headerArray['accept'] ?? 'text/html', ['application/json', 'text/json'])) {
            $request->respondJson($errorInfo)->setStatusCode($error->getCode())->__toString();
        } else {
            $html = View::make('trace', $errorInfo)->render();
            try {
                $request->respondBody($html)->setStatusCode($error->getCode());
            } catch (Throwable $exception) {
                Output::printException($exception);
            }
        }
        $request->client->send($request->response->__toString());
    }
}
