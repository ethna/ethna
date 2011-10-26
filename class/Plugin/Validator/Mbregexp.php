<?php
// {{{ Ethna_Plugin_Validator_Mbegexp
/**
 *  マルチバイト対応正規表現によるバリデータプラグイン
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Mbregexp extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    public $accept_array = false;

    /**
     *  正規表現によるフォーム値のチェックを行う(マルチバイト対応）
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    public function validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['mbregexp']) == false
            || $type == VAR_TYPE_FILE || $this->isEmpty($var, $type)) {
            return $true;
        }

        $ctl = $this->backend->getController();
        $cli_enc = $ctl->getClientEncoding();
        $encoding = (isset($params['encoding']))
                  ? $params['encoding']
                  : $cli_enc;
        mb_regex_encoding($encoding);

        if (mb_ereg($params['mbregexp'], $var) !== 1) {
            if (isset($params['error'])) {
                $msg = $params['error'];
            } else {
                $msg = _et('Please input {form} properly.');
            }
            return Ethna::raiseNotice($msg, E_FORM_REGEXP);
        }

        return $true;
    }
}
// }}}
