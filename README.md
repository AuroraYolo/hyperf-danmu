# 斗鱼弹幕 hyperf-danmu

之前别人写过一版php版本的,[传送门](https://github.com/wjhtime/douyu_danmu_php),但是主要还是用的swoole的异步客户端写的,现在swoole最新版已经去掉了async,所以基于兴趣用hyperf实现了,数据的分析还是用的别人的。自己主要是实现了采集外,然后把数据丢入消息队列,通过websokcet实现转发广播

## Requirements
- php7.2
- swoole4.4.x
- hyperf
- jaeger/querylist

## Quick Start
1.安装好项目后,composer install
2.php bin/hyperf.php 查看命令
3.php bin/hyperf.php barrage:search 英雄联盟(或者其他搜索内容)
4.php bin/hyperf.php live:in 1111(房间号)
5.php bin/hyperf.php start 启动服务
6.使用websocket客户端连接websocketserver然后就可以欣赏弹幕了

## TODO
- 后续加入vue弹幕服务
........

## License

[MIT](https://github.com/inocturne/hyperf-danmu/blob/master/LICENSE)
