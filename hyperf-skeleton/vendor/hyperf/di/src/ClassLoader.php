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
namespace Hyperf\Di;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter;
use Dotenv\Repository\RepositoryBuilder;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\LazyLoader\LazyLoader;
use Hyperf\Utils\Composer;

class ClassLoader
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $composerClassLoader;

    /**
     * The container to collect all the classes that would be proxy.
     * [ OriginalClassName => ProxyFileAbsolutePath ].
     *
     * @var array
     */
    protected $proxies = [];

    public function __construct(ComposerClassLoader $classLoader, string $proxyFileDir, string $configDir)
    {
        $this->setComposerClassLoader($classLoader);
        //加载环境配置
        if (file_exists(BASE_PATH . '/.env')) {
            $this->loadDotenv();
        }

        //划重点  扫描注解并生成反射
        // Scan by ScanConfig to generate the reflection class map
        $scanner = new Scanner($this, $config = ScanConfig::instance($configDir));
        $classLoader->addClassMap($config->getClassMap());
        $reflectionClassMap = $scanner->scan();
        // Get the class map of Composer loader
        $composerLoaderClassMap = $this->getComposerClassLoader()->getClassMap();
        $proxyManager = new ProxyManager($reflectionClassMap, $composerLoaderClassMap, $proxyFileDir);
        $this->proxies = $proxyManager->getProxies();
    }

    public function loadClass(string $class): void
    {
        $path = $this->locateFile($class);

        if ($path) {
            include $path;
        }
    }

    public static function init(?string $proxyFileDirPath = null, ?string $configDir = null): void
    {
        //如果生成了代理文件,加载代理类文件
        if (! $proxyFileDirPath) {
            // This dir is the default proxy file dir path of Hyperf
            $proxyFileDirPath = BASE_PATH . '/runtime/container/proxy/';
        }
        //默认配置目录 /config/下
        if (! $configDir) {
            // This dir is the default config file dir path of Hyperf
            $configDir = BASE_PATH . '/config/';
        }
        //获取所有的自动加载(composer)
        $loaders = spl_autoload_functions();

        // Proxy the composer class loader
        foreach ($loaders as &$loader) {
            $unregisterLoader = $loader;
            //获取composer loader里加载的对象
            if (is_array($loader) && $loader[0] instanceof ComposerClassLoader) {
                /** @var ComposerClassLoader $composerClassLoader */
                $composerClassLoader = $loader[0];
                AnnotationRegistry::registerLoader(function ($class) use ($composerClassLoader) {
                    return (bool) $composerClassLoader->findFile($class);
                });
                //这里重新加载composerLoader proxy 和配置 生成反射类
                $loader[0] = new static($composerClassLoader, $proxyFileDirPath, $configDir);
            }
            //注销其他的自动加载
            spl_autoload_unregister($unregisterLoader);
        }

        unset($loader);

        //重新加载自动注册
        // Re-register the loaders
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Initialize Lazy Loader. This will prepend LazyLoader to the top of autoload queue.
        LazyLoader::bootstrap($configDir);
    }

    public function setComposerClassLoader(ComposerClassLoader $classLoader): self
    {
        $this->composerClassLoader = $classLoader;
        // Set the ClassLoader to Hyperf\Utils\Composer to avoid unnecessary find process.
        Composer::setLoader($classLoader);
        return $this;
    }

    public function getComposerClassLoader(): ComposerClassLoader
    {
        return $this->composerClassLoader;
    }

    protected function locateFile(string $className): ?string
    {
        if (isset($this->proxies[$className]) && file_exists($this->proxies[$className])) {
            $file = $this->proxies[$className];
        } else {
            $file = $this->getComposerClassLoader()->findFile($className);
        }

        return is_string($file) ? $file : null;
    }

    protected function loadDotenv(): void
    {
        $repository = RepositoryBuilder::create()
            ->withReaders([
                new Adapter\PutenvAdapter(),
            ])
            ->withWriters([
                new Adapter\PutenvAdapter(),
            ])
            ->immutable()
            ->make();

        Dotenv::create($repository, [BASE_PATH])->load();
    }
}
