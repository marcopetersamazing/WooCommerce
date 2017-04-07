<?php
class MultiSafepay_Gateway_Amex extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_amex";
    }

    public static function getName()
    {
        return __('Amex', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_amex_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "AMEX";
    }

    public function getType()
    {
        return "redirect";
    }
}