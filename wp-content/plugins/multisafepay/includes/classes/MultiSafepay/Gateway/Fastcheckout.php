<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      TechSupport <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2017 MultiSafepay, Inc. (http://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class MultiSafepay_Gateway_Fastcheckout extends MultiSafepay_Gateway_Abstract
{

    public static function getCode()
    {
        return;
    }
    public static function getName()
    {
        return;
    }
    
    public static function getTitle()
    {
        return;
    }

    public function getItemsFCO () {
        $items = array();
        foreach (WC()->cart->get_cart() as $values) {
            $items[] = array ( 'name' => $values['data']->get_title(), 'qty' => $values['quantity'] );
        }
        return ($items);
    }

    public function setCartFCO() {

        $shopping_cart = array();
        foreach (WC()->cart->get_cart() as $values) {

            $_product = $values['data'];

            $qty    = absint($values['quantity']);
            $sku    = $_product->get_sku();
            $id     = $_product->get_id();

            $name   = html_entity_decode($_product->get_title(), ENT_NOQUOTES, 'UTF-8');
            $descr  = html_entity_decode(get_post($_product)->post->post_content, ENT_NOQUOTES, 'UTF-8');

            if ($_product->product_type == 'variation') {
                $meta = WC()->cart->get_item_data($values, true);

                if (empty($sku))
                    $sku = $_product->parent->get_sku();

                if (!empty($meta))
                    $name .= " - " . str_replace(", \n", " - ", $meta);
            }

            $product_price       = $values['line_subtotal'] / $qty;
            $percentage          = round ($values['line_subtotal_tax'] /$values['line_subtotal'] ,2);

            $json_array = array();
            $json_array['sku'] = $sku;
            $json_array['id']  = $id;

            $shopping_cart['items'][] = array (
                'name'  			 => $name,
                'description' 		 => $descr,
                'unit_price'  		 => $product_price,
                'quantity'    		 => $qty,
                'merchant_item_id' 	 => json_encode($json_array),
                'tax_table_selector' => 'Tax-'. $percentage,
                'weight' 			 => array ('unit'=> '0',  'value'=> 'KG')
            );
        }


        // Add custom Woo cart fees as line items
        foreach (WC()->cart->get_fees() as $fee) {
            if ($fee->tax > 0)
                $fee_tax_percentage = round($fee->tax / $fee->amount, 2);
            else
                $fee_tax_percentage = 0;

            $json_array = array();
            $json_array['fee'] = $fee->name;

            $shopping_cart['items'][] = array (
                'name'  			 => $fee->name,
                'description' 		 => $fee->name,
                'unit_price'  		 => number_format($fee->amount, 2, '.', ''),
                'quantity'    		 => 1,
                'merchant_item_id' 	 => json_encode($json_array),
                'tax_table_selector' => 'Tax-'. $fee_tax_percentage,
                'weight' 			 => array ('unit'=> '',  'value'=> 'KG')
            );
        }

        // Get discount(s)
        foreach (WC()->cart->applied_coupons as $code) {

            $unit_price     = WC()->cart->coupon_discount_amounts[$code];
            $unit_price_tax = WC()->cart->coupon_discount_tax_amounts[$code];
            $percentage     = round ($unit_price_tax/$unit_price ,2);

            $json_array = array();
            $json_array['Coupon-code'] = $code;

            $shopping_cart['items'][] = array (
                'name'  			 => 'Discount Code: ' . $code,
                'description' 		 => '',
                'unit_price'  		 => -round ($unit_price, 5),
                'quantity'    		 => 1,
                'merchant_item_id' 	 => json_encode($json_array),
                'tax_table_selector' => 'Tax-'. ($percentage*100),
                'weight' 			 => array ('unit'=> '',  'value'=> 'KG')
            );
        }

        return ($shopping_cart);
    }

    public function setCheckoutOptionsFCO(){

        $checkout_options = array ();
        $checkout_options['no_shipping_method']         = false;
        $checkout_options['tax_tables']['alternate']    = array ();
        $checkout_options['tax_tables']['default']      = array ('shipping_taxed'=> 'true', 'rate' => '0.21');

        foreach (WC()->cart->get_cart() as $values) {
            $percentage = round ($values['line_subtotal_tax'] /$values['line_subtotal'] ,2);
            array_push($checkout_options['tax_tables']['alternate'], array ('name' => 'Tax-'. $percentage, 'rules' => array (array ('rate' => $percentage ))));
        }

        /* Get CartFee tax */
        foreach (WC()->cart->get_fees() as $fee) {
            if ($fee->tax > 0)
                $fee_tax_percentage = round($fee->tax / $fee->amount, 2);
            else
                $fee_tax_percentage = 0;

            array_push($checkout_options['tax_tables']['alternate'], array ('name' => 'Tax-'. $fee_tax_percentage, 'rules' => array (array ('rate' => $fee_tax_percentage/100 ))));
        }

        /*Get discount(s) tax    */
        if (WC()->cart->get_cart_discount_total()) {
            array_push($checkout_options['tax_tables']['alternate'], array ('name' => 'Tax-0', 'rules' => array (array ('rate' => '0.00' ))));
        }


        WC()->shipping->calculate_shipping($this->get_shipping_packagesFCO());
        foreach (WC()->shipping->packages[0]['rates'] as $rate) {
            $checkout_options['shipping_methods']['flat_rate_shipping'][] = array(  "name"  => $rate->label,
                                                                                    "price" => number_format($rate->cost, '2', '.', ''));
        }

        return ($checkout_options);
    }

    private function get_shipping_packagesFCO() {
        // Packages array for storing 'carts'
        $packages = array();
        $packages[0]['contents']                = WC()->cart->cart_contents;            // Items in the package
        $packages[0]['contents_cost']           = 0;                                    // Cost of items in the package, set below
        $packages[0]['applied_coupons']         = WC()->session->applied_coupon;
        $packages[0]['destination']['country']  = WC()->customer->get_shipping_country();
        $packages[0]['destination']['state']    = WC()->customer->get_shipping_state();
        $packages[0]['destination']['postcode'] = WC()->customer->get_shipping_postcode();
        $packages[0]['destination']['city']     = WC()->customer->get_shipping_city();
        $packages[0]['destination']['address']  = WC()->customer->get_shipping_address();
        $packages[0]['destination']['address_2']= WC()->customer->get_shipping_address_2();

        foreach (WC()->cart->get_cart() as $item)
            if ($item['data']->needs_shipping())
                if (isset($item['line_total']))
                    $packages[0]['contents_cost'] += $item['line_total'];

        return apply_filters('woocommerce_cart_shipping_packages', $packages);
    }
   
}