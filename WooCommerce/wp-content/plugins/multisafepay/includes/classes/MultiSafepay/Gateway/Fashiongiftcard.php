<?php

class MultiSafepay_Gateway_Fashiongiftcard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_fashiongiftcard";
    }

    public static function getName()
    {
        return __('Fashion-Giftcard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_fashiongiftcard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "FASHIONGIFTCARD";
    }

    public function getType()
    {
        return "redirect";
    }
}