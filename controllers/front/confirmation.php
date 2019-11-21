<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class UTRUSTConfirmationModuleFrontController extends ModuleFrontController
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

                Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key.'&cancel=1');
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
