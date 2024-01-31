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

use Cclilshy\PRipple\Core\Kernel;
use Cclilshy\PRipple\Core\Map\WorkerMap;
use Cclilshy\PRipple\Core\Output;
use Cclilshy\PRipple\Database\Proxy\PDOProxyPool;
use Cclilshy\PRipple\Framework\Facades\Route;
use Cclilshy\PRipple\Framework\Route\RouteMap;
use Cclilshy\PRipple\Http\Service\HttpWorker;
use Cclilshy\PRipple\PRipple;
use Cclilshy\PRipple\Redis\Facade\RedisClient as RedisClientFacade;
use Cclilshy\PRipple\Redis\RedisClient;
use Cclilshy\PRipple\Worker\Worker;
use Illuminate\Translation\Translator;
use Throwable;

class Loader
{
    public Kernel     $kernel;
    public HttpWorker $httpWorker;
    public RouteMap   $routeMap;
    public Translator $translator;

    /**
     * @return void
     */
    public function __construct(string $path)
    {
        define('ROOT_PATH', realpath($path));
        define('APP_PATH', ROOT_PATH . '/app');
        define('HTTP_PATH', ROOT_PATH . '/app/http');
        define('ROUTES_PATH', ROOT_PATH . '/app/http/route');
        define('RUNTIME_PATH', ROOT_PATH . '/runtime');
        define('CONFIG_PATH', ROOT_PATH . '/config');
        $this->kernel = PRipple::configure([
            'PP_RUNTIME_PATH' => '/tmp',
            'PP_LOG_PATH'     => RUNTIME_PATH . '/log',
            'PP_LANG_PATH'    => ROOT_PATH . '/resource/lang',
        ]);
        $this->initialize();
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        try {
            $this->initializeConfig();
            $this->initComponent();
            $this->initializeRedis();
            $this->initializeDatabase();
            $this->initializeRoutes();
            $this->initializeHttpWorker();
            $this->initializeWebApplication();
            $this->initializeWebConstruct();
            $this->kernel->push($this->httpWorker);
        } catch (Throwable $exception) {
            Output::printException($exception);
            exit(0);
        }
    }

    /**
     * @return void
     */
    private function initializeConfig(): void
    {
        array_map(function ($file) {
            if (is_file($pathFull = CONFIG_PATH . FS . $file)) {
                $configName  = basename($file, '.php');
                $configValue = require $pathFull;
                PRipple::config($configName, $configValue);
            }
        }, scandir(CONFIG_PATH));
    }

    /**
     * @return void
     */
    private function initializeRedis(): void
    {
        $redisServices = PRipple::getArgument('redis');
        if (count($redisServices) > 0) {
            $this->kernel->push(RedisClient::new(RedisClient::class));
            WorkerMap::get(RedisClient::class)->initialize();
            foreach (PRipple::getArgument('redis') as $name => $config) {
                RedisClientFacade::addClient($config, $name);
            }
        }
    }

    /**
     * @return void
     */
    private function initializeDatabase(): void
    {
        foreach (PRipple::getArgument('database') as $name => $config) {
            $databasePool = new PDOProxyPool($config, $name);
            $databasePool->run($config['thread'] ?? 1);
            $this->kernel->push($databasePool);
        }
    }

    /**
     * @return void
     */
    private function initializeRoutes(): void
    {
        Route::setRouteMapInstance($this->routeMap = new RouteMap());
        array_map(function ($file) {
            if (is_file($pathFull = ROUTES_PATH . FS . $file)) {
                require $pathFull;
            }
        }, scandir(ROUTES_PATH));
    }

    /**
     * @return void
     */
    private function initializeHttpWorker(): void
    {
        $address          = PRipple::getArgument('http')['address'] ?? '0.0.0.0';
        $port             = PRipple::getArgument('http')['port'] ?? 8008;
        $thread           = PRipple::getArgument('http')['thread'] ?? 1;
        $addressFull      = "tcp://{$address}:{$port}";
        $this->httpWorker = HttpWorker::new(HttpWorker::class)
            ->bind($addressFull, [SO_REUSEPORT => 1, SO_REUSEADDR => 1])
            ->mode(Worker::MODE_INDEPENDENT, $thread);
    }

    /**
     * @return void
     */
    private function initializeWebApplication(): void
    {
        $httpUploadPath = PRipple::getArgument('http')['upload_path'] ?? RUNTIME_PATH . '/temp';
        $httpPublic     = PRipple::getArgument('http')['public'] ?? ROOT_PATH . '/public';
        $sessionType    = PRipple::getArgument('session')['type'] ?? 'file';
        $sessionPath    = PRipple::getArgument('session')['path'] ?? RUNTIME_PATH . '/session';
        Core::install($this->httpWorker, $this->routeMap, [
            'HTTP_UPLOAD_PATH' => $httpUploadPath,
            'SESSION_TYPE'     => $sessionType,
            'SESSION_PATH'     => $sessionPath,
            'HTTP_PUBLIC'      => $httpPublic,
            'VIEW_PATH_BLADE'  => ROOT_PATH . '/app/view',
        ]);
    }

    /**
     * @return void
     */
    private function initializeWebConstruct(): void
    {
        foreach (PRipple::getArgument('construct') as $class) {
            call_user_func([$class, 'handle'], $this->kernel);
        }
    }

    /**
     * @return void
     */
    private function initComponent(): void
    {
        foreach (PRipple::getArgument('component') as $class) {
            call_user_func([$class, 'initialize']);
        }
    }

    /**
     * @param string $projectPath
     * @return Loader
     */
    public static function makeBuildProject(string $projectPath): Loader
    {
        return new Loader($projectPath);
    }
}
