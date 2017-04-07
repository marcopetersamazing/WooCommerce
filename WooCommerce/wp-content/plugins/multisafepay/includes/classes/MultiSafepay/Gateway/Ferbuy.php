<?php

class MultiSafepay_Gateway_Ferbuy extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_ferbuy";
    }

    public static function getName()
    {
        return __('FerBuy', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_ferbuy_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "FERBUY";
    }

    public function getType()
    {
        return "redirect";
    }
}