<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Logger;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            //注解扫描目录 默认是/src
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            //用来加载组件提供的默认配置文件, 或者其他一些组件提供的 demo 文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for logger.',
                    'source' => __DIR__ . '/../publish/logger.php',
                    'destination' => BASE_PATH . '/config/autoload/logger.php',
                ],
            ],
            //依赖关系,用于解耦
            'dependencies'=>[
            ],
            // 部分 hyperf 组件有有自定义的 command, php bin/hyperf.php 看到的命令, 配置就是这里来的
            'commands' => [
            ],
        ];
    }
}
