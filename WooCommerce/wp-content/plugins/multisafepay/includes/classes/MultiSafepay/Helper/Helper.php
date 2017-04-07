<?php

/**
 *  MultiSafepay Payment Module
 *
 *  @author    MultiSafepay <techsupport@MultiSafepay.com>
 *  @copyright Copyright (c) 2013 MultiSafepay (http://www.multisafepay.com)
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Helper_Helper {
    
    public static function write_log($log)
    {
        if (get_option('multisafepay_debugmode') == 'yes') {
            if (is_array($log) || is_object($log)) error_log(print_r($log, true));
            else error_log($log);
        }
    }
    
    public static function getApiKey()
    {
        return get_option('multisafepay_api_key');
    }

    public static function getTestMode()
    {
        return (get_option('multisafepay_testmode') == 'yes' ? true : false);
    }    
}


class CheckConnection
{

    public function testConnection ($api, $test_mode)
    {
        if ($api == '') {
            return;
        }
        // Test with current mode
        $msg = $this->tryToConnect($api, $test_mode);
        if ($msg == null) {
            return;
        }

        // Test with oposite mode
        $msg = $this->tryToConnect($api, !$test_mode);
        if ($msg == null ) {
            return ( ($test_mode ? __('This API-Key belongs to a LIVE-account', 'multisafepay') : __('This API-Key belongs to a TEST-account', 'multisafepay')));
        }

        return ( __('Unknown error. Probably the API-Key is not correct. Error-code: ', 'multisafepay') . $msg);
    }



    private function tryToConnect ($api, $test_mode){

        $test_order = array(
            "type"         => 'redirect',
            "order_id"     => 'Check Connection-'. time(),
            "currency"     => 'EUR',
            "amount"       => 1,
            "description"  => 'Check Connection-'. time()
        );

        $msp = new Client();
        $msp->setApiKey($api);
        $msp->setApiUrl($test_mode);

		$msg = null;

        try {
            $msp->orders->post($test_order);
            $url = $msp->orders->getPaymentLink();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        return ($msg);
    }
}
