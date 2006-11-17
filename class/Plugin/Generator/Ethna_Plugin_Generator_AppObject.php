<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_AppObject.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_AppObject
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_AppObject extends Ethna_Plugin_Generator
{
    /**
     *  アプリケーションオブジェクトのスケルトンを生成する
     *
     *  @access public
     *  @param  string  $table_name     テーブル名
     *  @return bool    true:成功 false:失敗
     */
    function generate($table_name)
    {
        $table_id = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($table_name));

        $app_dir = $this->ctl->getDirectory('app');
        $app_path = ucfirst($this->ctl->getAppId()) . '_' . $table_id .'.php';

        $macro = array();
        $macro['project_id'] = $this->ctl->getAppId();
        $macro['app_path'] = $app_path;
        $macro['app_object'] = ucfirst($this->ctl->getAppId()) . '_' . $table_id;

        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        $path = "$app_dir/$app_path";
        Ethna_Util::mkdir(dirname($path), 0755);
        if (file_exists($path)) {
            printf("file [%s] already exists -> skip\n", $path);
        } else if ($this->_generateFile("skel.app_object.php", $path, $macro) == false) {
            printf("[warning] file creation failed [%s]\n", $path);
        } else {
            printf("app-object script(s) successfully created [%s]\n", $path);
        }
    }
}
// }}}
?>
