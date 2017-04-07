<?php

define ('DEBUG', false);

if (DEBUG) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL)
    error_log(date ('Y-m-d H:i:s') . "Parameters: " . print_r ($_GET, true) . "\n", 3, "MultiSafepay_Feed.log");
}


$results = feeds($_GET);

// Reindex array
$results = reOrderArray($results);
if (DEBUG)
    error_log(date ('Y-m-d H:i:s') . "results: " . print_r ($results, true) . "\n", 3, "MultiSafepay_Feed.log");


// create JSON
$json = json_encode($results);
$json = utf8_encode($json);
$json = prettyPrint($json);
if (DEBUG)
    error_log(date ('Y-m-d H:i:s') . "json: " . $json . "\n", 3, "MultiSafepay_Feed.log");


if (isset ($_GET['test'])){
    echo '<pre>';
}else{
    $json = gzcompress($json);
    if (DEBUG)
        error_log(date ('Y-m-d H:i:s') . "json: " . $json .  "\n", 3, "MultiSafepay_Feed.log");
}

die($json);



function feeds($params)
{
    if (!isset ($_GET['test'])){
        //get full url of the call
        $api_key = '';

        // This should be the full URL including parameters
        $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
        $url = $base_url . $_SERVER["REQUEST_URI"];

        $header = get_nginx_headers();

        // This can be found within your website profile at MultiSafepay
        $hash_id = '';

        $timestamp = microtime_float();
        $auth = explode('|', base64_decode($header['Auth']));

        $message = $url.$auth[0].$hash_id;
        $token = hash_hmac('sha512', $message, $api_key);

        if($token !== $auth[1] and round($timestamp - $auth[0]) > 10)
        {
            return (array('This is not a valid Feed command'));
        }
    }

    // no identifier provided
    if (!isset($params['identifier']))
        return ('no identifier provided');

    if ($params['identifier'] == 'products' && isset($params['product_id']) && $params['product_id'] != '')
        return (productById($params['product_id']));

    if ($params['identifier'] == 'products' && isset($params['category_id']) && $params['category_id'] != '')
        return (productByCategory($params['category_id']));

    if ($params['identifier'] == 'categories')
        return (categories($params));

    if ($params['identifier'] == 'stock' && isset($params['product_id']) && $params['product_id'] != '')
        return (stock($params['product_id']));

    if ($params['identifier'] == 'stores')
        return (stores($params));

    if ($params['identifier'] == 'shipping')
        return (shipping($params));

    return (array('No results available'));
}


function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


function productById($product_id = 0)
{
    $results = array();
    $product = WC()->product_factory->get_product($product_id);
    if ($product)
        $results = get_product_details($product);


    return($results);
}

function productByCategory($category_id = 0)
{
    $results = array();
    $args    = array(   'post_type'             => 'product',
                        'post_status'           => 'publish',
                        'ignore_sticky_posts'   => 1,
                        'posts_per_page'        => '-1',
                        'meta_query'            => array( array( 'key'      => '_visibility',
                                                                 'value'    => array('catalog', 'visible'),
                                                                 'compare'  => 'IN' ) ),
                        'tax_query'             => array( array( 'taxonomy' => 'product_cat',
                                                                 'field'    => 'term_id',
                                                                 'terms'    => $category_id,
                                                                 'operator' => 'IN' )));
    $products = new WP_Query($args);

    while ($products->have_posts()) {
        $products->the_post();
        $id        = get_the_ID();
        $_product  = WC()->product_factory->get_product($id);
        $results[] = get_product_details($_product);
    }
    return ($results);
}

function stock($product_id = 0)
{
    $results = array();
    $product = WC()->product_factory->get_product($product_id);
    if ($product) {
        $data = get_product_details($product);

        $results = array('product_id'   => $product->get_id(),
                         'stock'        => $product->get_stock_quantity());
    }
    return ($results);
}

function categories()
{
    $results = _categories();
    return ($results);
}

function _categories($id = 0)
{
    global $wpdb;
    $sql = "SELECT wp_terms.term_id, wp_terms.name
				FROM wp_terms
				LEFT JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id
				WHERE wp_term_taxonomy.taxonomy = 'product_cat'
 				  AND wp_term_taxonomy.parent =".$id;

    $results  = $wpdb->get_results($sql);

    $children = array();

    if (count($results) > 0) {

        # It has children, let's get them.
        foreach ($results as $key => $result) {
            # Add the child to the list of children, and get its subchildren
            $children[$result->term_id]['id']       = $result->term_id;
            $children[$result->term_id]['title']    = array(get_locale() => $result->name);

            $childs = _categories($result->term_id);
            if (count ($childs) > 0 )
                $children[$result->term_id]['children'] = $childs;
        }
    }
    return $children;
}



function shipping()
{
    $active_methods   = array();

    // Get shippingmethods based on cart
    $shipping_methods = WC()->shipping->calculate_shipping(get_shipping_packagesFCO());

    // If no results, get global shipping methods
    if (count($shipping_methods) === 0)
        $shipping_methods = WC()->shipping->get_shipping_methods();


    foreach ($shipping_methods as $id => $shipping_method) {
        if (isset($shipping_method->enabled) && $shipping_method->enabled == 'yes') {

            $active_methods[] = array('id' => $id,
                'type' => $shipping_method->method_title,
                'provider' => '',
                'name' => $shipping_method->method_title,
                'price' => '',
//                'excluded_areas' => array(),
//                'included_areas' => array()
            );
        }
    }

    return $active_methods;
}


function get_shipping_packagesFCO() {
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


function stores()
{

    $store = array( 'allowed_countries'     => WC()->countries->get_countries(),
                    'shipping_countries'    => array(),
                    'languages'             => array(get_locale()),

                    'stock_updates'         => get_option('woocommerce_manage_stock') == 'yes' ? true : false,
                    'supported_currencies'  => get_woocommerce_currency(),

                    'including_tax'         => get_option('woocommerce_prices_include_tax') == 'yes' ? true : false,
                    'shipping_tax'          => array(   'id'    => '',
                                                        'name'  => '',
                                                        'rules' => array(get_locale() => '')),

                    'require_shipping'      => wc_shipping_enabled(),
                    'base_url'              => get_home_url(),
                    'logo'                  => '',
                    'order_push_url'        => get_option('multisafepay_nurl'),
                    'order_notification'    => '',
                    'coc'                   => '',
                    'email'                 => '',
                    'contact_phone'         => '',
                    'address'               => '',
                    'housenumber'           => '',
                    'zipcode'               => '',
                    'city'                  => '',
                    'country'               => '',
                    'vat_nr'                => '',
                    'terms_and_conditions'  => '',
                    'faq'                   => '',
                    'open'                  => '00:00',
                    'closed'                => '23:59',
                    'days'                  => array(   'sunday'    => true,
                                                        'monday'    => true,
                                                        'tuesday'   => true,
                                                        'wednesday' => true,
                                                        'thursday'  => true,
                                                        'friday'    => true,
                                                        'saturday'  => true),
                    'social'                => array(   'facebook' => '',
                                                        'twitter' => '',
                                                        'linkedin' => ''));

    return ($store);
}

function get_product_details($product)
{
    // get categories, maximal=2
    $categories = strip_tags($product->get_categories());
    list ($primary_category, $secondary_category, $rest) = array_pad(explode(', ', $categories, 3), 3, null);

    // get (all) taxes incl info
    $tax_id   = $product->get_tax_class() ? $product->get_tax_class() : 'Standard';
    $rates    = reset(WC_Tax::get_rates($product->get_tax_class()));
    $location = WC_tax::get_tax_location();

    // get meta tags
    $metadata = array();
    $tags     = explode(", ", strip_tags($product->get_tags()));
    foreach ($tags as $tag) {
        if ($tag) {

            array_push($metadata, array(get_locale() => array(  'title'         => $tag,
                                                                'keyword'       => $tag,
                                                                'description'   => $tag)));
        }
    }

    // get main image
    $images = array();
    $_images                      = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail');
    if ($_images) {
        $main_image = reset($_images);
        array_push($images, array('url'   => $main_image,
                                                        'main'  => true));

    }

    // get other images
    $attachment_ids = $product->get_gallery_attachment_ids();
    foreach ($attachment_ids as $attachment_id) {
        $_images = wp_get_attachment_url($attachment_id);
        if ($_images != $main_image)
            array_push($images, array('url'   => $_images,
                                      'main'  => false));

    }

    // Variens?
    $variants = array();
    if ($product->has_child()) {

        $available_variations = $product->get_available_variations();
        foreach ($available_variations as $variation) {

            $attributes = array();
            foreach ($variation['attributes'] as $key => $attr) {
                $key                            = str_replace('attribute_pa_', '', $key);
//                $attributes['attributes'][$key] = array(get_locale() => array('label' => $key, 'value' => $attr));
                $attributes[$key] = array(get_locale() => array('label' => $key, 'value' => $attr));
            }

            $variants[] = array('product_id'        => $variation['variation_id'],
                                'sku_number'        => $variation['sku'],
                                'gtin'              => null,
                                'unique_identifier' => false,
                                'product_image_url' => $images,
                                'stock'             => $variation['max_qty']+0,
                                'sale_price'        => number_format((double) $variation['display_price'], 2, '.', ''),
                                'retail_price'      => number_format((double) $variation['display_regular_price'], 2, '.', ''),
                                'attributes'        => $attributes
                                );
        }
    }

    return ( array( 'product_id'                => $product->get_id(),
                    'product_name'              => $product->get_title(),
                    'brand'                     => null,
                    'sku_number'                => $product->get_sku(),
                    'product_url'               => $product->post->guid,
                    'primary_category'          => array(get_locale() => $primary_category),
                    'secondary_category'        => array(get_locale() => $secondary_category),
                    'short_product_description' => array(get_locale() => $product->post->post_excerpt ? $product->post->post_excerpt : $product->get_title()),
                    'long_product_description'  => array(get_locale() => $product->post->post_content ? $product->post->post_content : $product->get_title()),
                    'sale_price'                => number_format ((double) $product->get_sale_price(), 2, '.', ''),
                    'retail_price'              => number_format ((double) $product->get_regular_price(), 2, '.', ''),
                    'tax'                       => array(   'id'    => $tax_id,
                                                            'name'  => $rates['label'],
                                                            'rules' => array($location[0] => $rates['rate'])),
                    'gtin'                      => null,
                    'mpn'                       => null,
                    'unique_identifier'         => false,
                    'stock'                     => (int)$product->get_stock_quantity(),
                    'options'                   => null,
                    'attributes'                => array(),
                    'metadata'                  => $metadata,
                    'created'                   => $product->post->post_date,
                    'updated'                   => $product->post->post_modified,
                    'downloadable'              => $product->is_downloadable(),
                    'package_dimensions'        => (int)$product->get_length().'x'.(int)$product->get_width().'x'.(int)$product->get_height(),
                    'dimension_unit'            => get_option('woocommerce_dimension_unit'),
                    'weight'                    => number_format ((double) $product->get_weight(), 5, '.', ''),
                    'weight_unit'               => get_option('woocommerce_weight_unit'),
                    'product_image_urls'        => $images,
                    'variants'                  => $variants ));

}






function reOrderArray($array)
{
    if (!is_array($array)) {
        return $array;
    }
    $count  = 0;
    $result = array();
    foreach ($array as $k => $v) {
        if (is_integer_value($k)) {
            $result[$count] = reOrderArray($v);
            ++$count;
        } else {
            $result[$k] = reOrderArray($v);
        }
    }
    return $result;
}

function is_integer_value($value)
{
    if (!is_int($value)) {
        if (is_string($value) && preg_match("/^-?\d+$/i", $value)) {
            return true;
        }
        return false;
    }
    return true;
}

function prettyPrint($json)
{
    $result          = '';
    $level           = 0;
    $in_quotes       = false;
    $in_escape       = false;
    $ends_line_level = NULL;
    $json_length     = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char           = $json[$i];
        $new_line_level = NULL;
        $post           = "";
        if ($ends_line_level !== NULL) {
            $new_line_level  = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level  = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char            = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level  = NULL;
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }
        if ($new_line_level !== NULL) {
            $result .= "\n".str_repeat("\t", $new_line_level);
        }
        $result .= $char.$post;
    }

    return $result;
}

function get_nginx_headers($function_name='getallheaders'){

        $all_headers=array();

        if(function_exists($function_name)){

            $all_headers=$function_name();
        }
        else{

            foreach($_SERVER as $name => $value){

                if(substr($name,0,5)=='HTTP_'){

                    $name=substr($name,5);
                    $name=str_replace('_',' ',$name);
                    $name=strtolower($name);
                    $name=ucwords($name);
                    $name=str_replace(' ', '-', $name);

                    $all_headers[$name] = $value;
                }
                elseif($function_name=='apache_request_headers'){

                    $all_headers[$name] = $value;
                }
            }
        }


        return $all_headers;
}

?>
