<?php

class MultiSafepay_Gateway_GoodCard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_goodcard";
    }

    public static function getName()
    {
        return __('GoodCard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_goodcard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "GOODCARD";
    }

    public function getType()
    {
        return "redirect";
    }
}