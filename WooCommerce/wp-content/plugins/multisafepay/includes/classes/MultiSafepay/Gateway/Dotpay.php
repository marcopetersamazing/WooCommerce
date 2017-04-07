<?php

class MultiSafepay_Gateway_Dotpay extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_dotpay";
    }

    public static function getName()
    {
        return __('Dotpay', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_dotpay_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "DOTPAY";
    }

    public function getType()
    {
        return "redirect";
    }
}