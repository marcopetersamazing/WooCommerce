<?php

class MultiSafepay_Gateway_Nationalebioscoopbon extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_nationalebioscoopbon";
    }

    public static function getName()
    {
        return __('Nationale Bioscoopbon', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_nationalebioscoopbon_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "NATIONALEBIOSCOOPBON";
    }

    public function getType()
    {
        return "redirect";
    }
}