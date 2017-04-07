<?php

class MultiSafepay_Gateway_Creditcard extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return "multisafepay_creditcard";
    }

    public static function getName()
    {
        return __('Creditcard', 'multisafepay');
    }

    public static function getSettings()
    {
        return get_option('woocommerce_multisafepay_creditcard_settings');
    }

    public static function getTitle()
    {
        $settings =  self::getSettings();
        return ($settings['title']);
    }

    public static function getGatewayCode()
    {
        return ( empty($_POST['cc_issuer']) ? "CREDITCARDS" : $_POST['cc_issuer']);
    }

    public function getType()
    {
        return "redirect";
    }

    public function payment_fields()
    {
        $description = '';

        $description_text = $this->get_option('description');
        if (!empty($description_text))
            $description .= '<p>'.$description_text.'</p>';

        $msp = new Client();

        $msp->setApiKey($this->getApiKey());
        $msp->setApiUrl($this->getTestMode());

        try {
            $msg = null;
            $gateways = $msp->gateways->get();
        } catch (Exception $e) {
            $msg = htmlspecialchars($e->getMessage());
            $this->write_log($msg);
            wc_add_notice($msg, 'error');
        }

        $description .= __('Select CreditCard', 'multisafepay').'<br/>';
        $description .= '<select id="cc_issuer" name="cc_issuer" class="required-entry">';

        foreach ($gateways as $gateway) {
            switch ($gateway->id) {
                case 'VISA':
                case 'AMEX':
                case 'MAESTRO':
                case 'MASTERCARD':
                    $description .= '<option value="'.$gateway->id.'">'.$gateway->description.'</option>';
            }
        }

        $description .= '</select>';
        $description .= '</p>';

        echo $description;
    }

    public function validate_fields()
    {
        if (empty($_POST['cc_issuer'])) {
            wc_add_notice(__('Error: ', 'multisafepay').' '.__('Please select a CreditCard.', 'multisafepay'));
            return false;
        }
        return true;
    }
}