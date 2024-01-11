# p-ripple-framework

The documentation is being assembled, welcome to see the controller example

```php
<?php

namespace app\controller;

use app\model\UserModel;
use app\service\WebSocketService;
use Cclilshy\PRippleHttpService\Request;
use Facade\JsonRpc;
use Generator;
use Illuminate\Support\Facades\View;
use PRipple;
use PRipple\Framework\Route\Route;
use PRipple\Framework\Session\Session;

class IndexController
{
    /**
     * @param Request $request
     * @return Generator
     */
    public static function index(Request $request): Generator
    {
        yield $request->respondBody('Hello,World!');
    }

    /**
     * @param Request $request 实现了 Coroutine(协程构建) 接口的请求对象
     * @return Generator 返回一个生成器
     */
    public static function info(Request $request): Generator
    {
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'pid'       => posix_getpid(),
                'rpc'       => array_keys(JsonRpc::getInstance()->rpcServiceConnections),
                'configure' => PRipple::getArgument()
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Generator
     */
    public static function data(Request $request): Generator
    {
        $data = UserModel::query()->first();
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => $data
        ]);
    }

    /**
     * @param Request $request
     * @return Generator
     */
    public static function notice(Request $request): Generator
    {
        if ($message = $request->query['message'] ?? null) {
            JsonRpc::call([WebSocketService::class, 'sendMessageToAll'], $message);
            yield $request->respondJson([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'message' => $message
                ],
            ]);
        } else {
            yield $request->respondJson([
                'code' => 1,
                'msg'  => 'error',
                'data' => [
                    'message' => 'message is required'
                ],
            ]);
        }
    }

    /**
     * @param Request $request
     * @param Session $session
     * @return Generator
     */
    public static function login(Request $request, Session $session): Generator
    {
        if ($name = $request->query('username')) {
            $session->set('username', $name);
            yield $request->respondJson([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'message' => 'login success,' . $name
                ],
            ]);
        } elseif ($name = $session->get('username')) {
            yield $request->respondJson([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'message' => 'hello,' . $name
                ],
            ]);
        } else {
            yield $request->respondJson([
                'code' => 1,
                'msg'  => 'error',
                'data' => [
                    'message' => 'name is required'
                ],
            ]);
        }
    }

    /**
     * @param Request $request
     * @param Session $session
     * @return Generator
     */
    public static function logout(Request $request, Session $session): Generator
    {
        $session->clear();
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'message' => 'logout success'
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Generator
     */
    public static function download(Request $request): Generator
    {
        yield $request->respondFile(__DIR__ . '/Index.php', 'Index.php');
    }

    /**
     * @param Request $request
     * @return Generator
     */
    public static function upload(Request $request): Generator
    {
        if ($request->method === Route::GET) {
            $template = View::make('upload', ['title' => 'please select upload file'])->render();
            yield $request->respondBody($template);
        } else {
            yield $request->respondBody('wait...');
            if ($request->upload) {
                $request->on(Request::ON_UPLOAD, function (array $fileInfo) {
                    JsonRpc::call([WebSocketService::class, 'sendMessageToAll'], 'Upload File Info:' . json_encode($fileInfo));
                });
            }
        }
    }
}

```
