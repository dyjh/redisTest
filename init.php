<?php
require_once 'Order.php';

$order = new Order();
$order->setGoods('g1', 2);
print_r('商品 g1 初始库存 ' . $order->getGoods('g1') . "\n");
//$order->setGoods('g2', 100);
//print_r('商品 g2 初始库存 ' . $order->getGoods('g2') . "\n");
//$order->setGoods('g3', 100);
//print_r('商品 g3 初始库存 ' . $order->getGoods('g3') . "\n");
//print_r("初始化就绪\n");



