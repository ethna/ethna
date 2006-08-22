<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_Project.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_Project
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_Project extends Ethna_Plugin_Generator
{
    /**
     *  プロジェクトスケルトンを生成する
     *
     *  @access public
     *  @param  string  $id         プロジェクトID
     *  @param  string  $basedir    プロジェクトベースディレクトリ
     *  @return bool    true:成功 false:失敗
     */
    function generate($id, $basedir)
    {
        $dir_list = array(
            array("app", 0755),
            array("app/action", 0755),
            array("app/action_cli", 0755),
            array("app/action_xmlrpc", 0755),
            array("app/filter", 0755),
            array("app/plugin", 0755),
            array("app/plugin/Filter", 0755),
            array("app/plugin/Validator", 0755),
            array("app/view", 0755),
            array("bin", 0755),
            array("etc", 0755),
            array("lib", 0755),
            array("locale", 0755),
            array("locale/ja", 0755),
            array("locale/ja/LC_MESSAGES", 0755),
            array("log", 0777),
            array("schema", 0755),
            array("skel", 0755),
            array("template", 0755),
            array("template/ja", 0755),
            array("tmp", 0777),
            array("www", 0755),
            array("www/css", 0755),
            array("www/js", 0755),
        );

        $r = Ethna_Controller::checkAppId($id);
        if (Ethna::isError($r)) {
            return $r;
        }

        $basedir = sprintf("%s/%s", $basedir, strtolower($id));

        // ディレクトリ作成
        if (is_dir($basedir) == false) {
            // confirm
            printf("creating directory ($basedir) [y/n]: ");
            flush();
            $fp = fopen("php://stdin", "r");
            $r = trim(fgets($fp, 128));
            fclose($fp);
            if (strtolower($r) != 'y') {
                return Ethna::raiseError('aborted by user');
            }

            if (mkdir($basedir, 0775) == false) {
                return Ethna::raiseError('directory creation failed');
            }
        }
        foreach ($dir_list as $dir) {
            $mode = $dir[1];
            $dir = $dir[0];
            $target = "$basedir/$dir";
            if (is_dir($target)) {
                printf("%s already exists -> skipping...\n", $target);
                continue;
            }
            if (mkdir($target, $mode) == false) {
                return Ethna::raiseError('directory creation failed');
            } else {
                printf("project sub directory created [%s]\n", $target);
            }
            if (chmod($target, $mode) == false) {
                return Ethna::raiseError('chmod failed');
            }
        }

        // スケルトンファイル作成
        $macro['application_id'] = strtoupper($id);
        $macro['project_id'] = ucfirst($id);
        $macro['project_prefix'] = strtolower($id);
        $macro['basedir'] = realpath($basedir);

        $macro['action_class'] = '{$action_class}';
        $macro['action_form'] = '{$action_form}';
        $macro['action_name'] = '{$action_name}';
        $macro['action_path'] = '{$action_path}';
        $macro['forward_name'] = '{$forward_name}';
        $macro['view_name'] = '{$view_name}';
        $macro['view_path'] = '{$view_path}';

        $user_macro = $this->_getUserMacro();
        $default_macro = $macro;
        $macro = array_merge($macro, $user_macro);

        // the longest if? :)
        if ($this->_generateFile("www.index.php", "$basedir/www/index.php", $macro) == false ||
            $this->_generateFile("www.info.php", "$basedir/www/info.php", $macro) == false ||
            $this->_generateFile("www.unittest.php", "$basedir/www/unittest.php", $macro) == false ||
            $this->_generateFile("www.xmlrpc.php", "$basedir/www/xmlrpc.php", $macro) == false ||
            $this->_generateFile("www.css.ethna.css", "$basedir/www/css/ethna.css", $macro) == false ||
            $this->_generateFile("dot.ethna", "$basedir/.ethna", $macro) == false ||
            $this->_generateFile("app.controller.php", sprintf("$basedir/app/%s_Controller.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("app.error.php", sprintf("$basedir/app/%s_Error.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("app.actionclass.php", sprintf("$basedir/app/%s_ActionClass.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("app.actionform.php", sprintf("$basedir/app/%s_ActionForm.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("app.viewclass.php", sprintf("$basedir/app/%s_ViewClass.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("app.action.default.php", "$basedir/app/action/Index.php", $macro) == false ||
            $this->_generateFile("app.plugin.filter.default.php", sprintf("$basedir/app/plugin/Filter/%s_Plugin_Filter_ExecutionTime.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("app.view.default.php", "$basedir/app/view/Index.php", $macro) == false ||
            $this->_generateFile("app.unittest.php", sprintf("$basedir/app/%s_UnitTestManager.php", $macro['project_id']), $macro) == false ||
            $this->_generateFile("etc.ini.php", sprintf("$basedir/etc/%s-ini.php", $macro['project_prefix']), $macro) == false ||
            $this->_generateFile("skel.action.php", sprintf("$basedir/skel/skel.action.php"), $default_macro) == false ||
            $this->_generateFile("skel.action_cli.php", sprintf("$basedir/skel/skel.action_cli.php"), $default_macro) == false ||
            $this->_generateFile("skel.action_test.php", sprintf("$basedir/skel/skel.action_test.php"), $default_macro) == false ||
            $this->_generateFile("skel.app_object.php", sprintf("$basedir/skel/skel.app_object.php"), $default_macro) == false ||
            $this->_generateFile("skel.cli.php", sprintf("$basedir/skel/skel.cli.php"), $default_macro) == false ||
            $this->_generateFile("skel.view.php", sprintf("$basedir/skel/skel.view.php"), $default_macro) == false ||
            $this->_generateFile("skel.template.tpl", sprintf("$basedir/skel/skel.template.tpl"), $default_macro) == false ||
            $this->_generateFile("skel.view_test.php", sprintf("$basedir/skel/skel.view_test.php"), $default_macro) == false ||
            $this->_generateFile("template.index.tpl", sprintf("$basedir/template/ja/index.tpl"), $default_macro) == false) {
            return Ethna::raiseError('generating files failed');
        }

        return true;
    }
}
// }}}
?>
