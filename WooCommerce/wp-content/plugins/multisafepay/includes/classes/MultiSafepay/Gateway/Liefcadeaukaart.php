<?php

class MultiSafepay_Gateway_Liefcadeaukaart extends MultiSafepay_Gateway_Abstract
{
	public static function getCode()
    {
        return "multisafepay_liefcadeaukaart";
    }

    public static function getName()
    {
        return __('Lief-cadeaukaart', 'multisafepay');
    }

	public static function getGatewayCode()
    {
        return "LIEF";
    }

    public function getType()
    {
        return "redirect";
    }

}