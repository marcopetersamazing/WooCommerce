<?php

class MultiSafepay_Gateway_Maestro extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_maestro";
    }

    public static function getName()
    {
        return __('Maestro', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_maestro_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "MAESTRO";
    }

    public function getType()
    {
        return "redirect";
    }
}