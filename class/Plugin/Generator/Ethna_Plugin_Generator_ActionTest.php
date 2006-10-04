<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_ActionTest.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_ActionTest
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_ActionTest extends Ethna_Plugin_Generator
{
    /**
     *  アクション用テストのスケルトンを生成する
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @param  string  $app_dir        プロジェクトディレクトリ
     *  @return bool    true:成功 false:失敗
     */
    function generate($action_name, $app_dir, $gateway = GATEWAY_WWW)
    {
        // get application controller
        $c =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($c)) {
            return $c;
        }

        $action_dir = $c->getActiondir($gateway);
        $action_class = $c->getDefaultActionClass($action_name, false);
        $action_form = $c->getDefaultFormClass($action_name, false);
        $action_path = $c->getDefaultActionPath($action_name . "Test", false);

        $macro = array();
        $macro['project_id'] = $c->getAppId();
        $macro['action_name'] = $action_name;
        $macro['action_class'] = $action_class;
        $macro['action_form'] = $action_form;
        $macro['action_path'] = $action_path;

        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        Ethna_Util::mkdir(dirname("$action_dir$action_path"), 0755);

        if (file_exists("$action_dir$action_path")) {
            printf("file [%s] aleady exists -> skip\n", "$action_dir$action_path");
        } else if ($this->_generateFile("skel.action_test.php", "$action_dir$action_path", $macro) == false) {
            printf("[warning] file creation failed [%s]\n", "$action_dir$action_path");
        } else {
            printf("action test(s) successfully created [%s]\n", "$action_dir$action_path");
        }
    }
}
// }}}
?>
