<?php
namespace App\Listener;

use App\Constants\Session;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnWorkerStop;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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

    public function listen() : array
    {
        return [
            OnWorkerStop::class,
        ];
    }

    public function process(object $event)
    {
        $redis = $this->container->get(\Redis::class);
        $redis->del(Session::FD_KEY);
        $this->logger->info(sprintf('[Redis] Del Key:[%s]', Session::FD_KEY));
    }

}
