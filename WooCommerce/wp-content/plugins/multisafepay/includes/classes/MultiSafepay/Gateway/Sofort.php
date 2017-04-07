<?php

class MultiSafepay_Gateway_Sofort extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_sofort";
    }

    public static function getName()
    {
        return __('Sofort', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_sofort_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "DIRECTBANK";
    }

    public function getType()
    {
        return "redirect";
    }
}