<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Components\Http;

use App\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Coroutine;
use Swlib\Http\Uri;
use Swlib\Saber\Request;
use Swlib\Saber\Response;
use Swlib\SaberGM;
use Swoole\Coroutine\Channel;
use Throwable;

class Client
{
    public const JSON_BODY = 'json';

    public const FORM_BODY = 'form';

    public const STRING_BODY = 'string';

    /**
     * Saber Get.
     *
     * @param       $url
     * @param array $headers
     * @param array $queryParams
     *
     * @param int   $timeOut
     *
     * @return array
     */
    public static function get($url, $headers = [], $queryParams = [], $timeOut = 5)
    {
        return static::multiGet($url, $headers, $queryParams, $timeOut);
    }

    /**
     * Saber Post.
     *
     * @param        $url
     * @param array  $headers
     * @param array  $queryParams
     * @param null   $body
     * @param string $bodyType
     *
     * @return array
     */
    public static function post($url, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiPost($url, $headers, $queryParams, $body, $bodyType);
    }

    /**
     * Saber Put.
     *
     * @param        $url
     * @param array  $headers
     * @param array  $queryParams
     * @param null   $body
     * @param string $bodyType
     *
     * @return array
     */
    public static function put($url, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiPut($url, $headers, $queryParams, $body, $bodyType);
    }

    /**
     * Saber Delete.
     *
     * @param        $url
     * @param array  $headers
     * @param array  $queryParams
     * @param null   $body
     * @param string $bodyType
     *
     * @return array
     */
    public static function delete($url, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiDelete($url, $headers, $queryParams, $body, $bodyType);
    }

    /**
     * Saber download.
     * @see https://github.com/swlib/saber#%E8%B6%85%E5%A4%A7%E6%96%87%E4%BB%B6%E4%B8%8B%E8%BD%BD
     *
     * @param string $url
     * @param string $savePath
     * @param int    $offset
     * @param array  $options
     *
     * @return Request|Response
     */
    public static function download(string $url, string $savePath, int $offset = 0, array $options = [])
    {
        return SaberGM::download($url, $savePath, $offset, $options);
    }

    public static function multiGet($urls, $headers = [], $queryParams = [], $timeOut = 5)
    {
        return static::multiRequest($urls, 'GET', $headers, $queryParams, null, self::JSON_BODY, $timeOut);
    }

    public static function multiPost($urls, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY, $timeOut = 5)
    {
        return static::multiRequest($urls, 'POST', $headers, $queryParams, $body, $bodyType, $timeOut);
    }

    public static function multiPut($urls, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY, $timeOut = 5)
    {
        return static::multiRequest($urls, 'PUT', $headers, $queryParams, $body, $bodyType, $timeOut);
    }

    public static function multiDelete($urls, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY, $timeOut = 5)
    {
        return static::multiRequest($urls, 'DELETE', $headers, $queryParams, $body, $bodyType, $timeOut);
    }

    /**
     * @param        $urls
     * @param        $method
     * @param array  $headers
     * @param array  $queryParams
     * @param null   $body
     * @param string $bodyType
     * @param int    $timeOut
     *
     * @return array
     */
    public static function multiRequest($urls, $method, $headers = [], $queryParams = [], $body = null, $bodyType = self::JSON_BODY, $timeOut = 5)
    {
        if (!is_array($urls)) {
            $urls = (array)$urls;
        }
        $requestCount = count($urls);

        $chan = new Channel($requestCount);

        $aggResult  = [];
        $exceptions = [];
        foreach ($urls as $id => $url) {
            Coroutine::create(
                function () use (&$aggResult, $id, $url, $chan, $method, $headers, $queryParams, $body, $bodyType, $timeOut, &$exceptions)
                {
                    $request = SaberGM::psr()->withMethod($method)
                                      ->withUri(new Uri($url));

                    if (!is_null($body)) {
                        switch ($bodyType) {
                            case self::JSON_BODY:
                                $headers['Content-Type'] = 'application/json';
                                $body                    = json_encode($body);
                                break;
                            case self::FORM_BODY:
                                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                                $body                    = http_build_query($body);
                                break;
                            case self::STRING_BODY:
                                $body = (string)$body;
                                break;
                        }
                        $request->withBody(new SwooleStream($body));
                    }
                    if (is_array($queryParams) && count($queryParams) > 0) {
                        foreach ($queryParams as $qKey => $qValue) {
                            $request->withQueryParam($qKey, $qValue);
                        }
                    }

                    foreach ($headers as $name => $value) {
                        $request->withAddedHeader($name, $value);
                    }
                    $response = null;
                    try {
                        $aggResult[$id] = $response = $request->withTimeout($timeOut)->exec()->recv();
                    } catch (Throwable $throwable) {
                        array_push($exceptions, $throwable);
                    }
                    $chan->push(1);
                }
            );
        }

        for ($i = 0; $i < $requestCount; ++$i) {
            $chan->pop();
        }
        $chan->close();
        if (count($exceptions) > 0) {
            $messages = [];
            foreach ($exceptions as $exception) {
                array_push($messages, $exception->getMessage());
            }
            throw new \RuntimeException(implode(',', $messages), 401);
        }
        return $aggResult;
    }
}
