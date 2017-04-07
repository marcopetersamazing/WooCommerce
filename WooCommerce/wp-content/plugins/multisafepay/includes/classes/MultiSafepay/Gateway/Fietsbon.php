<?php

class MultiSafepay_Gateway_Fietsbon extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_fietsbon";
    }

    public static function getName()
    {
        return __('Fietsbon', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_fietsbon_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "FIETSBON";
    }

    public function getType()
    {
        return "redirect";
    }
}