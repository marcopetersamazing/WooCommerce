<?php

class MultiSafepay_Gateway_Paysafecard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_paysafecard";
    }

    public static function getName()
    {
        return __('Paysafecard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_paysafecard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "PSAFECARD";
    }

    public function getType()
    {
        return "redirect";
    }
}