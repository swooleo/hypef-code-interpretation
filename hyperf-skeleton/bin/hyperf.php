#!/usr/bin/env php
<?php

//php.ini的基础配置
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);

//定义常量BASE_PATH,所有的路径相关都使用这个常量
! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
//定义swoole一键协程化
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

//这个不多说,大家都懂composer的自动加载
require BASE_PATH . '/vendor/autoload.php';

// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(function () {
    Hyperf\Di\ClassLoader::init();
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';

    $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
    $application->run();
})();
