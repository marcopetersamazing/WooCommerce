<?php

class Multisafepay_Gateway_Abstract extends WC_Payment_Gateway
{

    public static function getVersion()
    {
        return get_option('multisafepay_version');
    }

    public static function getCode()
    {
        throw new Exception('Please implement the getCode method');
    }

    public static function getName()
    {
        throw new Exception('Please implement the getName method');
    }

    public static function getApiKey()
    {
        return get_option('multisafepay_api_key');
    }

    public static function getTestMode()
    {
        return (get_option('multisafepay_testmode') == 'yes' ? true : false);
    }

    public static function getEnabled()
    {
        return get_option('multisafepay_enabled');
    }

    public static function getTitle()
    {
        return get_option('multisafepay_gateway_title');
    }

    public static function getNurl()
    {
        return get_option('multisafepay_nurl');
    }

    public static function getShowImages()
    {
        return (get_option('multisafepay_show_images') == 'yes' ? true : false);
    }

    public static function getDescription()
    {
        return get_option('multisafepay_gateway_title');
    }

    public static function getTimeActive()
    {
        switch (get_option('multisafepay_time_unit')) {
            case 'days':
                $time_active = (get_option('multisafepay_time_active') * 24 * 60 * 60);
                break;
            case 'hours':
                $time_active = (get_option('multisafepay_time_active') * 60 * 60);
                break;
            case 'seconds':
                $time_active = (get_option('multisafepay_time_active'));
            default:
                $time_active = (30 * 24 * 60 * 60); // 30 days
                break;
        }
        return ($time_active);
    }

    public static function getSendInvoice()
    {
        return get_option('multisafepay_send_invoice');
    }

    public static function getDebugMode()
    {
        return (get_option('multisafepay_debugmode') == 'yes' ? true : false);
    }

    public static function getWarning()
    {
        return null;
    }

    public static function canRefund()
    {
        return true;
    }

    public function getIcon()
    {
//        $button_locale_code = get_locale();
//        $image              = plugins_url('/Images/'.$button_locale_code.'/'.$this->getCode().'.png', dirname(__FILE__));
          $image              = plugins_url('/Images/'.$this->getCode().'.png', dirname(__FILE__));


        return ($image);
    }

    public function __construct()
    {
        $this->id                   = $this->getCode();
        $this->has_fields           = true;
        $this->method_title         = $this->getName();
        $this->method_description   = sprintf(__('Activate this module to accept %s transactions by MultiSafepay', 'multisafepay'), $this->getName());

        if ($this->canRefund())
            $this->supports = array('products', 'refunds');
        else
            $this->supports = array('products');

        $this->init_settings();

        $this->title = $this->get_option('title');

        if ($this->getShowImages()) {
            $this->icon = $this->getIcon();
        }

        add_filter('woocommerce_available_payment_gateways', array ('MultiSafepay_Gateway_Payafter', 'payafter_filter_gateways'));
        add_filter('woocommerce_available_payment_gateways', array ('MultiSafepay_Gateway_Klarna', 'klarna_filter_gateways'));
        add_filter('woocommerce_available_payment_gateways', array ('MultiSafepay_Gateway_Einvoice', 'einvoice_filter_gateways'));

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_order_status_completed', array($this, 'setToShipped'), 13);
    }

    public function init_settings($form_fields = array())
    {
        $this->form_fields = array();

        $warning = $this->getWarning();

        if (is_array($warning))
            $this->form_fields['warning'] = $warning;


        $this->form_fields['enabled'] = array(
            'title'         => __('Enable', 'woocommerce'),
            'type'          => 'checkbox',
            'label'         => sprintf(__('%s', 'multisafepay'), $this->getName()),
            'default'       => 'no'
        );

        $this->form_fields['title'] = array(
            'title'         => __('Title', 'woocommerce'),
            'type'          => 'text',
            'description'   => __('This controls the title which the user sees during checkout.', 'woocommerce'),
            'default'       => $this->getName(),
            'desc_tip'      => true,
        );

        $this->form_fields['description'] = array(
            'title'         => __('Customer Message', 'woocommerce'),
            'type'          => 'textarea',
            'default'       => sprintf(__('Pay with %s', 'multisafepay'), $this->getName()),
        );
        $this->form_fields  = array_merge($this->form_fields, $form_fields);

        parent::init_settings();
    }

    public function payment_fields()
    {
        echo $this->get_option('description');
    }

    public function process_payment($order_id)
    {

        $order = new WC_Order($order_id);
        $msp   = new Client();

        $msp->setApiKey($this->getApiKey());
        $msp->setApiUrl($this->getTestMode());

        $my_order = array(
            "type"                  => $this->getType(),
            "order_id"              => $order->get_order_number(),
            "currency"              => get_woocommerce_currency(),
            "amount"                => round($order->get_total() * 100),
            "description"           => 'Order #'.$order->get_order_number(),
            "var1"                  => $order->order_key,
            "var2"                  => $order_id,
            "items"                 => $this->setItemList($order->get_items()),
            "manual"                => false,
            "gateway"               => $this->getGatewayCode(),
            "seconds_active"        => $this->getTimeActive(),
            "payment_options"       => array(
                "notification_url"  => add_query_arg('type=initial', '', $this->getNurl()),
                "redirect_url"      => add_query_arg('utm_nooverride', '1', $this->get_return_url($order)),
                "cancel_url"        => htmlspecialchars_decode(add_query_arg('key', $order->id, $order->get_cancel_order_url())),
                "close_window"      => true
            ),
            "customer"              => $this->setCustomer($msp, $order),
            "delivery"              => $this->setDelivery($msp, $order),
            "google_analytics"      => $this->setGoogleAnalytics(),
            "plugin"                => $this->setPlugin(),
            "gateway_info"          => isset($this->GatewayInfo) ? $this->GatewayInfo : array(),
            "shopping_cart"         => isset($this->shopping_cart) ? $this->shopping_cart : array(),
            "checkout_options"      => isset($this->checkout_options) ? $this->checkout_options : array(),
        );

        $msg = null;
        try {
            $msp->orders->post($my_order);
            $url = $msp->orders->getPaymentLink();
        } catch (Exception $e) {

            $msg = 'Error: '.htmlspecialchars($e->getMessage());
            $this->write_log($msg);

            wc_add_notice(__('Payment error:', 'multisafepay').' '.$msg, 'error');
        }


        if (!$msg) {
            return array(   'result'    => 'success',
                            'redirect'  => $url);
        } else {

            $this->write_log('msp->transactiondata:');
            $this->write_log($msp);
            $this->write_log('msp->transaction URL:'.$url);
            $this->write_log('msp->End debug');

            $msg = 'Error: '.htmlspecialchars($e->getMessage());
            $this->write_log($msg);

            return array(   'result'    => 'error',
                            'redirect'  => $url);
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $msp = new Client();

        $msp->setApiKey($this->getApiKey());
        $msp->setApiUrl($this->getTestMode());

        $order = new WC_Order($order_id);

        $endpoint = 'orders/'.$order_id.'/refunds';
        $refund   = array(  "currency"      => $order->get_order_currency(),
                            "amount"        => $amount * 100,
                            "description"   => $reason );

        try {
            $order = $msp->orders->post($refund, $endpoint);
        } catch (Exception $e) {

            $msg = 'Error: '.htmlspecialchars($e->getMessage());
            $this->write_log($msg);

            wc_add_notice(__('Payment error:', 'multisafepay').' '.$msg, 'error');
        }

        if ($msp->error) {
            return new WP_Error('multisafepay_ideal', 'Order can\'t be refunded:'.$msp->error_code.' - '.$msp->error);
        } else {
            return true;
        }
    }

    public function getCart($order_id)
    {
        $order = new WC_Order($order_id);

        $shopping_cart                             = array();
        $checkout_options                          = array();
        $checkout_options['tax_tables']['default'] = array('shipping_taxed' => 'true',
            'rate' => '0.21');

        //Add BTW 0%
        $checkout_options['tax_tables']['alternate'][] = array('name' => 'BTW-0',
            'rules' => array(array('rate' => '0.00')));

        $tax_array = array('BTW-0');

        // Fee
        /*              foreach ($order->get_items('fee') as $fee) {

          $taxes = unserialize($fee['taxes']);
          $taxes = array_shift ($taxes);

          $tax_table_selector = 'fee';
          $tax_percentage = round($taxes /$fee['cost'], 2);

          $method_id = explode (':', $fee['method_id']);

          $shopping_cart['items'][] = array (
          'name'  		     => $fee['type'],
          'description' 		 => $fee['name'],
          'unit_price'  		 => $fee['cost'],
          'quantity'    		 => 1,
          'merchant_item_id' 	 => $method_id[0],
          'tax_table_selector' => $tax_table_selector,
          'weight' 		     => array ('unit'=> 0,  'value'=> 'KG')
          );

          if (!in_array($tax_table_selector, $tax_array)) {
          array_push($checkout_options['tax_tables']['alternate'], array ('name' => $tax_table_selector, 'rules' => array (array ('rate' => $tax_percentage))));
          array_push($tax_array, $tax_table_selector);
          }
          }
         */


        // Shipping
        foreach ($order->get_items('shipping') as $shipping) {

            $taxes = unserialize($shipping['taxes']);
            $taxes = array_shift($taxes);

            $tax_table_selector = 'shipping';
            $tax_percentage     = round($taxes / $shipping['cost'], 2);

            $method_id = explode(':', $shipping['method_id']);

            $shopping_cart['items'][] = array(
                'name'              => $shipping['type'],
                'description'       => $shipping['name'],
                'unit_price'        => $shipping['cost'],
                'quantity'          => 1,
                'merchant_item_id'  => $method_id[0],
                'tax_table_selector'=> $tax_table_selector,
                'weight'            => array('unit' => 0, 'value' => 'KG') );

            if (!in_array($tax_table_selector, $tax_array)) {
                array_push($checkout_options['tax_tables']['alternate'],
                    array(  'name'  => $tax_table_selector,
                            'rules' => array(array(
                            'rate'  => $tax_percentage))));
                array_push($tax_array, $tax_table_selector);
            }
        }


        //add coupon discount
        foreach ($order->get_items('coupon') as $coupon) {

            $tax_table_selector = $coupon['type'];
            $tax_percentage     = round($coupon['discount_amount_tax'] / $coupon['discount_amount'], 2);

            $shopping_cart['items'][] = array(
                'name'              => $coupon['type'],
                'description'       => $coupon['name'],
                'unit_price'        => -$coupon['discount_amount'],
                'quantity'          => 1,
                'merchant_item_id'  => $coupon['type'],
                'tax_table_selector'=> $tax_table_selector,
                'weight'            => array('unit' => 0, 'value' => 'KG') );

            if (!in_array($tax_table_selector, $tax_array)) {
                array_push($checkout_options['tax_tables']['alternate'],
                    array(  'name'  => $tax_table_selector,
                            'rules' => array(array(
                            'rate'  => $tax_percentage))));
                array_push($tax_array, $tax_table_selector);
            }
        }

        //add item data
        $items = "<ul>\n";
        foreach ($order->get_items() as $item) {

            $items .= "<li>".$item['qty'].' x : '.$item['name']."</li>\n";

            $tax_percentage = round($item['line_subtotal_tax'] / $item['line_subtotal'], 2);
            $product_price  = round($item['line_subtotal'] / $item['qty'], 5);

            if ($item['line_subtotal_tax'] > 0) {
                $tax_table_selector = 'BTW-'.$tax_percentage * 100;
            } else {
                $tax_table_selector = 'BTW-0';
            }

            $shopping_cart['items'][] = array(
                'name'              => $item['name'],
                'description'       => '',
                'unit_price'        => $product_price,
                'quantity'          => $item['qty'],
                'merchant_item_id'  => $item['product_id'],
                'tax_table_selector'=> $tax_table_selector,
                'weight'            => array('unit' => 0, 'value' => 'KG') );

            if (!in_array($tax_table_selector, $tax_array)) {
                array_push($checkout_options['tax_tables']['alternate'],
                    array(  'name'  => $tax_table_selector,
                            'rules' => array(array(
                            'rate'  => $tax_percentage))));
                array_push($tax_array, $tax_table_selector);
            }
        }
        $items .= "</ul>\n";

        return ( array($shopping_cart, $checkout_options) );
    }

    public function getGatewayInfo($order_id)
    {
        $order = new WC_Order($order_id);

        return (array(  'referrer'      => $_SERVER['HTTP_REFERER'],
                        'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
                        'birthday'      => $_POST['PAYAFTER_birthday'],
                        'bankaccount'   => $_POST['PAYAFTER_account'],
                        'phone'         => $order->billing_phone,
                        'email'         => $order->billing_email,
                        'gender'        => '') );
    }

    public function setItemList($items)
    {
        $list = '<ul>';
        foreach ($items as $item) {
            $list .= '<li>'.absint($item['qty']).' x '.html_entity_decode($item['name'], ENT_NOQUOTES, 'UTF-8').'</li>';
        }
        $list .= '</ul>';
        return ($list);
    }

    public function setDelivery($msp, $order)
    {
        $address = isset($order->shipping_address_1) ? $order->shipping_address_1 : '';
        list ($street, $houseNumber) = $msp->parseCustomerAddress($address);

        return ( array( "locale"        => $this->getLocale(),
                        "ip_address"    => $_SERVER['REMOTE_ADDR'],
                        "referrer"      => $_SERVER['HTTP_REFERER'],
                        "user_agent"    => $_SERVER['HTTP_USER_AGENT'],
                        "first_name"    => isset($order->shipping_first_name) ? $order->shipping_first_name : '',
                        "last_name"     => isset($order->shipping_last_name) ? $order->shipping_last_name : '',
                        "address1"      => $street,
                        "house_number"  => $houseNumber,
                        "zip_code"      => isset($order->shipping_postcode) ? $order->shipping_postcode : '',
                        "city"          => isset($order->shipping_city) ? $order->shipping_city : '',
                        "state"         => isset($order->shipping_state) ? $order->shipping_state : '',
                        "country"       => isset($order->shipping_country) ? $order->shipping_country : '',
                        "phone"         => isset($order->shipping_phone) ? $order->shipping_phone : '',
                        "email"         => isset($order->shipping_email) ? $order->shipping_email : ''));
    }

    public function setCustomer($msp, $order)
    {
        $address = isset($order->billing_address_1) ? $order->billing_address_1 : '';
        list ($street, $houseNumber) = $msp->parseCustomerAddress($address);

        return ( array( "locale"        => $this->getLocale(),
                        "ip_address"    => $_SERVER['REMOTE_ADDR'],
                        "referrer"      => $_SERVER['HTTP_REFERER'],
                        "user_agent"    => $_SERVER['HTTP_USER_AGENT'],
                        "first_name"    => isset($order->billing_first_name) ? $order->billing_first_name : '',
                        "last_name"     => isset($order->billing_last_name) ? $order->billing_last_name : '',
                        "address1"      => $street,
                        "house_number"  => $houseNumber,
                        "zip_code"      => isset($order->billing_postcode) ? $order->billing_postcode : '',
                        "city"          => isset($order->billing_city) ? $order->billing_city : '',
                        "state"         => isset($order->billing_state) ? $order->billing_state : '',
                        "country"       => isset($order->billing_country) ? $order->billing_country : '',
                        "phone"         => isset($order->billing_phone) ? $order->billing_phone : '',
                        "email"         => isset($order->billing_email) ? $order->billing_email : ''));
    }

    public function setGoogleAnalytics()
    {
        return ( array("account" => "UA-XXXXXXXXX"));
    }

    public function setPlugin()
    {
        global $woocommerce;

        return ( array( "shop"          => "WooCommerce",
                        "shop_version"  => 'WooCommerce '.$woocommerce->version,
                        "plugin_version"=> '('.get_option('multisafepay_version').')',
                        "partner"       => '',
                        "shop_root_url" => ''));
    }

    public function getLocale()
    {
        return (str_replace('-', '_', get_bloginfo('language')));
    }

    public function write_log($log)
    {
        if (get_option('multisafepay_debugmode') == 'yes') {
            if (is_array($log) || is_object($log)) error_log(print_r($log, true));
            else error_log($log);
        }
    }
}