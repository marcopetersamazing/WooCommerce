<?php
/**
 *  MultiSafepay Payment Module
 *
 *  @author    MultiSafepay <techsupport@MultiSafepay.com>
 *  @copyright Copyright (c) 2013 MultiSafepay (http://www.multisafepay.com)
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class API_Autoload
{
    public static function autoload($class_name)
    {
        $name = str_replace("Object", "Object/", $class_name);
        $file_name = realpath(dirname(__FILE__) . "/{$name}.php");

        if (file_exists($file_name)) {
            require $file_name;
        }
    }

    public static function register()
    {
        return spl_autoload_register(array(__CLASS__, "autoload"));
    }

    public static function unregister()
    {
        return spl_autoload_unregister(array(__CLASS__, "autoload"));
    }
}


//APIAutoloader::register();
