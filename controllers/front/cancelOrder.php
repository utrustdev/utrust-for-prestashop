<?php
class UtrustpaymentsCancelOrderModuleFrontController extends ModuleFrontController
{
	public function postProcess()
    {
    	parent::initContent();

    	if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false) || (Tools::isSubmit('order_id') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $order_id = Tools::getValue('order_id');
        $secure_key = Tools::getValue('secure_key');

        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);

        $order = new Order((int) $order_id);  
        $params = array(
        	'order' => $order,
        	'cart_id' => $cart_id,
        	'secure_key' => $secure_key,
        	'product' => $order->getProducts(),
        );   
        
        $currency = new Currency($order->id_currency);

        // echo "<pre>";
        // print_r($cart);
        // print_r($params['order']->subtotals);
        // print_r($params['order']->labels);



        $this->context->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'total' => Tools::displayPrice($order->getOrdersTotalPaid(), $currency, false),
            'params' => $params,
            'order' => (array) $order,
            'products' => (array) $order->getProducts(),
        ));

    	return $this->setTemplate('module:utrustpayments/views/templates/hook/cancel-confirmation.tpl');
    }
}