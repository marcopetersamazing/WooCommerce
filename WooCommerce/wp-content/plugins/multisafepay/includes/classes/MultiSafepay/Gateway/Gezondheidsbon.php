<?php

class MultiSafepay_Gateway_Gezondheidsbon extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_gezondheidsbon";
    }

    public static function getName()
    {
        return __('Gezondheidsbon', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_gezondheidsbon_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "GEZONDHEIDSBON";
    }

    public function getType()
    {
        return "redirect";
    }
}