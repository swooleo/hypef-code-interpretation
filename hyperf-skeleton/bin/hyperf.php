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
   //ClassLoader的初始化,类的反射和环境配置的加载
    Hyperf\Di\ClassLoader::init();
    //重中之重 contaiiner hyperf的依赖注入管理容器，几乎所有的组件服务。
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';

    //启动服务 Symfony\Component\Console\Application Hyperf 的命令管理默认由 symfony/console 提供支持
    //执行 php bin/hyperf.php start 后，将由 Hyperf\Server\Command\StartServer 命令类接管，
    //并根据配置文件 config/autoload/server.php 内定义的 Server 逐个启动
    /**
     * @var \Symfony\Component\Console\Application $application
     */
    $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
    $application->run();
})();
