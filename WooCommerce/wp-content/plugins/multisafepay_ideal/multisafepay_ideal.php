<?php

/*
  Plugin Name: Multisafepay iDEAL
  Plugin URI: http://www.multisafepay.com
  Description: Multisafepay Payment Plugin
  Author: Multisafepay
  Author URI:http://www.multisafepay.com
  Version: 2.1.0

  Copyright: � 2012 Multisafepay(email : techsupport@multisafepay.com)
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


load_plugin_textdomain('multisafepay', false, dirname(plugin_basename(__FILE__)) . '/');

if (!class_exists('MultiSafepay')) {
    require(realpath(dirname(__FILE__)) . '/../multisafepay/MultiSafepay.combined.php');
}
add_action('plugins_loaded', 'WC_MULTISAFEPAY_IDEAL_Load', 0);

function WC_MULTISAFEPAY_IDEAL_Load() {

    class WC_MULTISAFEPAY_IDEAL extends WC_Payment_Gateway {

        public function __construct() {
            global $woocommerce;

            $this->init_settings();
            $this->settings2 = (array) get_option('woocommerce_multisafepay_settings');
            $this->id = "MULTISAFEPAY_IDEAL";

            $this->has_fields = false;
            $this->paymentMethodCode = "IDEAL";

            add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
            add_action("woocommerce_update_options_payment_gateways_MULTISAFEPAY_IDEAL", array($this, 'process_admin_options'));
            add_filter('woocommerce_payment_gateways', array('WC_MULTISAFEPAY_IDEAL', 'MULTISAFEPAY_IDEAL_Add_Gateway'));

            $output = '';

            if ($this->settings2['testmode'] == 'yes'):
                $mspurl = true;
            else :
                $mspurl = false;
            endif;

            $msp = new MultiSafepay();
            $msp->test = $mspurl;
            $msp->merchant['account_id'] = $this->settings2['accountid'];
            $msp->merchant['site_id'] = $this->settings2['siteid'];
            $msp->merchant['site_code'] = $this->settings2['securecode'];

            $iDealIssuers = $msp->getIdealIssuers();

            $output .= "<select name='IDEAL_issuer' style='width:164px; padding: 2px; margin-left: 7px;'>";
            $output .= '<option>Kies uw bank</option>';

            if ($iDealIssuers['issuers']) {
                if ($this->settings2['testmode'] == 'yes') {
                    foreach ($iDealIssuers['issuers'] as $issuer) {
                        $output .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
                    }
                } else {
                    foreach ($iDealIssuers['issuers']['issuer'] as $issuer) {
                        $output .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
                    }
                }
            } else {
                $output .= '<option value="none">No issuers available, check settings</option>';
            }
            $output .= '</select>';


            if (file_exists(dirname(__FILE__) . '/images/IDEAL.png')) {
                $this->icon = apply_filters('woocommerce_multisafepay_ideal_icon', plugins_url('images/IDEAL.png', __FILE__));
            } else {
                $this->icon = '';
            }

            $this->settings = (array) get_option("woocommerce_{$this->id}_settings");
            if ($this->settings['pmtitle'] != "") {
                $this->title = $this->settings['pmtitle'];
                $this->method_title = $this->settings['pmtitle'];
            } else {
                $this->title = "iDEAL";
                $this->method_title = "iDEAL";
            }

            if (isset($this->settings['issuers'])) {
                if ($this->settings['issuers'] != 'yes') {
                    $output = '';
                }
            }


            $this->IDEAL_Forms();

            if (isset($this->settings['description'])) {
                if ($this->settings['description'] != '') {
                    $this->description = $this->settings['description'];
                }
            }
            $this->description .= $output;


            if (isset($this->settings['enabled'])) {
                if ($this->settings['enabled'] == 'yes') {
                    $this->enabled = 'yes';
                } else {
                    $this->enabled = 'no';
                }
            } else {
                $this->enabled = 'no';
            }
        }

        public function IDEAL_Forms() {
            $this->form_fields = array(
                'stepone' => array(
                    'title' => __('Gateway Setup', 'multisafepay'),
                    'type' => 'title'
                ),
                'pmtitle' => array(
                    'title' => __('Title', 'multisafepay'),
                    'type' => 'text',
                    'description' => __('Optional:overwrites the title of the payment method during checkout', 'multisafepay'),
                    'css' => 'width: 300px;'
                ),
                'enabled' => array(
                    'title' => __('Enable this gateway', 'multisafepay'),
                    'type' => 'checkbox',
                    'label' => __('Enable transaction by using this gateway', 'multisafepay'),
                    'default' => 'yes',
                    'description' => __('When enabled it will show on during checkout', 'multisafepay'),
                ),
                'issuers' => array(
                    'title' => __('Enable iDEAL issuers', 'multisafepay'),
                    'type' => 'checkbox',
                    'label' => __('Enable bank selection on website', 'multisafepay'),
                    'default' => 'yes',
                    'description' => __('Enable of disable the selection of the preferred bank within the website.', 'multisafepay'),
                ),
                'description' => array(
                    'title' => __('Gateway Description', 'multisafepay'),
                    'type' => 'text',
                    'description' => __('This will be shown when selecting the gateway', 'multisafepay'),
                    'css' => 'width: 300px;'
                ),
            );
        }

        public function process_payment($order_id) {
            global $wpdb, $woocommerce;

            $settings = (array) get_option('woocommerce_multisafepay_settings');

            if ($settings['send_confirmation'] == 'yes') {
                $mailer = $woocommerce->mailer();
                $email = $mailer->emails['WC_Email_New_Order'];
                $email->trigger($order_id);
            }

            $order = new WC_Order($order_id);
            $language_locale = get_bloginfo('language');
            $language_locale = str_replace('-', '_', $language_locale);

            $paymentMethod = explode('_', $order->payment_method);
            $gateway = strtoupper($paymentMethod[1]);

            $html = '<ul>';
            $item_loop = 0;

            if (sizeof($order->get_items()) > 0) : foreach ($order->get_items() as $item) :
                    if ($item['qty']) :
                        $item_loop++;
                        $html .= '<li>' . $item['name'] . ' x ' . $item['qty'] . '</li>';
                    endif;
                endforeach;
            endif;

            $html .= '</ul>';
            if ($settings['testmode'] == 'yes'):
                $mspurl = true;
            else :
                $mspurl = false;
            endif;

            $ordernumber = ltrim($order->get_order_number(), __('#', '', 'multisafepay'));
            $ordernumber = ltrim($ordernumber, __('n°', '', 'multisafepay'));

            $msp = new MultiSafepay();
            $msp->test = $mspurl;
            $msp->merchant['account_id'] = $settings['accountid'];
            $msp->merchant['site_id'] = $settings['siteid'];
            $msp->merchant['site_code'] = $settings['securecode'];
            $msp->merchant['notification_url'] = $settings['notifyurl'] . '&type=initial';
            $msp->merchant['cancel_url'] = $order->get_cancel_order_url();
            $msp->merchant['cancel_url'] = htmlspecialchars_decode(add_query_arg('key', $order->id, $msp->merchant['cancel_url']));
            $msp->merchant['redirect_url'] = add_query_arg('utm_nooverride', '1', $this->get_return_url($order));
            $msp->merchant['close_window'] = true;
            $msp->customer['locale'] = $language_locale;
            $msp->customer['firstname'] = $order->billing_first_name;
            $msp->customer['lastname'] = $order->billing_last_name;
            $msp->customer['zipcode'] = $order->billing_postcode;
            $msp->customer['city'] = $order->billing_city;
            $msp->customer['email'] = $order->billing_email;
            $msp->customer['phone'] = $order->billing_phone;
            $msp->customer['country'] = $order->billing_country;
            $msp->customer['state'] = $order->billing_state;
            $msp->parseCustomerAddress($order->billing_address_1);
            $msp->transaction['id'] = $ordernumber; //$order_id; 
            $msp->transaction['currency'] = get_woocommerce_currency();
            $msp->transaction['amount'] = $order->get_total() * 100;
            $msp->transaction['description'] = 'Order ' . __('#', '', 'multisafepay') . $ordernumber . ' : ' . get_bloginfo();
            $msp->transaction['gateway'] = $gateway;
            $msp->plugin_name = 'WooCommerce';
            $msp->plugin['shop'] = 'WooCommerce';
            $msp->plugin['shop_version'] = $woocommerce->version;
            $msp->plugin['plugin_version'] = '2.1.0';
            $msp->plugin['partner'] = '';
            $msp->version = '(2.1.0)';
            $msp->transaction['items'] = $html;
            $msp->transaction['var1'] = $order->order_key;
            $msp->transaction['var2'] = $order_id;
            $issuerName = sprintf('%s_issuer', $paymentMethod[1]);
            $issuerName = sprintf('%s_issuer', $paymentMethod[1]);


            if (isset($_POST[$issuerName])) {
                $msp->extravars = $_POST[$issuerName];
                $url = $msp->startDirectXMLTransaction();
            } else {
                $url = $msp->startTransaction();
            }


            if (!isset($msp->error)) {
                return array(
                    'result' => 'success',
                    'redirect' => $url
                );
            } else {
                $woocommerce->add_error(__('Payment error:', 'multisafepay') . ' ' . $msp->error);
            }
        }

        public static function MULTISAFEPAY_IDEAL_Add_Gateway($methods) {
            global $woocommerce;
            $methods[] = 'WC_MULTISAFEPAY_IDEAL';
            return $methods;
        }

    }

    // Start 
    new WC_MULTISAFEPAY_IDEAL();
}
