<?php

class MultiSafepay_Gateway_Vvvbon extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_vvvbon";
    }

    public static function getName()
    {
        return __('VVV-Bon', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_vvvbon_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "VVVBON";
    }

    public function getType()
    {
        return "redirect";
    }
}