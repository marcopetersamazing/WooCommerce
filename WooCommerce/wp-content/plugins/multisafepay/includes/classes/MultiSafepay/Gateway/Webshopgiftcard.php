<?php

class MultiSafepay_Gateway_Webshopgiftcard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_webshopgiftcard";
    }

    public static function getName()
    {
        return __('Webshop-giftcard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_webshopgiftcard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "WEBSHOPGIFTCARD";
    }

    public function getType()
    {
        return "redirect";
    }
}