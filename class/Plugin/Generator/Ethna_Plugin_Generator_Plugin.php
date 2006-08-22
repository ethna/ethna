<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_Plugin.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_Plugin
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_Plugin extends Ethna_Plugin_Generator
{
    /**
     *  プラグインを生成する
     *
     *  @access public
     *  @param  string  $type       プラグインの$type
     *  @param  string  $name       プラグインの$name
     *  @return bool    true:成功 false:失敗
     */
    function generate($type, $name, $app_dir)
    {
        // get application controller
        $c =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($c)) {
            return $c;
        }
        $appid = $c->getAppId();
        $plugin =& $c->getPlugin();

        list($class, $plugin_dir, $plugin_path) = $plugin->getPluginNaming($type, $name, $appid);

        $macro = array();
        $macro['project_id'] = $appid;
        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        Ethna_Handle::mkdir(dirname("$plugin_dir/$plugin_path"), 0755);

        if (file_exists("$plugin_dir/$plugin_path")) {
            printf("file [%s] already exists -> skip\n", "$plugin_dir/$plugin_path");
        } else if ($this->_generateFile("skel.plugin.{$type}_{$name}.php", "$plugin_dir/$plugin_path", $macro) == false) {
            printf("[warning] file creation failed [%s]\n", "$plugin_dir/$plugin_path");
        } else {
            printf("plugin script(s) successfully created [%s]\n", "$plugin_dir/$plugin_path");
        }
    }

    /**
     *  プラグインを消す
     *  TODO: もっといい方針を考える
     *
     *  @access public
     *  @param  string  $type       プラグインの$type
     *  @param  string  $name       プラグインの$name
     *  @return bool    true:成功 false:失敗
     */
    function remove($type, $name, $app_dir)
    {
        // get application controller
        $c =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($c)) {
            return $c;
        }
        $appid = $c->getAppId();
        $plugin =& $c->getPlugin();

        list($class, $plugin_dir, $plugin_path) = $plugin->getPluginNaming($type, $name, $appid);

        $macro = array();
        $macro['project_id'] = $appid;
        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        if (file_exists("$plugin_dir/$plugin_path")) {
            unlink("$plugin_dir/$plugin_path");
            printf("file [%s] successfully unlinked\n", "$plugin_dir/$plugin_path");
        } else {
            printf("file [%s] not found\n", "$plugin_dir/$plugin_path");
        }
    }
}
// }}}
?>
