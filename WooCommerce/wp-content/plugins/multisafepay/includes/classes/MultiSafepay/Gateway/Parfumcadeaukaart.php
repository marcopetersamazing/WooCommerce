<?php

class MultiSafepay_Gateway_Parfumcadeaukaart extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_parfumcadeaukaart";
    }

    public static function getName()
    {
        return __('Parfum-cadeaukaart', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_parfumcadeaukaart_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "PARFUMCADEAUKAART";
    }

    public function getType()
    {
        return "redirect";
    }
}