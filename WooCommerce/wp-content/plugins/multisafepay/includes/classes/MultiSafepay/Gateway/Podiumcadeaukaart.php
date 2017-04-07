<?php

class MultiSafepay_Gateway_Podiumcadeaukaart extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_podiumcadeaukaart";
    }

    public static function getName()
    {
        return __('Podium-Cadeaukaart', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_podiumcadeaukaart_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "PODIUM";
    }

    public function getType()
    {
        return "redirect";
    }
}