<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use App\Amqp\Producer\DanmuProducer;
use App\Components\Http\Client;
use Hyperf\Amqp\Producer;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
//        $message = new DanmuProducer(1);
//        $producer = $this->container->get(Producer::class);
//        $result = $producer->produce($message);
        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function http(){
        /**
         * @var \Swlib\Http\Response [] $res
         */
       $res =  Client::get(sprintf('https://douyu.com/search?kw=%s','英雄联盟'));
        var_dump($res[0]->getBody()->getContents());
    }
}
