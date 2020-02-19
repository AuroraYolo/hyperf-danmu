<?php
namespace App\Lib\Barrage;

use App\Components\Http\Client;
use App\Lib\Barrage\Platform\Douyu;
use QL\QueryList;

class Search
{
    public static function searchRooms(string $keywords)
    {
        $url = sprintf(Douyu::ROOM_SEARCH_URL, rawurlencode($keywords));
        /**
         * @var \Swlib\Http\Response [] $res
         */
        $res     = Client::get($url);
        $content = $res[0]->getBody()->getContents();
        $ql      = QueryList::html($content);
        $ids     = $ql->find('.layout-play-list a')->attrs('href')->toArray();
        $titles  = $ql->find('.layout-play-list li a h3')->texts('title')->toArray();
        $names   = $ql->find('.layout-play-list li a h2')->texts()->toArray();
        return array_map(null, $ids, $titles, $names);
    }
}
