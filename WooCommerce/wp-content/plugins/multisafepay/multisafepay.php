<?php

/*
  Plugin Name: Multisafepay
  Plugin URI: http://www.multisafepay.com
  Description: Multisafepay Payment Plugin
  Author: Multisafepay
  Author URI:http://www.multisafepay.com
  Version: 3.0.0

  Copyright: ? 2012 Multisafepay(email : techsupport@multisafepay.com)
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


//Autoloader laden en registreren
require_once dirname(__FILE__) . '/includes/classes/Autoload.php';
require_once dirname(__FILE__) . '/includes/classes/MultiSafepay/api/Autoload.php';


//plugin functies inladen
require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

//textdomain inladen
load_plugin_textdomain( 'multisafepay', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );

function error_woocommerce_not_active() {
    echo '<div class="error"><p>' . __('To use the Multisafepay plugin it is required that woocommerce is active', 'multisafepay') . '</p></div>';
}

function error_curl_not_installed() {
    echo '<div class="error"><p>' . __('Curl is not installed.<br />In order to use the MultiSafepay plug-in, you must install CURL.<br />Ask your system administrator to install php_curl', 'multisafepay') . '</p></div>';
}


// Curl is niet geinstalleerd. foutmelding weergeven
if (!function_exists('curl_version')) {
    add_action('admin_notices', __('error_curl_not_installed', 'multisafepay'));
}


if (is_plugin_active('woocommerce/woocommerce.php') || is_plugin_active_for_network('woocommerce/woocommerce.php')) {

    //Autoloader registreren
    MultiSafepay_Autoload::register();
    API_Autoload::register();

    //MultiSafepay gateways aan woocommerce koppelen
    MultiSafepay_Gateways::register();

} else {
    // Woocommerce is niet actief. foutmelding weergeven
    add_action('admin_notices', error_woocommerce_not_active);
}



// Check if new version is available.
class update_available {
    private $_message;

    function __construct( $message ) {
        $this->_message = $message;

        add_action( 'admin_notices', array( $this, 'render' ) );
    }

    function render() {
        printf( '<div class="notice">%s</div>', $this->_message );
    }
}

// Slow down the performance to much, So temporarly disabled
//if ($message = new_plugin_version_available()) {
//    new update_available( $message );
//}

function new_plugin_version_available(){

    $html = 'https://www.multisafepay.com/nl_nl/oplossingen/shop-plug-ins/detail/plugins/woocommerce/';

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTMLFile($html);
    libxml_clear_errors();

    $items = $doc->getElementsByTagName('td');
    $latestVersion = $items->item(0)->nodeValue;

    if ($latestVersion > get_option('multisafepay_version') ){

        $message = __('There is a new version of the MultiSafepay plugin available. ', 'multisafepay')
        . '<a href="https://www.multisafepay.com/fileadmin/plugins/changelogs/woocommerce.html">'
        . __('View version', 'multisafepay') . $latestVersion
        . __(' details', 'multisafepay')     . '</a>'
        . __(' or ', 'multisafepay')
        . '<a href="https://www.multisafepay.com/fileadmin/plugins/Plugin_WooCommerce_' . $latestVersion . '.zip">'
        . __('download now.', 'multisafepay') . '</a>';


        return ($message);
    }else{
        return false;
    }
}
