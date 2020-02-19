<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Constants\Session;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController extends AbstractController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    public function onMessage(WebSocketServer $server, Frame $frame) : void
    {
//        foreach ($server->connections as $fd) {
//            if (!$server->isEstablished($fd)) {
//                // 如果连接不可用则忽略
//                continue;
//            }
//            $server->push($fd, $frame->data); // 服务端通过 push 方法向所有连接的客户端发送数据
//        }
    }

    public function onClose(Server $server, int $fd, int $reactorId) : void
    {
        $redis = $this->container->get(\Redis::class);
        $redis->sRem(Session::FD_KEY,$fd);
    }

    public function onOpen(WebSocketServer $server, Request $request) : void
    {
        $this->container->get(StdoutLoggerInterface::class)->info('open fd:' . $request->fd);
        $redis = $this->container->get(\Redis::class);
        if (!$redis->sIsMember(Session::FD_KEY, $request->fd)) {
            $redis->sAdd(Session::FD_KEY, $request->fd);
        }
    }
}
