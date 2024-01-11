<?php
return [
    'default' => [
        'driver'                  => 'sqlite',
        'url'                     => ROOT_PATH . '/resource/database/base.sqlite',
        'database'                => 'main',
        'prefix'                  => '',
        'thread'                  => 4,
        'foreign_key_constraints' => true,
        'options'                 => [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
    ]
];
