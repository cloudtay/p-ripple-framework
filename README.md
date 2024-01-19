## Brief introduction

<p align="center">
<img src="https://cloudtay.com/static/image/logo-wide.png" width="420" alt="Logo">
</p>

`内核` [https://github.com/cloudtay/p-ripple](https://github.com/cloudtay/p-ripple.git)

`内核文档` [https://cloudtay.github.io/p-ripple-document/](https://cloudtay.github.io/p-ripple-document/)

> The documentation is being assembled, so check out the controller examples

## Directory structure

```text
ROOT
├──── app - 应用目录
│     ├── construct        # Construct a service process
│     ├── http             # HTTP applications
│     │     ├── attribute  # Commentary analyzer
│     │     ├── controller # Controller
│     │     ├── middleware # Middleware
│     │     ├── public     # Public directories
│     │     ├── route      # Route configuration
│     │     ├── service    # Business Services
│     │     └── view       # View files
│     ├── model            # Data model
│     └── service          # Resident service
├──── component            # Component-resident construction
├──── config               # Common configuration
├──── resource             # Resource directory
│     ├── cert             # Certificate
│     ├── database         # SQLite file
│     └── lang             # Multilingual documents
└──── runtime              # Runtime directory
```

## Example

```php
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
 * Classes also support annotations, which support recursion
 */
#[EnableSession] //The controller uses Session for all methods
class IndexController
{
    public static function index(Request $request): Generator
    {
        yield $request->respondBody('Hello,World!');
    }

    #[PreventNotLoggedRequest] // Failure to log in is automatically blocked
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

    #[PreventNotLoggedRequest] 
    public static function data(Request $request): Generator
    {
        $data = UserModel::query()->first();
        yield $request->respondJson([
            'code' => 0,
            'msg'  => 'success',
            'data' => $data
        ]);
    }

    #[PreventNotLoggedRequest] 
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
     * @param Request  $request  Automatic dependency injection
     * @param Validate $validate Attribute dependency injection
     * @param Session  $session  Attribute recursion dependency injection
     * @return Generator
     * @throws JsonException
     * @throws RedisException
     */
    #[PreventLoggedRequest]                // Prohibit logged-in users
    #[Validate(LoginFormValidator::class)] // Automate form validation
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
    #[PreventNotLoggedRequest] 
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

    public static function download(Request $request): Generator
    {
        yield $request->respondFile(__DIR__ . '/Index.php', 'Index.php');
    }

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
