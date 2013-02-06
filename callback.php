<?php

// Работаем в корневой директории
chdir ('../../');
require_once('api/Simpla.php');
$simpla = new Simpla();



$order_id      = intval($_POST['WMI_PAYMENT_NO']);
$amount        = $_POST['WMI_PAYMENT_AMOUNT']; 
$status        = $_POST['WMI_ORDER_STATE']; 

if($status != 'Accepted')
	exit();

////////////////////////////////////////////////
// Выберем заказ из базы
////////////////////////////////////////////////
$order = $simpla->orders->get_order(intval($order_id));
if(empty($order))
	die('Оплачиваемый заказ не найден');
 
////////////////////////////////////////////////
// Выбираем из базы соответствующий метод оплаты
////////////////////////////////////////////////
$method = $simpla->payment->get_payment_method(intval($order->payment_method_id));
if(empty($method))
	die("WMI_RESULT=RETRY&WMI_DESCRIPTION=Неизвестный метод оплаты");
	
$settings = unserialize($method->settings);
$payment_currency = $simpla->money->get_currency(intval($method->currency_id));

// Нельзя оплатить уже оплаченный заказ  
if($order->paid)
	die('WMI_RESULT=RETRY&WMI_DESCRIPTION=Этот заказ уже оплачен');

if($amount != round($simpla->money->convert($order->total_price, $method->currency_id, false), 2) || $amount<=0)
	die("WMI_RESULT=RETRY&WMI_DESCRIPTION=incorrect price");
	
   
// Установим статус оплачен
$simpla->orders->update_order(intval($order->id), array('paid'=>1));

// Отправим уведомление на email
$simpla->notify->email_order_user(intval($order->id));
$simpla->notify->email_order_admin(intval($order->id));

// Спишем товары  
$simpla->orders->close(intval($order->id));

echo "WMI_RESULT=OK";
header('Location: '.$simpla->request->root_url.'/order/'.$order->url);

exit();