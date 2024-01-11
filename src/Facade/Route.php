<?php

namespace Cclilshy\PRippleFramework;

use Cclilshy\PRippleWeb\Route as Router;
use Cclilshy\PRippleWeb\RouteMap;

class Route
{
    public const string GET     = 'GET';
    public const string POST    = 'POST';
    public const string PUT     = 'PUT';
    public const string DELETE  = 'DELETE';
    public const string PATCH   = 'PATCH';
    public const string HEAD    = 'HEAD';
    public const string OPTIONS = 'OPTIONS';
    public const string TRACE   = 'TRACE';
    public const string CONNECT = 'CONNECT';
    public const string STATIC  = 'STATIC';

    /**
     * @var RouteMap $routeMap
     */
    public static RouteMap $routeMap;

    /**
     * @param string $method
     * @param string $path
     * @param array  $route
     * @return Router
     */
    public static function define(string $method, string $path, array $route): Router
    {
        return Route::$routeMap->define($method, $path, $route);
    }

    /**
     * @param RouteMap $routeMap
     * @return RouteMap
     */
    public static function setRouteMapInstance(RouteMap $routeMap): RouteMap
    {
        return Route::$routeMap = $routeMap;
    }
}
