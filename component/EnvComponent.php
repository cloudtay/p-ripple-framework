<?php

namespace component;

use Cclilshy\PRipple\Framework\Std\ComponentInterface;
use Symfony\Component\Dotenv\Dotenv;

class EnvComponent implements ComponentInterface
{
    /**
     * @var Dotenv $dotenv
     */
    private static Dotenv $dotenv;

    /**
     * @return void
     */
    public static function initialize(): void
    {
        EnvComponent::$dotenv = new Dotenv();
        if (file_exists($envPath = ROOT_PATH . '/.env')) {
            EnvComponent::$dotenv->load($envPath);
        }
    }
}
