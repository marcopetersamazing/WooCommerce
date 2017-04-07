<?php

class MultiSafepay_Gateway_Nationaletuinbon extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_nationaletuinbon";
    }

    public static function getName()
    {
        return __('Nationale-tuinbon', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_nationaletuinbon_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "NATIONALETUINBON";
    }

    public function getType()
    {
        return "redirect";
    }
}