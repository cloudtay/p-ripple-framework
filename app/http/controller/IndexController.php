<?php

namespace app\http\controller;

use app\http\attribute\EnableSession;
use app\http\attribute\PreventLoggedRequest;
use app\http\attribute\PreventNotLoggedRequest;
use app\http\attribute\Validate;
use app\http\service\validator\LoginFormValidator;
use app\model\UserModel;
use app\service\WebSocketService;
use Cclilshy\PRipple\Http\Service\Request;
use Facade\JsonRpc;
use Generator;
use Illuminate\Support\Facades\View;
use PRipple;
use PRipple\Framework\Exception\JsonException;
use PRipple\Framework\Facades\Log;
use PRipple\Framework\Route\Route;
use PRipple\Framework\Session\Session;
use RedisException;

/**
 * @Class IndexController
 * 类也支持注解,注解支持递归
 */
#[EnableSession] //该控制器所有方法都使用Session
class IndexController
{
    public static function index(Request $request): Generator
    {
        yield $request->respondBody('Hello,World!');
    }

    #[PreventNotLoggedRequest] // 未登录自动阻断
    public static function info(Request $request): Generator
    {
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'pid'       => posix_getpid(),
                'rpc'       => array_keys(JsonRpc::getInstance()->rpcServiceConnections),
                'configure' => PRipple::getArgument()
            ]
        ]);
    }

    #[PreventNotLoggedRequest] // 未登录自动阻断
    public static function data(Request $request): Generator
    {
        $data = UserModel::query()->first();
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => $data
        ]);
    }

    #[PreventNotLoggedRequest] // 未登录自动阻断
    public static function notice(Request $request): Generator
    {
        if ($message = $request->query('message')) {
            JsonRpc::call([WebSocketService::class, 'sendMessageToAll'], $message);
            yield $request->respondJson([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'message' => $message
                ]
            ]);

            // 请求结束后执行
            $request->defer(fn() => Log::write("notice:$message"));
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
     * @param Request  $request  自动依赖注入
     * @param Validate $validate 注解依赖注入
     * @param Session  $session  注解依赖注入
     * @return Generator
     * @throws JsonException
     * @throws RedisException
     */
    #[PreventLoggedRequest]                // 禁止已登陆的用户访问
    #[Validate(LoginFormValidator::class)] // 自动化表单验证
    public static function login(Request $request, Validate $validate, Session $session): Generator
    {
        if ($session->get('username')) {
            throw new JsonException('login success');
        } elseif ($validate->validator->fails()) {
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

    /**
     * @throws RedisException
     */
    #[PreventNotLoggedRequest] // 未登录自动阻断
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

    #[EnableSession] // 启用Session
    public static function download(Request $request): Generator
    {
        yield $request->respondFile(__DIR__ . '/Index.php', 'Index.php');
    }

    #[EnableSession] // 启用Session
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

