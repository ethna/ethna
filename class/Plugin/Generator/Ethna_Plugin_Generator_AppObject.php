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
     *  @param  string  $app_dir        プロジェクトディレクトリ
     *  @return bool    true:成功 false:失敗
     */
    function generate($table_name, $app_dir)
    {
        // get application controller
        $c =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($c)) {
            return $c;
        }

        $table_id = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($table_name));

        $app_dir = $c->getDirectory('app');
        $app_path = ucfirst($c->getAppId()) . '_' . $table_id .'.php';

        $macro = array();
        $macro['project_id'] = $c->getAppId();
        $macro['app_path'] = $app_path;
        $macro['app_object'] = ucfirst($c->getAppId()) . '_' . $table_id;

        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        $path = "$app_dir/$app_path";
        Ethna_Handle::mkdir(dirname($path), 0755);
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
