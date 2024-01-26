<?php
/**
 * 数据库配置
 * @option thread  线程数
 * @option options PDO选项,默认为 [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
 */
return [
    'default' => [
        'driver'                  => 'sqlite',
        'url'                     => ROOT_PATH . '/resource/database/base.sqlite',
        'database'                => 'main',
        'prefix'                  => '',
        'foreign_key_constraints' => true,
        'thread'                  => 4,
        'options'                 => [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
    ],

    /**
     * MYSQL配置模板
     */
    //    'mysql'   => [
    //        'driver'    => 'mysql',
    //        'host'      => 'localhost',
    //        'database'  => 'database',
    //        'username'  => 'root',
    //        'password'  => 'password',
    //        'charset'   => 'utf8',
    //        'collation' => 'utf8_unicode_ci',
    //        'prefix'    => '',
    //        'options'   => [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
    //    ]
];
