<?php
// vim: foldmethod=marker
/**
 *  Regexp.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Regexp
/**
 *  正規表現によるバリデータプラグイン
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Regexp extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    public $accept_array = false;

    /**
     *  正規表現によるフォーム値のチェックを行う
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
        if (isset($params['regexp']) == false
            || $type == VAR_TYPE_FILE || $this->isEmpty($var, $type)) {
            return $true;
        }

        if (preg_match($params['regexp'], $var) == 0) {
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
