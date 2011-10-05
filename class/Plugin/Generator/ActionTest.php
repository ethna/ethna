<?php
// vim: foldmethod=marker
/**
 *  ActionTest.php
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
     *  (現在のところ GATEWAY_WWW のみ対応)
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @param  string  $skelton        スケルトンファイル名
     *  @param  int     $gateway        ゲートウェイ
     *  @return true|Ethna_Error        true:成功 Ethna_Error:失敗
     */
    function generate($action_name, $skelton = null, $gateway = GATEWAY_WWW)
    {
        $action_dir = $this->ctl->getActiondir($gateway);
        $action_class = $this->ctl->getDefaultActionClass($action_name, $gateway);
        $action_form = $this->ctl->getDefaultFormClass($action_name, $gateway);
        $action_path = $this->ctl->getDefaultActionPath($action_name . 'Test');

        // entity
        $entity = $action_dir . $action_path;
        Ethna_Util::mkdir(dirname($entity), 0755);

        // skelton
        if ($skelton === null) {
            $skelton = 'skel.action_test.php';
        }

        // macro
        $macro = array();
        $macro['project_id'] = $this->ctl->getAppId();
        $macro['action_name'] = $action_name;
        $macro['action_class'] = $action_class;
        $macro['action_form'] = $action_form;
        $macro['action_path'] = $action_path;

        // user macro
        $user_macro = $this->_getUserMacro();
        $macro = array_merge($macro, $user_macro);

        // original action script existence check.
        $original_action_path = $this->ctl->getDefaultActionPath($action_name);
        $original_action_entity = $action_dir . $original_action_path;
        if (!file_exists($original_action_entity)) {
            printf("\n");
            printf("[!!!!warning!!!!] original action script was not found.\n");
            printf("[!!!!warning!!!!] You must generate it by the following command :\n");
            printf("[!!!!warning!!!!] ethna add-action %s\n\n", $action_name);
        } 

        // generate
        if (file_exists($entity)) {
            printf("file [%s] already exists -> skip\n", $entity);
        } else if ($this->_generateFile($skelton, $entity, $macro) == false) {
            printf("[warning] file creation failed [%s]\n", $entity);
        } else {
            printf("action test(s) successfully created [%s]\n", $entity);
        }

        $true = true;
        return $true;
    }
}
// }}}
