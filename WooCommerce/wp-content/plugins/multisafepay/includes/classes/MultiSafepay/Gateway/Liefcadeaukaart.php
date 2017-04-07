<?php

class MultiSafepay_Gateway_Liefcadeaukaart extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_liefcadeaukaart";
    }

    public static function getName()
    {
        return __('Lief-cadeaukaart', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_liefcadeaukaart_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "LIEF";
    }

    public function getType()
    {
        return "redirect";
    }
}