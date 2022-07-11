<?php

/**

 * UtrustPayments - A Sample Payment Module for PrestaShop 1.7

 *

 * Order Validation Controller

 *

 * @author Andresa Martins <contact@andresa.dev>

 * @license https://opensource.org/licenses/afl-3.0.php

 */



class UtrustpaymentsValidationModuleFrontController extends ModuleFrontController

{

    /**

     * This class should be use by your Instant Payment

     * Notification system to validate the order remotely

     */

    public function postProcess()

    {


        // ini_set('max_execution_time', '13000');
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

        // $request_body = "{\"event_type\":\"ORDER.PAYMENT.RECEIVED\",\"resource\":{\"reference\":\"62\"}, \"signature\": \"e464ee00e355c72eb3d196d9e1a1c36de68cd8bd0da68464c5862477603ec2b7\", \"state\": \"detected\"}";



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
                    $payment_status = null;
                    break;
            }

        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');
        }

        # STOP FUNCTION IF EVENT TYPE IS NOT KNOWN
        if ($payment_status == null) {
            echo 1;
            die();
            return;
        }

        $mail = false;
        $objOrder = new Order((int) $order_id);

        if ($payment_status == Configuration::get('PS_OS_PAYMENT')) {
            $mail = true;
            // $res = Db::getInstance()->getValue('
            //     SELECT transaction_id
            //     FROM `' . _DB_PREFIX_ . 'order_payment`
            //     WHERE order_reference = "' . $objOrder->reference . '"
            //     AND transaction_id != ""');
            // if (!$res) {
            // }

            // $objOrder->addOrderPayment($notification->resource->amount, null, $results[0]["UUID"]);
            // $orderPaymentDatas = OrderPayment::getByOrderId($order_id);
            // if(!empty($orderPaymentDatas)){
            //     $orderPayment = new OrderPayment($orderPaymentDatas[0]->id);
            //     $orderPayment->transaction_id = $results[0]["UUID"];
            //     $orderPayment->save();
            // }

        }

        $history = new OrderHistory();
        $history->id_order = (int) $objOrder->id;
        $history->changeIdOrderState($payment_status, (int) ($objOrder->id));
        $history->addWithemail($mail);
        echo $history->save();


        // sleep(20);
        // Db::getInstance()->getValue('
        //     DELETE
        //     FROM `' . _DB_PREFIX_ . 'order_payment`
        //     WHERE order_reference = "' . $objOrder->reference . '"
        //     AND transaction_id != ""');


        Db::getInstance()->getValue('
            UPDATE
            `' . _DB_PREFIX_ . 'order_payment`
            SET transaction_id = "' .$results[0]["UUID"] . '"
            WHERE order_reference = "'.$objOrder->reference.'"');


        // $orderPaymentDatas = OrderPayment::getByOrderId($order_id);
        // Db::getInstance()->getValue('
        //     UPDATE
        //      `' . _DB_PREFIX_ . 'order_invoice_payment`
        //     WHERE id_order_payment = "' . $orderPaymentDatas[0]->id . '"
        //      WHERE id_order="'.$objOrder->id).'"');
         // echo 1;
        die();

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