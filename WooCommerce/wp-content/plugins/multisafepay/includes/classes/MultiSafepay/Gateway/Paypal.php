<?php

class MultiSafepay_Gateway_Paypal extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_paypal";
    }

    public static function getName()
    {
        return __('PayPal', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_paypal_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "PAYPAL";
    }

    public function getType()
    {
        return "redirect";
    }
}