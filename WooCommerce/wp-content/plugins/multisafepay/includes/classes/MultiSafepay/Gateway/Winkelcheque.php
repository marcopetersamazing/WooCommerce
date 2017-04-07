<?php

class MultiSafepay_Gateway_Winkelcheque extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_winkelcheque";
    }

    public static function getName()
    {
        return __('Winkelcheque', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_winkelcheque_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "WINKELCHEQUE";
    }

    public function getType()
    {
        return "redirect";
    }
}