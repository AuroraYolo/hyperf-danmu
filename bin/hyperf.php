#!/usr/bin/env php
<?php
//php ini设置
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
//设置上海时区
date_default_timezone_set('Asia/Shanghai');
// 定义常量 BASE_PATH, 所有路径相关都会使用这个常量
!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
// 定义swoole全局HOOK配置
!defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(function ()
{
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';
    $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
    $application->run();
})();
