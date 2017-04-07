<?php

class MultiSafepay_Gateway_Eps extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_eps";
    }

    public static function getName()
    {
        return __('EPS', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_eps_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "EPS";
    }

    public function getType()
    {
        return "redirect";
    }
}