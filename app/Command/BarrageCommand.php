<?php

declare(strict_types = 1);

namespace App\Command;

use App\Lib\Barrage\Search;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class BarrageCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('barrage:search');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('搜索直播房间');
        $this->setHelp('Hyperf 自定义命令演示');
        $this->addUsage('--keywords 演示代码');
    }

    public function handle()
    {
        $argument = $this->input->getArgument('keywords') ?? '英雄联盟';
        $logo     = <<<STR
         _-_-/  ,                   ,,
       (_/     ||         '         ||    _      _
      (_ --_  =||=  _-_  \\  \\/\\  ||   < \,   / \\   _-_  ,._-_,
        --_ )  ||  || \\ ||  || ||  ||   /-||  || ||  || \\  ||
      _,   ))  ||  ||/   ||  || ||  ||  (( ||  || ||  ||/    ||
     (-_-_-``   \\, \\,/  \\  \\ \\  \\  \/\\  \\_||  \\,/   \\,
                                             /\\  \\
                                            (  \\_//
               __        ___  __                     __  /  __
         |\ | |_  |  |    _/ |_   /\  |    /\  |\ | |  \   (_
         | \| |__ |/\|   /__ |__ /--\ |__ /--\ | \| |__/   __)

                              .sssssssss.
                        .sssssssssssssssssss
                      sssssssssssssssssssssssss
                     ssssssssssssssssssssssssssss
                      @@sssssssssssssssssssssss@ss
                      |s@@@@sssssssssssssss@@@@s|s
               _______|sssss@@@@@sssss@@@@@sssss|s
             /         sssssssss@sssss@sssssssss|s
            /  .------+.ssssssss@sssss@ssssssss.|
           /  /       |...sssssss@sss@sssssss...|
          |  |        |.......sss@sss@ssss......|
          |  |        |..........s@ss@sss.......|
          |  |        |...........@ss@..........|
           \  \       |............ss@..........|
            \  '------+...........ss@...........|
             \________ .........................|
                      |.........................|
                     /...........................\
                    |.............................|
                       |.......................|
                           |...............|

               __         __  __ ___      __   __  __  __
              |_  | |\ | |_  (_   |      |__) |_  |_  |__)
              |   | | \| |__ __)  |      |__) |__ |__ | \
STR;

        $this->line(
            $logo . PHP_EOL
            , 'comment');
        $res = $this->filter(Search::searchRooms($argument));
        $this->table(['房间号', '房间名称', '主播名称'], $res);
    }

    protected function getArguments()
    {
        return [
            ['keywords', InputArgument::REQUIRED, '请输入关键字搜索']
        ];
    }

    protected function filter(array $rooms)
    {
        foreach ($rooms as $key => $room) {
            $rid            = ltrim($room[0], '/');
            $rooms[$key][0] = $rid;
            if (!is_numeric($rid)) {
                unset($rooms[$key]);
                continue;
            }
        }
        return array_values($rooms);
    }
}
