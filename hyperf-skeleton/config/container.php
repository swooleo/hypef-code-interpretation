<?php
/**
 * Initialize a dependency injection container that implemented PSR-11 and return the container.
 */

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;
//Container容器初始化  并不是在初始化的时候所有的对象就被实例化了(单例)，实例化的对象都会放在Container $resolvedEntries属性里。只有在第一次get的时候会被放到 Container $resolvedEntries属性里
//大家可以看看文档依赖注入里获取容器对象get()方法里。
$container = new Container((new DefinitionSourceFactory(true))());

if (! $container instanceof \Psr\Container\ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}
//存储整个容器对象
return ApplicationContext::setContainer($container);
