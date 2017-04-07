<?php

class MultiSafepay_Gateway_Visa extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_visa";
    }

    public static function getName()
    {
        return __('Visa', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_visa_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "VISA";
    }

    public function getType()
    {
        return "redirect";
    }
}