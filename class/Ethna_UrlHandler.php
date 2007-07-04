<?php
// vim: foldmethod=marker
/**
 *  Ethna_UrlHandler.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  URLハンドラクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_UrlHandler
{
    /**
     *  Ethna_UrlHandlerクラスのインスタンスを取得する
     *  (only proxy for plugin)
     *
     * @param string $class_name just ignored (DEPRECATED)
     * @param string $type type of urlhandler plugin
     * @access public
     */
    function &getInstance($class_name = null)
    {
        $controller = &Ethna_Controller::getInstance();
        if ($class_name === null || strpos($class_name, '_') !== false) {
            // for B.C.
            $plugin_name = 'Default';
        } else {
            $plugin_name = $class_name;
        }
        $plugin = &$controller->getPlugin();
        $url_handler = &$plugin->getPlugin('Urlhandler', $plugin_name);
        return $url_handler;
    }
}

?>
