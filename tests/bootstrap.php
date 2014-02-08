<?php
define('ETHNA_INSTALL_BASE', dirname(dirname(__FILE__)));
define("__ETHNA_BASE", dirname(dirname(__FILE__)));
define("__ETHNA_PLUGIN_DIR", __ETHNA_BASE . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "Plugin");

ini_set("include_path", __ETHNA_BASE . DIRECTORY_SEPARATOR . ":". ini_get("include_path"));
require dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor/autoload.php";

new Prophecy\PhpUnit\ProphecyTestCase();


// smarty plugins
require_once __ETHNA_BASE . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array("class", "Plugin", "Smarty", "function.select.php"));
require_once __ETHNA_BASE . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array("class", "Plugin", "Smarty", "modifier.checkbox.php"));
require_once __ETHNA_BASE . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array("class", "Plugin", "Smarty", "modifier.explode.php"));
require_once __ETHNA_BASE . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array("class", "Plugin", "Smarty", "modifier.select.php"));
require_once __ETHNA_BASE . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array("class", "Plugin", "Smarty", "modifier.unique.php"));
require_once __ETHNA_BASE . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array("class", "Plugin", "Smarty", "modifier.wordwrap_i18n.php"));

spl_autoload_register(function($name){
    if (strpos($name, "Ethna") !== false) {
        $parts = explode("_", $name);
        array_shift($parts);
        $class_name = join("_", $parts);

        $path = __ETHNA_BASE . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $parts) . ".php";
        if (file_exists($path)) {
            require $path;
            return true;
        }

        $path = __ETHNA_BASE . DIRECTORY_SEPARATOR . "tests/brandnew/Ethna" . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $parts) . ".php";
        if (file_exists($path)) {
            require $path;
            return true;
        }

        $path = __ETHNA_BASE . DIRECTORY_SEPARATOR . "tests/legacy/Ethna" . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $parts) . ".php";
        if (file_exists($path)) {
            require $path;
            return true;
        }
    }
});