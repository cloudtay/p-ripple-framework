<?php

namespace component;

use Cclilshy\LaravelComponentCore\Kernel;
use Cclilshy\PRipple\Component\LaravelComponent;
use Cclilshy\PRipple\Framework\Std\ComponentInterface;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\View;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine as ViewCompilerEngine;
use Illuminate\View\Engines\EngineResolver as ViewEngineResolver;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\FileViewFinder as ViewFileFinder;

class ViewComponent implements ComponentInterface
{
    /**
     * @return void
     */
    public static function initialize(): void
    {
        $viewPaths = [
            ROOT_PATH . '/src/Resources/Views',
            ROOT_PATH . '/app/http/view'
        ];
        if ($viewPath = $config['VIEW_PATH_BLADE'] ?? null) {
            if (is_array($viewPath)) {
                $viewPaths = array_merge($viewPaths, $viewPath);
            } else {
                $viewPaths[] = $viewPath;
            }
        }
        $cachePath = PP_RUNTIME_PATH . '/cache';
        if (!is_dir($cachePath)) {
            mkdir($cachePath);
        }
        ViewComponent::initViewEngine($viewPaths, $cachePath);
    }

    /**
     * 注册模板引擎
     * @param array       $viewPaths
     * @param string|null $cachePath 缓存文件路径
     * @return void
     */
    public static function initViewEngine(array $viewPaths, string|null $cachePath = '/tmp'): void
    {
        $filesystem         = Kernel::getInstance()->filesystem;
        $eventDispatcher    = Kernel::getInstance()->eventDispatcher;
        $container          = LaravelComponent::$laravel->container;
        $bladeCompiler      = new BladeCompiler($filesystem, $cachePath);
        $viewEngineResolver = new ViewEngineResolver();
        $viewEngineResolver->register('blade', function () use ($bladeCompiler) {
            return new ViewCompilerEngine($bladeCompiler);
        });
        $viewFileFinder = new ViewFileFinder($filesystem, $viewPaths);
        $factory        = new ViewFactory($viewEngineResolver, $viewFileFinder, $eventDispatcher);
        View::setFacadeApplication($container);
        $container->singleton('view', function (Container $container) use ($factory) {
            return $factory;
        });
    }
}
