<?php
require_once 'Order.php';

$order = new Order();

/*$res1 = $order->buy('user1', 'g1', 11);
print_r("用户user1第一次购买结果：$res1\n");
$res2 = $order->buy('user1', 'g1', 9);
print_r("用户user1第二次购买结果：$res2\n");
$res3 = $order->buy('user2', 'g1', 10);
print_r("用户user2购买结果：$res3");*/


//for ($i = 1; $i < 2; $i++) {
    $num = 1;
    $i = mt_rand(1, 99);
    $user = "user$i";
    $goods_no = 1;
    $goods = "g$goods_no";
    $res = $order->buy($user, $goods, $num);
//}
echo $res;

