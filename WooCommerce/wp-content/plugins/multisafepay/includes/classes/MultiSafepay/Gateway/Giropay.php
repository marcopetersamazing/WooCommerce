<?php

class MultiSafepay_Gateway_Giropay extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_giropay";
    }

    public static function getName()
    {
        return __('GiroPay', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_giropay_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "GIROPAY";
    }

    public function getType()
    {
        return "redirect";
    }
}