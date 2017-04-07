<?php

class MultiSafepay_Gateway_Wellnessgiftcard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_wellnessgiftcard";
    }

    public static function getName()
    {
        return __('Wellness-giftcard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_wellnessgiftcard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "WELLNESS-GIFTCARD";
    }

    public function getType()
    {
        return "redirect";
    }
}