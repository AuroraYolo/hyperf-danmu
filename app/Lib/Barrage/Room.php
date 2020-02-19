<?php
namespace App\Lib\Barrage;

use App\Components\Http\Client;
use App\Lib\Barrage\Platform\Douyu;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Throwable;

class Room
{
    /**
     * 查看房间号是否存在
     *
     * @param $roomid
     *
     * @return bool|mixed
     */
    public static function checkRoomExist($roomid)
    {
        $url = sprintf(Douyu::ROOM_INFO_URL, $roomid);
        try {
            /**
             * @var \Swlib\Http\Response [] $res
             */
            $res     = Client::get($url);
            $content = $res[0]->getBody()->getContents();
            $jsonArr = Json::decode($content, true);
            if ($jsonArr['error'] == 0) {
                return $jsonArr;
            }
            return false;
        } catch (Throwable $exception) {
            ApplicationContext::getContainer()->get(StdoutLoggerInterface::class)->error(printf('%s[%s] in %s', $exception->getMessage(), $exception->getLine(), $exception->getFile()));
        }
        return false;
    }
}
