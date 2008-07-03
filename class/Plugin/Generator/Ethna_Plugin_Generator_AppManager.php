<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_AppManager.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_AppManager
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_AppManager extends Ethna_Plugin_Generator
{
    /**
     *  アプリケーションマネージャのスケルトンを生成する
     *
     *  @access public
     *  @param  string  $manager_name    アプリケーションマネージ名
     *  @return bool    true:成功 false:失敗
     */
    function generate($manager_name)
    {
        $manager_id = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($manager_name));

        $app_dir = $this->ctl->getDirectory('app');
        $app_path = ucfirst($this->ctl->getAppId()) . '_' . $manager_id .'Manager.php';

        $macro = array();
        $macro['project_id'] = $this->ctl->getAppId();
        $macro['app_path'] = $app_path;
        $macro['app_manager'] = ucfirst($this->ctl->getAppId()) . '_' . $manager_id;

        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        $path = "$app_dir/$app_path";
        Ethna_Util::mkdir(dirname($path), 0755);
        if (file_exists($path)) {
            printf("file [%s] already exists -> skip\n", $path);
        } else if ($this->_generateFile("skel.app_manager.php", $path, $macro) == false) {
            printf("[warning] file creation failed [%s]\n", $path);
        } else {
            printf("app-manager script(s) successfully created [%s]\n", $path);
        }
    }
}
// }}}
?>
