<?php

class MultiSafepay_Gateway_Sportenfit extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_sportenfit";
    }

    public static function getName()
    {
        return __('Sport en Fit', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_sportenfit_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "SPORTENFIT";
    }

    public function getType()
    {
        return "redirect";
    }
}