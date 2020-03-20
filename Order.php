<?php
require 'RedisMutexLock.php';
require_once 'Mysql.php';

class Order
{
    /**
     * @var \Redis
     */
    private $redis;

    public function __construct()
    {
        $this->redis = RedisMutexLock::getRedis();
    }

    public function setGoods($name, $number)
    {
        $mysql = new Mysql();
        $sql = "select * from `goods` where `name` = '$name' limit 1";
        $res = $mysql->run_select($sql);
        if ($res) {
            $sql2 = "UPDATE `goods` SET `num`={$number} WHERE `name`= '{$name}'";
            $res2 = $mysql->run_edit($sql2);
        } else {
            $sql2 = "INSERT INTO `goods` (`name` ,`num`) VALUES ('{$name}', {$number})";
            $res2 = $mysql->run_edit($sql2);
        }
        if ($res2) {
            $this->redis->set($name, $number);
        }
        return $res2;
    }

    public function getGoods($name)
    {
        $mysql = new Mysql();
        $sql = "select * from `goods` where `name` = '$name' limit 1";
        $res = $mysql->run_select($sql);
        if ($res) {
            return $res[0]['num'];
        }
        return false;
    }

    public function server()
    {
        $mysql = new Mysql();
        while (true) {
            $shoppingInfo = $this->redis->rpop('users');
            if (!$shoppingInfo) {
                continue;
            }
            $shoppingInfo = explode(',', $shoppingInfo);
            print_r("用户 {$shoppingInfo[0]} 开始购买\n");
            $num = $this->redis->get($shoppingInfo[1]);
            if ($num < $shoppingInfo[2]) {
                print_r("用户 {$shoppingInfo[0]}-{$shoppingInfo[1]}库存不足{$num}-{$shoppingInfo[2]}\n");
                $this->redis->set('user_' . $shoppingInfo[0] . '_' . $shoppingInfo[3], '库存不足');
                continue;
            }
            print_r("用户 {$shoppingInfo[0]} 购买成功\n");
            $rest = $num - $shoppingInfo[2];
            $sql2 = "UPDATE `goods` SET `num` = {$rest} WHERE `name`= '{$shoppingInfo[1]}'";
            $res2 = $mysql->run_edit($sql2);
            if ($res2) {
                $sql3 = "INSERT INTO `order` (`goods` ,`num`, `user`) VALUES ('{$shoppingInfo[1]}', {$shoppingInfo[2]}, '{$shoppingInfo[0]}')";
                $res3 = $mysql->run_edit($sql3);
                print_r("商品 {$shoppingInfo[1]} 剩余库存 {$rest}\n");
                $this->redis->set($shoppingInfo[1], $rest);
                $this->redis->set('user_' . $shoppingInfo[0] . '_' . $shoppingInfo[3], '抢购成功');
            } else {
                print_r("用户 {$shoppingInfo[0]}-{$shoppingInfo[1]}库存不足{$num}-{$shoppingInfo[2]}\n");
                $this->redis->set('user_' . $shoppingInfo[0] . '_' . $shoppingInfo[3], '库存不足');
            }
        }
    }
    
    public function buy($people, $goods, $number)
    {
        $time = time();
        if (!$this->redis->get($goods)) {
            return '商品已售空或者不存在';
        }
        $this->redis->lpush('users', $people. ',' . $goods . ',' . $number . ',' . $time);
        $this->redis->setex('user_' . $people . '_' . $time, 3600, '开始');
        $resOrder = '';
        print_r("下单成功");
        while (true) {
            //sleep(2);
            $res = $this->redis->get('user_' . $people . '_' . $time);
            print_r("用户{$people}购买结果：{$res}\n");
            if ($res == '开始') {
                continue;
            } else {
                $resOrder = $res;
            }
            if (!$resOrder && $this->redis->llen('users') == 0) {
                $resOrder = '网络超时';
            }
            if ($resOrder) {
                break;
            }
        }
        return $resOrder;
    }
}