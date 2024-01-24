<?php

return [
    #监听端口
    'host'           => '0.0.0.0',

    #静态根目录
    'public'         => APP_PATH . '/http/public',

    #监听端口
    'port'           => 8008,

    #监听线程
    'thread'         => 1,

    #超时时间(s)
    'timeout'        => 60,

    #上传超时时间(s)
    'timeout_upload' => 120,

    #全局中间件
    'middlewares'    => [

    ]
];
