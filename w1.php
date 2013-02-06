<?php

require_once('api/Simpla.php');

class w1 extends Simpla
{	
	public function checkout_form($order_id, $button_text = null)
	{
		if(empty($button_text))
			$button_text = 'Перейти к оплате';
		
		$order = $this->orders->get_order((int)$order_id);
		$payment_method = $this->payment->get_payment_method($order->payment_method_id);
		$settings = $this->payment->get_payment_settings($payment_method->id);
		
		$price = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);
	
		$button =	'<form method="post" action="https://merchant.w1.ru/checkout/default.aspx" accept-charset="UTF-8">
					  <input type="hidden" name="WMI_MERCHANT_ID"    value="'.$settings['WMI_MERCHANT_ID'].'"/>
					  <input type="hidden" name="WMI_PAYMENT_AMOUNT" value="'.$price.'"/>
					  <input type="hidden" name="WMI_PAYMENT_NO"     value="'.$order->id.'"/>
					  <input type="hidden" name="WMI_CURRENCY_ID"    value="'.$settings['WMI_CURRENCY_ID'].'"/>
					  <input type="hidden" name="WMI_DESCRIPTION"    value="Оплата заказа №'.$order->id.' на сайте '.$this->config->root_url.'/"/>
					  <input type="hidden" name="WMI_SUCCESS_URL"    value="'.$this->config->root_url.'/order/'.$order->url.'"/>
					  <input type="hidden" name="WMI_FAIL_URL"       value="'.$this->config->root_url.'/order/'.$order->url.'"/>
					  <input type="submit" value="'.$button_text.'"/>
					</form>
					';
		return $button;
	}
}