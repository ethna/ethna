<?php
// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
/**
 *  CacheManager.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  キャッシュマネージャクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_CacheManager
{
    /**
     *  Cachemaanger プラグインのインスタンスを取得する
     *
     *  @param  string  $type   キャッシュタイプ('localfile', 'memcache'...)
     *  @return object  Ethna_Plugin_CacheMaanger   Cachemanager プラグインのインスタンス
     *  @access public
     */
    public static function getInstance($type)
    {
        $controller = Ethna_Controller::getInstance();
        $plugin = $controller->getPlugin();

        $cache_manager = $plugin->getPlugin('Cachemanager', ucfirst($type));

        return $cache_manager;
    }
}
