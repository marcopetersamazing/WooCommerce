<?php
/**
 *  MultiSafepay Payment Module
 *
 *  @author    MultiSafepay <techsupport@MultiSafepay.com>
 *  @copyright Copyright (c) 2013 MultiSafepay (http://www.multisafepay.com)
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class ObjectIssuers extends ObjectCore
{
    public $success;
    public $data;

    public function get($endpoint = 'issuers', $type = 'ideal', $body = array(), $query_string = false)
    {
        $result = parent::get($endpoint, $type, $body, $query_string);
        $this->success = $result->success;
        $this->data = $result->data;

        return $this->data;
    }
}
