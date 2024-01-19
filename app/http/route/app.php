<?php

use app\http\controller\IndexController;
use PRipple\Framework\Facades\Route;
use PRipple\Framework\Middleware\SessionMiddleware;

Route::define(Route::GET, '/', [IndexController::class, 'index']);
Route::define(Route::GET, '/data', [IndexController::class, 'data']);
Route::define(Route::GET, '/notice', [IndexController::class, 'notice']);
Route::define(Route::GET, '/download', [IndexController::class, 'download']);
Route::define(Route::GET, '/upload', [IndexController::class, 'upload']);
Route::define(Route::POST, '/upload', [IndexController::class, 'upload']);

/**
 * Session requests are supported
 */
Route::define(Route::GET, '/login', [IndexController::class, 'login']);
Route::define(Route::GET, '/logout', [IndexController::class, 'logout']);
Route::define(Route::GET, '/info', [IndexController::class, 'info']);
