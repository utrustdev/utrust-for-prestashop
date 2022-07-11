<?php
class UtrustpaymentsConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
     /* 

        $request_body = file_get_contents( 'php://input' );
        $notification = json_decode($request_body);
        error_log("---");
        ob_start();                    // start buffer capture
        var_dump($notification);     // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log($contents); 
        
     */

        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false) || (Tools::isSubmit('order_id') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $order_id = Tools::getValue('order_id');
        $secure_key = Tools::getValue('secure_key');

        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);

       

        if ($secure_key == $customer->secure_key) {
            /**
             * The order has been placed so we redirect the customer on the confirmation page.
             */
            $module_id = $this->module->id;
            if (Tools::isSubmit('cancel') == true && Tools::getValue('cancel') == true) {

                $redirectLink = $this->context->link->getModuleLink('utrustpayments', 'cancelOrder', array('order_id' => $order_id, 'cart_id' => $cart_id, 'secure_key' => Context::getContext()->customer->secure_key));
                // Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key.'&cancel=1');
                Tools::redirect($redirectLink);
            } else {
                
                Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
            }
        } else {
            /*
             * An error occured and is shown on a new page.
             */
            $this->errors[] = $this->module->l('An error occured. Please contact the merchant to have more informations');

            return $this->setTemplate('error.tpl');
        }
    }
}
