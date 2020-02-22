<?php

namespace App\Listener;

use App\Constants\Session;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\OnWorkerStop;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Timer;

class WorkerListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger    = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            OnWorkerStop::class,
            AfterWorkerStart::class
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof AfterWorkerStart) {
            Timer::tick(config('tick_heartbeat_time'), function () use ($event) {
                $redis = $this->container->get(\Redis::class);
                foreach ($event->server->heartbeat(true) as $fd) {
                    if ($redis->sIsMember(Session::FD_KEY, $fd)) {
                        $redis->sRem(Session::FD_KEY, $fd);
                        $event->server->close($fd);
                        $this->logger->info(sprintf('[Redis] Del Fd:[%s]', $fd));
                    }
                }
            });
        } elseif ($event instanceof OnWorkerStop) {
            $redis = $this->container->get(\Redis::class);
            $redis->del(Session::FD_KEY);
            $this->logger->info(sprintf('[Redis] Del Key:[%s]', Session::FD_KEY));
        }
    }
}
