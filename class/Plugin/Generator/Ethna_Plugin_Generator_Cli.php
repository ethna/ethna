<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_Cli.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_Cli
/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_Cli extends Ethna_Plugin_Generator
{
    /**
     *  CLIエントリポイントのスケルトンを生成する
     *
     *  @access public
     *  @param  string  $forward_name   アクション名
     *  @param  string  $app_dir        プロジェクトディレクトリ
     *  @return bool    true:成功 false:失敗
     */
    function generate($action_name, $app_dir)
    {
        // get application controller
        $c =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($c)) {
            return $c;
        }
        $this->ctl =& $c;

        $app_dir = $c->getDirectory('app');
        $bin_dir = $c->getDirectory('bin');
        $cli_file = sprintf("%s/%s.%s", $bin_dir, $action_name, $c->getExt('php'));

        $macro = array();
        $macro['project_id'] = $c->getAppId();
        $macro['action_name'] = $action_name;
        $macro['dir_app'] = $app_dir;
        $macro['dir_bin'] = $bin_dir;

        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        if (file_exists($cli_file)) {
            printf("file [%s] already exists -> skip\n", $cli_file);
        } else if ($this->_generateFile("skel.cli.php", $cli_file, $macro) == false) {
            printf("[warning] file creation failed [%s]\n", $cli_file);
        } else {
            printf("action script(s) successfully created [%s]\n", $cli_file);
        }
    }
}
// }}}
?>
