<?php

class RedisMutexLock
{
    /**
     * @return \Redis
     */
    public static function getRedis()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        return $redis;
    }


    public static function lock($key, $timeout = 0, $lockSecond = 20, $sleep = 100000)
    {
        if (strlen($key) === 0) {
            return [
                'code' => 500,
                'msg' => '未设置缓存key'
            ];
        }
        $start = self::getMicroTime();
        $redis = self::getRedis();
        do {
            $acquired = $redis->set("Lock:{$key}", 1, ['NX', 'EX' => $lockSecond]);
            if ($acquired) {
                break;
            }
            if ($timeout === 0) {
                break;
            }
            usleep($sleep);
        } while (!is_numeric($timeout) || (self::getMicroTime()) < ($start + ($timeout * 1000000)));
        return $acquired ? true : false;
    }


    public static function release($key)
    {
        if (strlen($key) === 0) {
            return [
                'code' => 500,
                'msg' => '未设置缓存key'
            ];
        }
        $redis = self::getRedis();
        $redis->del("Lock:{$key}");
    }

    /**
     * @return string
     */
    protected static function getMicroTime()
    {
        return bcmul(microtime(true), 1000000);
    }
}