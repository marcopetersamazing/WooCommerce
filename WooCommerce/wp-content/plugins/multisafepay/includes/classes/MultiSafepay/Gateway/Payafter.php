<?php

class MultiSafepay_Gateway_Payafter extends MultiSafepay_Gateway_Abstract
{

//    public function __construct()
//    {
//        add_filter('woocommerce_available_payment_gateways', array ('MultiSafepay_Gateway_Payafter', 'payafter_filter_gateways'));
//    }

    
	public static function getCode()
    {
        return "multisafepay_payafter";
    }

    public static function getName()
    {
        return __('PayAfter', 'multisafepay');
    }
		
	public static function getGatewayCode()
    {
        return "PAYAFTER";
    }

    public function getType()
    {
        $settings = get_option('woocommerce_multisafepay_payafter_settings');

        if ($settings['direct'] == 'yes')
            return "direct";
        else
            return "redirect";
    }
	
	public function init_settings($form_fields = array())
    {
		$this->form_fields = array();
		
		$warning = $this->getWarning();
		
		if(is_array($warning))
			$this->form_fields['warning'] = $warning;

		$this->form_fields['direct'] = array(
				'title'         => __('Direct', 'multisafepay'),
				'type'          => 'checkbox',
				'label'         => sprintf(__('Direct %s', 'multisafepay'), $this->getName()),
				'default'       => 'no');

		$this->form_fields['minamount'] = array(
                'title'         => __('Minimal order amount', 'multisafepay'),
                'type'          => 'text',
                'description'   => __('The minimal amount in euro\'s for an order to show Pay After Delivery', 'multisafepay'),
                'css'           => 'width: 100px;');

		$this->form_fields['maxamount'] = array(
                'title'         => __('Maximal order amount', 'multisafepay'),
                'type'          => 'text',
                'description'   => __('The max order amount in euro\'s for an order to show Pay After Delivery', 'multisafepay'),
                'css'           => 'width: 100px;');
			
        parent::init_settings($this->form_fields);
    }

	public function payment_fields()
    {

        $description = '';
        $description = '<p class="form-row form-row-wide  validate-required"><label for="birthday" class="">' . __('Geboortedatum', 'multisafepay') . '<abbr class="required" title="required">*</abbr></label><input type="text" class="input-text" name="PAYAFTER_birthday" id="birthday" placeholder="dd-mm-yyyy"/>
        </p><div class="clear"></div>';

        $description .= '<p class="form-row form-row-wide  validate-required"><label for="account" class="">' . __('Rekeningnummer', 'multisafepay') . '<abbr class="required" title="required">*</abbr></label><input type="text" class="input-text" name="PAYAFTER_account" id="account" placeholder=""/>
        </p><div class="clear"></div>';

        $description .= '<p class="form-row form-row-wide">' . __('By confirming this order you agree with the ', 'multisafepay') . '<a href="http://www.multifactor.nl/consument-betalingsvoorwaarden-2/" target="_blank">Terms and conditions of MultiFactor</a>';

		$description_text = $this->get_option('description');
		if(!empty($description_text))
			$description .= '<p>' . $description_text . '</p>';

        echo $description;
				
    }

    public function validate_fields() {
        return true;
    }

    public function payafter_filter_gateways($gateways) {

        unset($gateways['multisafepay_payafter']);
        global $woocommerce;

        $settings = (array) get_option("woocommerce_multisafepay_payafter_settings");

        if(!empty($settings['minamount'])){
            if ($woocommerce->cart->total > $settings['maxamount'] || $woocommerce->cart->total < $settings['minamount']) {
                unset($gateways['multisafepay_payafter']);
            }
        }

        if ($woocommerce->customer->get_country() != 'NL') {
            unset($gateways['multisafepay_payafter']);
        }

        return $gateways;
    }
            
    public function process_payment($order_id)
    {
        $this->type         = $this->getType();
        $this->GatewayInfo  = $this->getGatewayInfo($order_id);
        list ($this->shopping_cart, $this->checkout_options) = $this->getCart($order_id);

        return parent::process_payment($order_id);
    }
  
    
}

