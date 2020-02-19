<?php

declare(strict_types = 1);

namespace App\Command;

use App\Amqp\Producer\DanmuProducer;
use App\Lib\Barrage\Message;
use App\Lib\Barrage\Platform\Douyu;
use App\Lib\Barrage\Room;
use Hyperf\Amqp\Producer;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Client;
use Swoole\Timer;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class LiveCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var array|false
     */
    protected $roomInfo;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('live:in');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('进入直播间');
        $this->setHelp('Hyperf 自定义命令演示');
        $this->addUsage('--roomid 演示代码');
    }

    protected function getArguments()
    {
        return [
            ['roomid', InputArgument::REQUIRED, '请输入房间号']
        ];
    }

    public function handle()
    {
        $argument = $this->input->getArgument('roomid');
        if (!$this->roomInfo = Room::checkRoomExist($argument)) {
            return $this->error(Douyu::MSG_ERROR_ROOM_NOT_EXIST);
        }
        if ($this->roomInfo['data']['room_status'] == 2) {
            return $this->error(Douyu::MSG_ERROR_ROOM_NOT_OPEN);
        }
        $this->output->writeln(Douyu::showMsg(Douyu::MSG_ENTER));
        $client = new Client(SWOOLE_SOCK_TCP);
        // 连接
        if (!$client->connect(Douyu::SITE_NAME, Douyu::port(), 0.5)) {
            return $this->error("Connect failed");
        }
        $this->output->writeln(Douyu::showMsg(Douyu::MSG_LOADING));
        $client->send(Douyu::packMsg(Douyu::SEND_MSG_LOGIN, $argument));
        $client->send(Douyu::packMsg(Douyu::SEND_MSG_JOIN_ROOM, $argument));
        $message  = new DanmuProducer(1);
        $producer = $this->container->get(Producer::class);
        if ($client->isConnected()) {
            //设置定时器，发送心跳
            Timer::tick(45000, function () use ($client)
            {
                $client->send(Douyu::packMsg(Douyu::SEND_MSG_KEEP_LIVE));
            });
            while (true) {
                $recv          = $client->recv();
                $receiveResult = Message::handle($recv);
                if (!empty($receiveResult)) {
                    array_walk($receiveResult['msg'], function ($msg)
                    {
                        if (config('show_time')) {
                            $date = date("Y-m-d H:i:s");
                            $msg  = $date . ' ' . $msg;
                        }
                        $this->output->writeln($msg);
                    });
                    array_walk($receiveResult['amqp'], function ($msg) use ($message, $producer)
                    {
                        try {
                            if ($producer->produce($message->setPayload($msg))) {
                                $this->output->writeln('<fg=green>' . date('Y-m-d H:i:s') . ' ' . '消息投递成功</>');
                            };
                        } catch (\Throwable $throwable) {
                            $this->container->get(StdoutLoggerInterface::class)->error(sprintf('Runtime:[%s] [%s] [%s] [Message]:[%s]', $throwable->getFile(), $throwable->getMessage(), $throwable->getLine(), $msg));
                        }
                    });
                }
            }
        }
        $this->output->writeln('<fg=red>' . date('Y-m-d H:i:s') . ' ' . '服务启动失败!</>');
    }
}
