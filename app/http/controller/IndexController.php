<?php

namespace app\http\controller;

use app\http\attribute\PreventLoggedRequest;
use app\http\attribute\PreventNotLoggedRequest;
use app\http\attribute\Validate;
use app\http\service\validator\LoginFormValidator;
use app\model\UserModel;
use app\service\WebSocketService;
use Cclilshy\PRipple\Facade\RPC;
use Cclilshy\PRipple\Framework\Exception\JsonException;
use Cclilshy\PRipple\Framework\Facades\Log;
use Cclilshy\PRipple\Framework\Route\Route;
use Cclilshy\PRipple\Framework\Session\Session;
use Cclilshy\PRipple\Http\Service\Request;
use Cclilshy\PRipple\PRipple;
use Generator;
use Illuminate\Support\Facades\View;
use RedisException;
use Throwable;

/**
 * @Class IndexController
 */
class IndexController
{
    public static function index(Request $request): Generator
    {
        return yield $request->respondBody('Hello,World!');
    }

    #[PreventNotLoggedRequest]
    public static function info(Request $request): Generator
    {
        return yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'pid'       => posix_getpid(),
                'rpc'       => array_keys(RPC::getInstance()->rpcServiceConnections),
                'configure' => PRipple::getArgument()
            ]
        ]);
    }

    #[PreventNotLoggedRequest]
    public static function data(Request $request): Generator
    {
        $data = UserModel::query()->first();
        return yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => $data
        ]);
    }

    #[PreventNotLoggedRequest]
    public static function notice(Request $request): Generator
    {
        if ($message = $request->query('message')) {
            // 请求结束后执行
            $request->defer(fn() => Log::write("notice:$message"));
            RPC::call([WebSocketService::class, 'sendMessageToAll'], $message);
            return yield $request->respondJson([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'message' => $message
                ]
            ]);
        }
        return yield $request->respondJson([
            'code' => 1,
            'msg'  => 'error',
            'data' => [
                'message' => 'message is required'
            ],
        ]);
    }

    /**
     * @throws JsonException
     * @throws RedisException
     */
    #[PreventLoggedRequest]
    #[Validate(LoginFormValidator::class)]
    public static function login(Request $request, Validate $validate, Session $session): Generator
    {
        if ($validate->validator->fails()) {
            $request->defer(fn() => Log::write("[login failed:{$request->header('REMOTE_ADDR')}]"));
            throw new JsonException($validate->validator->errors()->first());
        } else {
            $session->set('username', $username = $request->query('username'));
            return yield $request->respondJson([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'message' => 'welcome,' . $username
                ],
            ]);
        }
    }


    #[PreventNotLoggedRequest]
    public static function logout(Request $request, Session $session): Generator
    {
        try {
            $session->clear();
        } catch (RedisException $exception) {
            return yield $request->respondJson([
                'code' => -1,
                'msg'  => 'error',
                'data' => [
                    'message' => $exception->getMessage()
                ],
            ]);
        }
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'message' => 'logout success'
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return Generator
     */
    public static function download(Request $request): Generator
    {
        yield $request->respondFile(__DIR__ . '/IndexController.php', 'Index.php');
    }

    /**
     * @param Request $request
     * @return Generator
     */
    public static function upload(Request $request): Generator
    {
        if ($request->method === Route::GET) {
            $template = View::make('upload', ['title' => 'please select upload file'])->render();
            return yield $request->respondBody($template);
        } else {
            yield $request->respondBody('wait...');
            if ($request->upload) {
                $request->on(Request::ON_UPLOAD, function (array $fileInfo) {
                    RPC::call([WebSocketService::class, 'sendMessageToAll'], 'Upload File Info:' . json_encode($fileInfo));
                });
            }
        }
    }

    /**
     * @throws Throwable
     */
    public static function sleep(Request $request): Generator
    {
        \Co\sleep(5);
        yield $request->respondBody('sleep 5s');
    }
}

