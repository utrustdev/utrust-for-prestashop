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
class UTRUSTValidationModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {

        /*
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die;
        }

        if (('POST' !== $_SERVER['REQUEST_METHOD'])) {
            return;
        }

        $request_body = file_get_contents('php://input');

        // $request_body = "{\"event_type\":\"ORDER.PAYMENT.RECEIVED\",\"resource\":{\"reference\":\"25\"}}";

        $notification = json_decode($request_body);
        /* error_log("---");
        ob_start();                    // start buffer capture
        var_dump($notification);     // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log($contents); */
        if (!isset($notification->resource->reference)) {
            return;
        }

        $order_id = $notification->resource->reference;

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'UTRUST WHERE id_order = ' . $order_id;

        $results = Db::getInstance()->executeS($sql);
        if (!$results) {
            return true;
        }

        if ($order_id == null || !$order_id) {
            return true;
        }

        if ($this->isValidOrder($request_body) === true) {
            switch ($notification->event_type) {
                case 'ORDER.PAYMENT.RECEIVED':
                    $payment_status = Configuration::get('PS_OS_PAYMENT');
                    break;
                case 'ORDER.PAYMENT.CANCELLED':
                    $payment_status = Configuration::get('PS_OS_CANCELED');
                    break;
                default:
                    $payment_status = Configuration::get('PS_OS_ERROR');
                    break;
            }

        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');
        }

        $mail = false;
        $objOrder = new Order((int) $order_id);

        if ($payment_status == Configuration::get('PS_OS_PAYMENT')) {
            $mail = true;
            $objOrder->addOrderPayment($notification->resource->amount, null, $results[0]["UUID"]);
        }

        $history = new OrderHistory();
        $history->id_order = (int) $objOrder->id;
        $history->changeIdOrderState($payment_status, (int) ($objOrder->id));
        $history->addWithemail($mail);

        return $history->save();
    }

    protected function isValidOrder($request_body)
    {
        $notification = json_decode($request_body);
        // get secret from Utrust settings
        $webhook_secret = Configuration::get('UTRUST_WEBHOOK_SECRET');

        // get signature from response
        $signature_from_response = $notification->signature;

        // removes signature from response
        unset($notification->signature);

        // concat keys and values into one string
        $concated_payload = array();
        foreach ($notification as $key => $value) {
            if (is_object($value)) {
                foreach ($value as $k => $v) {
                    $concated_payload[] = $key;
                    $concated_payload[] = $k . $v;
                }
            } else {
                $concated_payload[] = $key . $value;
            }
        }
        $concated_payload = join('', $concated_payload);

        // sign string with HMAC SHA256
        $signed_payload = hash_hmac('sha256', $concated_payload, $webhook_secret);

        // check if signature is correct
        if ($signature_from_response === $signed_payload) {
            return true;
        }
        return false;
    }
}
