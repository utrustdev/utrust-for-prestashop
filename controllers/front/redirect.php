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
class UTRUSTRedirectModuleFrontController extends ModuleFrontController
{

    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        /*
         * Oops, an error occured.
         */
        if (Tools::getValue('action') == 'error') {
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {

            $api = UTRUST::$api_sandbox;
            if (Configuration::get('UTRUST_MODE_API')) {
                $api = UTRUST::$api_prod;
            }

            // Get Api Key
            $api_key = Configuration::get('UTRUST_API_KEY');

            $cart_id = Context::getContext()->cart->id;
            $secure_key = Context::getContext()->customer->secure_key;

            $cart = new Cart((int) $cart_id);
            $customer = new Customer((int) $cart->id_customer);

            /**
             * Since it's an example we are validating the order right here,
             * You should not do it this way in your own module.
             */
            $payment_status = Configuration::get('PS_OS_UTRUST'); // Default value for a payment that succeed.
            $message = null; // You can add a comment directly into the order so the merchant will see it in the BO.

            /**
             * Converting cart into a valid order
             */
            $module_name = $this->module->displayName;
            $currency_id = (int) Context::getContext()->currency->id;

            $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);

            $order_id = Order::getOrderByCartId((int) $cart->id);

            $order_data = $this->buildOrderData($order_id, $cart->id);

            $path = 'stores/orders';

            $curl = curl_init();

            $request_string = json_encode($order_data);
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api . $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $request_string,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($request_string), "Authorization: Bearer " . $api_key),
            ));

            $response_order = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $response_order_json = json_decode($response_order);

            if (isset($response_order_json->data->attributes->redirect_url) && isset($response_order_json->data->id)) {

                $redirect_url = $response_order_json->data->attributes->redirect_url;
                $uuid = $response_order_json->data->id;

            } else {
                return;
            }

            $datasql = array('id_order' => $order_id,
                'UUID' => $uuid);

            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'UTRUST WHERE id_order = ' . $order_id;

            if ($results = Db::getInstance()->ExecuteS($sql)) {

                if (!Db::getInstance()->update('UTRUST', $datasql, 'id_order = ' . $order_id)) {
                    return;
                }

            } else {

                if (!Db::getInstance()->insert('UTRUST', $datasql)) {
                    return;
                }

            }

            if ($order_id && ($secure_key == $customer->secure_key)) {
                return Tools::redirect($redirect_url);
            } else {

                $this->errors[] = $this->module->l('An error occured. Please contact the merchant to have more informations');

                return $this->setTemplate('error.tpl');
            }
        }
    }

    public function buildOrderData($order_id, $cart_id)
    {
        $link = new Link();
        $order = new Cart($cart_id);
        $line_items = array();
        $order_items = $order->getProducts(true);
        $shipping_total = null;
        $tax_total = null;
        $currency = new Currency($order->id_currency);

        // Line items
        foreach ($order_items as $product) {

            $line_item = array(
                'sku' => $product['id_product'] . '-' . $product['id_product_attribute'],
                'name' => $product['name'] . ' - ' . $product['attributes_small'],
                'price' => strval($product['price']),
                'currency' => $currency->iso_code,
                'quantity' => $product['quantity'],
            );
            $line_items[] = $line_item;
        }

        $reference = $order_id;

        // Order info
        $order_data = array(
            'reference' => $reference,
            'amount' => array(
                'total' => strval($order->getOrderTotal(true, 3)),
                'currency' => $currency->iso_code,
                'details' => array(
                    'subtotal' => strval($order->getOrderTotal(false)),
                    'tax' => strval($order->getOrderTotal(true, 4) - $order->getOrderTotal(false, 4)),
                    'shipping' => strval($order->getOrderShippingCost()),
                    'discount' => strval($order->getOrderTotal(true, 2)),
                ),
            ),
            'return_urls' => array(
                'return_url' => $this->context->link->getModuleLink('UTRUST', 'confirmation', array('order_id' => $order_id, 'cart_id' => $cart_id, 'secure_key' => Context::getContext()->customer->secure_key)),
                'cancel_url' => $this->context->link->getModuleLink('UTRUST', 'confirmation', array('order_id' => $order_id, 'cart_id' => $cart_id, 'secure_key' => Context::getContext()->customer->secure_key, 'cancel' => true)),
                'callback_url' => $this->context->link->getModuleLink('UTRUST', 'validation', array()),
            ),
            'line_items' => $line_items,
        );

        $address = new Address($this->context->cart->id_address_invoice);

        // Customer info
        $customer = array(
            'first_name' => $address->firstname,
            'last_name' => $address->lastname,
            'email' => $this->context->customer->email,
            'address1' => $address->address1,
            'address2' => $address->address2,
            'city' => $address->city,
            'state' => '',
            'postcode' => $address->postcode,
            'country' => Country::getIsoById($address->id_country),
        );

        $request = array(

            'data' => array(
                'type' => 'orders',
                'attributes' => array(
                    'order' => $order_data,
                    'customer' => $customer,
                ),
            ),
        );
        return $request;
    }

    protected function displayError($message, $description = false)
    {
        /*
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign('path', '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l('Payment') . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error'));

        /*
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }
}
