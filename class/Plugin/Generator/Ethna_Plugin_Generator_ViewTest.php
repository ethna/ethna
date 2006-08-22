<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_ViewTest.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_ViewTest
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_ViewTest extends Ethna_Plugin_Generator
{
    /**
     *  ビュー用テストのスケルトンを生成する
     *
     *  @access public
     *  @param  string  $forward_name   アクション名
     *  @return bool    true:成功 false:失敗
     */
    function generate($forward_name, $app_dir)
    {
        // get application controller
        $c =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($c)) {
            return $c;
        }

        $view_dir = $c->getViewdir();
        $view_class = $c->getDefaultViewClass($forward_name, false);
        $view_path = $c->getDefaultViewPath($forward_name . "Test", false);

        $macro = array();
        $macro['project_id'] = $c->getAppId();
        $macro['forward_name'] = $forward_name;
        $macro['view_class'] = $view_class;
        $macro['view_path'] = $view_path;

        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        Ethna_Handle::mkdir(dirname("$view_dir/$view_path"), 0755);

        if (file_exists("$view_dir$view_path")) {
            printf("file [%s] aleady exists -> skip\n", "$view_dir$view_path");
        } else if ($this->_generateFile("skel.view_test.php", "$view_dir$view_path", $macro) == false) {
            printf("[warning] file creation failed [%s]\n", "$view_dir$view_path");
        } else {
            printf("view test(s) successfully created [%s]\n", "$view_dir$view_path");
        }
    }
}
// }}}
?>
