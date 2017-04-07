<?php

class MultiSafepay_Gateway_Erotiekbon extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_erotiekbon";
    }

    public static function getName()
    {
        return __('Erotiekbon', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_erotiekbon_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "EROTIEKBON";
    }

    public function getType()
    {
        return "redirect";
    }
}