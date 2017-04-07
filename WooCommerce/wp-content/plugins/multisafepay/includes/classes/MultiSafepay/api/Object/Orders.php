<?php
/**
 *  MultiSafepay Payment Module
 *
 *  @author    MultiSafepay <techsupport@MultiSafepay.com>
 *  @copyright Copyright (c) 2013 MultiSafepay (http://www.multisafepay.com)
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class ObjectOrders extends ObjectCore
{

    public $success;
    public $data;

    public function patch($body, $endpoint = '')
    {
		$result = parent::post(json_encode($body), $endpoint);
        $this->success = $result->success;
        $this->data = $result->data;
        return $result;
    }

    public function get($id, $type = 'orders', $body = array(), $query_string = false)
    {
        $result = parent::get($type, $id, $body, $query_string);
        $this->success = $result->success;
        $this->data = $result->data;
        return $this->data;
    }

    public function post($body, $endpoint = 'orders')
    {
		$result = parent::post(json_encode($body), $endpoint);
        $this->success = $result->success;
        $this->data = $result->data;

        return $this->data;
    }

    public function getPaymentLink()
    {
        return $this->data->payment_url;
    }
}
