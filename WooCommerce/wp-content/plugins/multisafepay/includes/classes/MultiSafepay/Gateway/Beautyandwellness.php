<?php

class MultiSafepay_Gateway_Beautyandwellness extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_beautyandwellness";
    }

    public static function getName()
    {
        return __('Beauty and wellness', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_beautyandwellness_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return "BEAUTYANDWELLNESS";
    }

    public function getType()
    {
        return "redirect";
    }
}