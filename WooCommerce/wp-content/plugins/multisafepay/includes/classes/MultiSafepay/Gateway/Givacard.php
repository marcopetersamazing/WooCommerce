<?php

class MultiSafepay_Gateway_Givacard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_givacard";
    }

    public static function getName()
    {
        return __('Givacard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_givacard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "GIVACARD";
    }

    public function getType()
    {
        return "redirect";
    }
}