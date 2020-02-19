<?php

declare(strict_types = 1);

namespace App\Amqp\Consumer;

use App\Constants\Session;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\WebSocketServer\Sender;
use Nette\Utils\Json;
use Redis;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", name ="DanmuConsumer", nums=1,enable=true)
 */
class DanmuConsumer extends ConsumerMessage
{
    public function consume($data) : string
    {
        $sender = $this->container->get(Sender::class);
        $redis  = $this->container->get(Redis::class);
        $fds    = $redis->sMembers(Session::FD_KEY);
        if (empty($fds)) {
            return  Result::ACK;
        }
        foreach ($fds as $fd) {
            $sender->push((int)$fd, Json::encode($data));
        }
        return Result::ACK;
    }

    public function isEnable() : bool
    {
        return true;
    }
}
