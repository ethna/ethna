<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/** 定型フィルタ: 半角入力 */
define('FILTER_HW', 'numeric_zentohan,alphabet_zentohan,ltrim,rtrim,ntrim');

/** 定型フィルタ: 全角入力 */
define('FILTER_FW', 'kana_hantozen,ntrim');


// {{{ Ethna_ActionForm
/**
 *  アクションフォームクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_ActionForm
{
    /**#@+
     *  @access private
     */

    /** @var    array   フォーム値定義(デフォルト) */
    var $form_template = array();

    /** @var    array   フォーム値定義 */
    var $form = array();

    /** @var    array   フォーム値 */
    var $form_vars = array();

    /** @var    array   アプリケーション設定値 */
    var $app_vars = array();

    /** @var    array   アプリケーション設定値(自動エスケープなし) */
    var $app_ne_vars = array();

    /** @var    object  Ethna_Backend       バックエンドオブジェクト */
    var $backend;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト */
    var $action_error;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト(省略形) */
    var $ae;

    /** @var    object  Ethna_I18N  i18nオブジェクト */
    var $i18n;

    /** @var    object  Ethna_Logger    ログオブジェクト */
    var $logger;

    /** @var    object  Ethna_Plugin    プラグインオブジェクト */
    var $plugin;

    /** @var    array   フォーム定義要素 */
    var $def = array('name', 'required', 'max', 'min', 'regexp', 'mbregexp',
                     'custom', 'filter', 'form_type', 'type');

    /** @var    array   フォーム定義のうち非プラグイン要素とみなすprefix */
    var $def_noplugin = array('type', 'form', 'name', 'plugin', 'filter',
                              'option', 'default');

    /** @var    bool    追加検証強制フラグ */
    var $force_validate_plus = false;

    /** @var    array   アプリケーションオブジェクト(helper) */
    var $helper_app_object = array();

    /** @var    array   アプリケーションオブジェクト(helper)で利用しないフォーム名 */
    var $helper_skip_form = array();

    /**#@-*/

    /**
     *  Ethna_ActionFormクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    controllerオブジェクト
     */
    function Ethna_ActionForm(&$controller)
    {
        $this->backend =& $controller->getBackend();
        $this->action_error =& $controller->getActionError();
        $this->ae =& $this->action_error;
        $this->i18n =& $controller->getI18N();
        $this->logger =& $controller->getLogger();
        $this->plugin =& $controller->getPlugin();

        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            return;
        }

        // フォーム値テンプレートの更新
        $this->form_template = $this->_setFormTemplate($this->form_template);

        // アプリケーションオブジェクト(helper)の生成
        foreach ($this->helper_app_object as $key => $value) {
            if (is_object($value)) {
                continue;
            }
            $this->helper_app_object[$key] =& $this->_getHelperAppObject($key);
        }

        // フォーム値定義の設定
        $this->_setFormDef_Helper();
        $this->_setFormDef();

        // 省略値補正
        foreach ($this->form as $name => $value) {
            foreach ($this->def as $k) {
                if (isset($value[$k]) == false) {
                    $this->form[$name][$k] = null;
                }
            }
        }
    }

    /**
     *  フォーム値のアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   フォーム値の名称
     *  @return mixed   フォーム値
     */
    function get($name)
    {
        if (isset($this->form_vars[$name])) {
            return $this->form_vars[$name];
        }
        return null;
    }

    /**
     *  フォーム値定義を取得する
     *
     *  @access public
     *  @param  string  $name   取得するフォーム名(nullなら全ての定義を取得)
     *  @return array   フォーム値定義
     */
    function getDef($name = null)
    {
        if (is_null($name)) {
            return $this->form;
        }

        if (array_key_exists($name, $this->form) == false) {
            return null;
        } else {
            return $this->form[$name];
        }
    }

    /**
     *  フォーム項目表示名を取得する
     *
     *  @access public
     *  @param  string  $name   フォーム値の名称
     *  @return mixed   フォーム値の表示名
     */
    function getName($name)
    {
        if (isset($this->form[$name]) == false) {
            return null;
        }
        if (isset($this->form[$name]['name'])
            && $this->form[$name]['name'] != null) {
            return $this->form[$name]['name'];
        }

        // try message catalog
        return $this->i18n->get($name);
    }

    /**
     *  ユーザから送信されたフォーム値をフォーム値定義に従ってインポートする
     *
     *  @access public
     *  @todo   多次元の配列への対応
     */
    function setFormVars()
    {
        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            return;
        } else if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
            $http_vars =& $_POST;
        } else {
            $http_vars =& $_GET;
        }

        //
        //  ethna_fid というフォーム値は、フォーム定義に関わらず受け入れる
        //  これは、submitされたフォームを識別するために使われる
        //  null の場合は、以下の場合である
        //
        //  1. フォームヘルパが使われていない
        //  2. 画面の初期表示などで、submitされなかった
        //  3. {form name=...} が未設定である
        //
        $this->form_vars['ethna_fid'] = (isset($http_vars['ethna_fid']) == false
                                      || is_null($http_vars['ethna_fid']))
                                      ? null
                                      : $http_vars['ethna_fid'];

        foreach ($this->form as $name => $def) {
            $type = is_array($def['type']) ? $def['type'][0] : $def['type'];
            if ($type == VAR_TYPE_FILE) {
                // ファイルの場合

                // 値の有無の検査
                if (isset($_FILES[$name]) == false || is_null($_FILES[$name])) {
                    $this->form_vars[$name] = null;
                    continue;
                }

                // 配列構造の検査
                if (is_array($def['type'])) {
                    if (is_array($_FILES[$name]['tmp_name']) == false) {
                        $this->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                } else {
                    if (is_array($_FILES[$name]['tmp_name'])) {
                        $this->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                }

                $files = null;
                if (is_array($def['type'])) {
                    $files = array();
                    // ファイルデータを再構成
                    foreach (array_keys($_FILES[$name]['name']) as $key) {
                        $files[$key] = array();
                        $files[$key]['name'] = $_FILES[$name]['name'][$key];
                        $files[$key]['type'] = $_FILES[$name]['type'][$key];
                        $files[$key]['size'] = $_FILES[$name]['size'][$key];
                        $files[$key]['tmp_name'] = $_FILES[$name]['tmp_name'][$key];
                        if (isset($_FILES[$name]['error']) == false) {
                            // PHP 4.2.0 以前
                            $files[$key]['error'] = 0;
                        } else {
                            $files[$key]['error'] = $_FILES[$name]['error'][$key];
                        }
                    }
                } else {
                    $files = $_FILES[$name];
                    if (isset($files['error']) == false) {
                        $files['error'] = 0;
                    }
                }

                // 値のインポート
                $this->form_vars[$name] = $files;

            } else {
                // ファイル以外の場合

                // 値の有無の検査
                if (isset($http_vars[$name]) == false
                    || is_null($http_vars[$name])) {
                    $this->form_vars[$name] = null;
                    if (isset($http_vars["{$name}_x"])
                        && isset($http_vars["{$name}_y"])) {
                        // 以前の仕様に合わせる
                        $this->form_vars[$name] = $http_vars["{$name}_x"];
                    }
                    continue;
                }

                // 配列構造の検査
                if (is_array($def['type'])) {
                    if (is_array($http_vars[$name]) == false) {
                        // 厳密には、この配列の各要素はスカラーであるべき
                        $this->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                } else {
                    if (is_array($http_vars[$name])) {
                        $this->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                }

                // 値のインポート
                $this->form_vars[$name] = $http_vars[$name];
            }
        }
    }

    /**
     *  ユーザから送信されたフォーム値をクリアする
     *
     *  @access public
     */
    function clearFormVars()
    {
        $this->form_vars = array();
    }

    /**
     *  フォーム値へのアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   フォーム値の名称
     *  @param  string  $value  設定する値
     */
    function set($name, $value)
    {
        $this->form_vars[$name] = $value;
    }

    /**
     *  フォーム値定義を設定する
     *
     *  @access public
     *  @param  string  $name   設定するフォーム名(nullなら全ての定義を設定)
     *  @param  array   $value  設定するフォーム値定義
     *  @return array   フォーム値定義
     */
    function setDef($name, $value)
    {
        if (is_null($name)) {
            $this->form = $value;
        }

        $this->form[$name] = $value;
    }

    /**
     *  フォーム値を配列にして返す
     *
     *  @access public
     *  @param  bool    $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    function &getArray($escape = true)
    {
        $retval = array();

        $this->_getArray($this->form_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  アプリケーション設定値のアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @return mixed   アプリケーション設定値
     */
    function getApp($name)
    {
        if (isset($this->app_vars[$name]) == false) {
            return null;
        }
        return $this->app_vars[$name];
    }

    /**
     *  アプリケーション設定値のアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @param  mixed   $value  値
     */
    function setApp($name, $value)
    {
        $this->app_vars[$name] = $value;
    }

    /**
     *  アプリケーション設定値を配列にして返す
     *
     *  @access public
     *  @param  boolean $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    function &getAppArray($escape = true)
    {
        $retval = array();

        $this->_getArray($this->app_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  アプリケーション設定値(自動エスケープなし)のアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @return mixed   アプリケーション設定値
     */
    function getAppNE($name)
    {
        if (isset($this->app_ne_vars[$name]) == false) {
            return null;
        }
        return $this->app_ne_vars[$name];
    }

    /**
     *  アプリケーション設定値(自動エスケープなし)のアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @param  mixed   $value  値
     */
    function setAppNE($name, $value)
    {
        $this->app_ne_vars[$name] = $value;
    }

    /**
     *  アプリケーション設定値(自動エスケープなし)を配列にして返す
     *
     *  @access public
     *  @param  boolean $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    function &getAppNEArray($escape = false)
    {
        $retval = array();

        $this->_getArray($this->app_ne_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  フォームを配列にして返す(内部処理)
     *
     *  @access private
     *  @param  array   &$vars      フォーム(等)の配列
     *  @param  array   &$retval    配列への変換結果
     *  @param  bool    $escape     HTMLエスケープフラグ(true:エスケープする)
     */
    function _getArray(&$vars, &$retval, $escape)
    {
        foreach (array_keys($vars) as $name) {
            if (is_array($vars[$name])) {
                $retval[$name] = array();
                $this->_getArray($vars[$name], $retval[$name], $escape);
            } else {
                $retval[$name] = $escape
                    ? htmlspecialchars($vars[$name], ENT_QUOTES) : $vars[$name];
            }
        }
    }

    /**
     *  追加検証強制フラグを取得する
     *  (通常検証でエラーが発生した場合でも_validatePlus()が呼び出される)
     *  @access public
     *  @return bool    true:追加検証強制 false:追加検証非強制
     */
    function isForceValidatePlus()
    {
        return $this->force_validate_plus;
    }

    /**
     *  追加検証強制フラグを設定する
     *
     *  @access public
     *  @param  $force_validate_plus    追加検証強制フラグ
     */
    function setForceValidatePlus($force_validate_plus)
    {
        $this->force_validate_plus = $force_validate_plus;
    }

    /**
     *  フォーム値検証メソッド
     *
     *  @access public
     *  @return int     発生したエラーの数
     */
    function validate()
    {
        foreach ($this->form as $name => $def) {
            $this->_validateWithPlugin($name);
        }

        if ($this->ae->count() == 0 || $this->isForceValidatePlus()) {
            // 追加検証メソッド
            $this->_validatePlus();
        }

        return $this->ae->count();
    }

    /**
     *  プラグインを使ったフォーム値検証メソッド
     *
     *  @access private
     *  @param  string  $form_name  フォームの名前
     *  @todo   ae 側に $key を与えられるようにする
     */
    function _validateWithPlugin($form_name)
    {
        // (pre) filter
        if ($this->form[$form_name]['type'] != VAR_TYPE_FILE) {
            
            //    入力値とフィルタ定義を取り出す
            $form_var = (isset($this->form_vars[$form_name]))
                      ? $this->form_vars[$form_name]
                      : null;
            $filter = (isset($this->form[$form_name]['filter']))
                    ? $this->form[$form_name]['filter']
                    : null;

            //    フィルタを適用
            if (is_array($this->form[$form_name]['type']) == false) {
                $this->form_vars[$form_name]
                    = $this->_filter($form_var, $filter);
            } else if ($form_var != null) {  //  配列の場合
                foreach (array_keys($this->form_vars[$form_name]) as $key) {
                    $this->form_vars[$form_name][$key]
                        = $this->_filter($this->form_vars[$form_name][$key], $filter);
                }
            } else {  //  配列で値が空の場合
                $this->form_vars[$form_name]
                    = $this->_filter($form_var, $filter);
            }
        }

        $form_vars = $this->form_vars[$form_name];
        $plugin = $this->_getPluginDef($form_name);

        // type のチェックを処理の最初に追加
        $plugin = array_merge(array('type' => array()), $plugin);
        if (is_array($this->form[$form_name]['type'])) {
            $plugin['type']['type'] = $this->form[$form_name]['type'][0];
        } else {
            $plugin['type']['type'] = $this->form[$form_name]['type'];
        }
        if (isset($this->form[$form_name]['type_error'])) {
            $plugin['type']['error'] = $this->form[$form_name]['type_error'];
        }

        // スカラーの場合
        if (is_array($this->form[$form_name]['type']) == false) {
            foreach (array_keys($plugin) as $name) {
                // break: 明示されていなければ，エラーが起きたらvalidateを継続しない
                $break = isset($plugin[$name]['break']) == false
                               || $plugin[$name]['break'];

                // プラグイン取得
                unset($v);
                $v =& $this->plugin->getPlugin('Validator',
                                               ucfirst(strtolower($name)));
                if (Ethna::isError($v)) {
                    continue;
                }

                // バリデーション実行
                unset($r);
                $r =& $v->validate($form_name, $form_vars, $plugin[$name]);

                // エラー処理
                if ($r !== true) {
                    if (Ethna::isError($r)) {
                        $this->ae->addObject($form_name, $r);
                    }
                    if ($break) {
                        break;
                    }
                }
            }
            return;
        }

        // 配列の場合

        // break 指示用の key list
        $valid_keys = is_array($form_vars) ? array_keys($form_vars) : array();

        foreach (array_keys($plugin) as $name) {
            // break: 明示されていなければ，エラーが起きたらvalidateを継続しない
            $break = isset($plugin[$name]['break']) == false
                           || $plugin[$name]['break'];

            // プラグイン取得
            unset($v);
            $v =& $this->plugin->getPlugin('Validator', ucfirst(strtolower($name)));
            if (Ethna::isError($v)) {
                continue;
            }

            // 配列全体を受け取るプラグインの場合
            if (isset($v->accept_array) && $v->accept_array == true) {
                // バリデーション実行
                unset($r);
                $r =& $v->validate($form_name, $form_vars, $plugin[$name]);

                // エラー処理
                if (Ethna::isError($r)) {
                    $this->ae->addObject($form_name, $r);
                    if ($break) {
                        break;
                    }
                }
                continue;
            }

            // 配列の各要素に対して実行
            foreach ($valid_keys as $key) {
                // バリデーション実行
                unset($r);
                $r =& $v->validate($form_name, $form_vars[$key], $plugin[$name]);

                // エラー処理
                if (Ethna::isError($r)) {
                    $this->ae->addObject($form_name, $r);
                    if ($break) {
                        unset($valid_keys[$key]);
                    }
                }
            }
        }
    }

    /**
     *  チェックメソッド(基底クラス)
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return array   チェック対象のフォーム値(エラーが無い場合はnull)
     */
    function check($name)
    {
        if (is_null($this->form_vars[$name]) || $this->form_vars[$name] === "") {
            return null;
        }

        // Ethna_Backendの設定
        $c =& Ethna_Controller::getInstance();
        $this->backend =& $c->getBackend();

        return to_array($this->form_vars[$name]);
    }

    /**
     *  チェックメソッド: 機種依存文字
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkVendorChar($name)
    {
        $null = null;
        $string = $this->form_vars[$name];

        for ($i = 0; $i < strlen($string); $i++) {
            /* JIS13区のみチェック */
            $c = ord($string{$i});
            if ($c < 0x80) {
                /* ASCII */
            } else if ($c == 0x8e) {
                /* 半角カナ */
                $i++;
            } else if ($c == 0x8f) {
                /* JIS X 0212 */
                $i += 2;
            } else if ($c == 0xad || ($c >= 0xf9 && $c <= 0xfc)) {
                /* IBM拡張文字 / NEC選定IBM拡張文字 */
                return $this->ae->add($name,
                    _et('{form} contains machine dependent code.'), E_FORM_INVALIDCHAR);
            } else {
                $i++;
            }
        }

        return $null;
    }

    /**
     *  チェックメソッド: bool値
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkBoolean($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if ($v != "0" && $v != "1") {
                return $this->ae->add($name,
                    _et('Please input {form} properly.'), E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  チェックメソッド: メールアドレス
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkMailaddress($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if (Ethna_Util::checkMailaddress($v) == false) {
                return $this->ae->add($name,
                    _et('Please input {form} properly.'), E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  チェックメソッド: URL
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkURL($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if (preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/', $v) == 0) {
                return $this->ae->add($name,
                    _et('Please input {form} properly.'), E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  フォーム値をhiddenタグとして返す
     *
     *  @access public
     *  @param  array   $include_list   配列が指定された場合、その配列に含まれるフォーム項目のみが対象となる
     *  @param  array   $exclude_list   配列が指定された場合、その配列に含まれないフォーム項目のみが対象となる
     *  @return string  hiddenタグとして記述されたHTML
     */
    function getHiddenVars($include_list = null, $exclude_list = null)
    {
        $hidden_vars = "";
        foreach ($this->form as $key => $value) {
            if (is_array($include_list) == true
                && in_array($key, $include_list) == false) {
                continue;
            }
            if (is_array($exclude_list) == true
                && in_array($key, $exclude_list) == true) {
                continue;
            }
            
            $type = is_array($value['type']) ? $value['type'][0] : $value['type'];
            if ($type == VAR_TYPE_FILE) {
                continue;
            }

            $form_value = $this->form_vars[$key];
            if (is_array($value['type'])) {
                $form_array = true;
            } else {
                $form_value = array($form_value);
                $form_array = false;
            }

            if (is_null($this->form_vars[$key])) {
                // フォーム値が送られていない場合はそもそもhiddenタグを出力しない
                continue;
            }

            foreach ($form_value as $k => $v) {
                if ($form_array) {
                    $form_name = "$key" . "[$k]";
                } else {
                    $form_name = $key;
                }
                $hidden_vars .=
                    sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n",
                    $form_name, htmlspecialchars($v, ENT_QUOTES));
            }
        }
        return $hidden_vars;
    }

    /**
     *  フォーム値検証のエラー処理を行う
     *
     *  @access public
     *  @param  string      $name   フォーム項目名
     *  @param  int         $code   エラーコード
     */
    function handleError($name, $code)
    {
        $def = $this->getDef($name);

        // ユーザ定義エラーメッセージ
        $code_map = array(
            E_FORM_REQUIRED     => 'required_error',
            E_FORM_WRONGTYPE_SCALAR => 'type_error',
            E_FORM_WRONGTYPE_ARRAY  => 'type_error',
            E_FORM_WRONGTYPE_INT    => 'type_error',
            E_FORM_WRONGTYPE_FLOAT  => 'type_error',
            E_FORM_WRONGTYPE_DATETIME   => 'type_error',
            E_FORM_WRONGTYPE_BOOLEAN    => 'type_error',
            E_FORM_MIN_INT      => 'min_error',
            E_FORM_MIN_FLOAT    => 'min_error',
            E_FORM_MIN_DATETIME => 'min_error',
            E_FORM_MIN_FILE     => 'min_error',
            E_FORM_MIN_STRING   => 'min_error',
            E_FORM_MAX_INT      => 'max_error',
            E_FORM_MAX_FLOAT    => 'max_error',
            E_FORM_MAX_DATETIME => 'max_error',
            E_FORM_MAX_FILE     => 'max_error',
            E_FORM_MAX_STRING   => 'max_error',
            E_FORM_REGEXP       => 'regexp_error',
        );
        //   フォーム定義にエラーメッセージが定義されていれば
        //   それを使う
        if (array_key_exists($code_map[$code], $def)) {
            $this->ae->add($name, $def[$code_map[$code]], $code);
            return;
        }

        //   定義されていない場合は、内部のメッセージを使う
        if ($code == E_FORM_REQUIRED) {
            switch ($def['form_type']) {
            case FORM_TYPE_TEXT:
            case FORM_TYPE_PASSWORD:
            case FORM_TYPE_TEXTAREA:
            case FORM_TYPE_SUBMIT:
                $message = _et('Please input {form}.');
                break;
            case FORM_TYPE_SELECT:
            case FORM_TYPE_RADIO:
            case FORM_TYPE_CHECKBOX:
            case FORM_TYPE_FILE:
                $message = _et('Please select {form}.');
                break;
            default:
                $message = _et('Please input {form}.');
                break;
            }
        } else if ($code == E_FORM_WRONGTYPE_SCALAR) {
            $message = _et('Please input scalar value to {form}.');
        } else if ($code == E_FORM_WRONGTYPE_ARRAY) {
            $message = _et('Please input array value to {form}.');
        } else if ($code == E_FORM_WRONGTYPE_INT) {
            $message = _et('Please input integer value to {form}.');
        } else if ($code == E_FORM_WRONGTYPE_FLOAT) {
            $message = _et('Please input float value to {form}.');
        } else if ($code == E_FORM_WRONGTYPE_DATETIME) {
            $message = _et('Please input valid datetime to {form}.');
        } else if ($code == E_FORM_WRONGTYPE_BOOLEAN) {
            $message = _et('You can input 0 or 1 to {form}.');
        } else if ($code == E_FORM_MIN_INT) {
            $this->ae->add($name,
                _et('Please input more than %d(int) to {form}.'),
                $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_FLOAT) {
            $this->ae->add($name,
                _et('Please input more than %f(float) to {form}.'),
                $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_DATETIME) {
            $this->ae->add($name,
                _et('Please input datetime value %s or later to {form}.'),
                $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_FILE) {
            $this->ae->add($name,
                _et('Please specify file whose size is more than %d KB.'), 
                $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_STRING) {
            $this->ae->add($name,
                _et('Please input more than %d full-size (%d half-size) characters to {form}.'),
                $code, intval($def['min']/2), $def['min']);
            return;
        } else if ($code == E_FORM_MAX_INT) {
            $this->ae->add($name,
                _et('Please input less than %d(int) to {form}.'),
                $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_FLOAT) {
            $this->ae->add($name,
                _et('Please input less than %f(float) to {form}.'),
                $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_DATETIME) {
            $this->ae->add($name,
                _et('Please input datetime value before %s to {form}.'),
                $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_FILE) {
            $this->ae->add($name,
                _et('Please specify file whose size is less than %d KB to {form}.'), 
                $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_STRING) {
            $this->ae->add($name,
                _et('Please input less than %d full-size (%d half-size) characters to {form}.'),
                $code, intval($def['max']/2), $def['max']);
            return;
        } else if ($code == E_FORM_REGEXP) {
            $message = _et('Please input {form} properly.');
        }

        $this->ae->add($name, $message, $code);
    }

    /**
     *  ユーザ定義検証メソッド(フォーム値間の連携チェック等)
     *
     *  @access protected
     */
    function _validatePlus()
    {
    }

    /**
     *  カスタムチェックメソッドを実行する
     *
     *  @access protected
     *  @param  string  $method_list    カスタムメソッド名(カンマ区切り)
     *  @param  string  $name           フォーム項目名
     */
    function _validateCustom($method_list, $name)
    {
        $method_list = preg_split('/\s*,\s*/', $method_list,
                                  -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($method_list) == false) {
            return;
        }
        foreach ($method_list as $method) {
            $this->$method($name);
        }
    }

    /**
     *  フォーム値に変換フィルタを適用する
     *
     *  @access private
     *  @param  mixed   $value  フォーム値
     *  @param  int     $filter フィルタ定義
     *  @return mixed   変換結果
     */
    function _filter($value, $filter)
    {
        if (is_null($filter)) {
            return $value;
        }

        foreach (preg_split('/\s*,\s*/', $filter) as $f) {
            $method = sprintf('_filter_%s', $f);
            if (method_exists($this, $method) == false) {
                $this->logger->log(LOG_WARNING,
                    'filter method is not defined [%s]', $method);
                continue;
            }
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     *  フォーム値変換フィルタ: 全角英数字->半角英数字
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_alnum_zentohan($value)
    {
        return mb_convert_kana($value, "a");
    }

    /**
     *  フォーム値変換フィルタ: 全角数字->半角数字
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_numeric_zentohan($value)
    {
        return mb_convert_kana($value, "n");
    }

    /**
     *  フォーム値変換フィルタ: 全角英字->半角英字
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_alphabet_zentohan($value)
    {
        return mb_convert_kana($value, "r");
    }

    /**
     *  フォーム値変換フィルタ: 左空白削除
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_ltrim($value)
    {
        return ltrim($value);
    }

    /**
     *  フォーム値変換フィルタ: 右空白削除
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_rtrim($value)
    {
        return rtrim($value);
    }

    /**
     *  フォーム値変換フィルタ: NULL(0x00)削除
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_ntrim($value)
    {
        return str_replace("\x00", "", $value);
    }

    /**
     *  フォーム値変換フィルタ: 半角カナ->全角カナ
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_kana_hantozen($value)
    {
        return mb_convert_kana($value, "K");
    }

    /**
     *  フォーム値定義テンプレートを設定する
     *
     *  @access protected
     *  @param  array   $form_template  フォーム値テンプレート
     *  @return array   フォーム値テンプレート
     */
    function _setFormTemplate($form_template)
    {
        return $form_template;
    }

    /**
     *  ヘルパオブジェクト経由でのフォーム値定義を設定する
     *
     *  @access protected
     */
    function _setFormDef_Helper()
    {
        foreach (array_keys($this->helper_app_object) as $key) {
            $object =& $this->helper_app_object[$key];
            $prop_def = $object->getDef();

            foreach ($prop_def as $key => $value) {
                // 1. override form_template
                $form_key = isset($value['form_name']) ? $value['form_name'] : $key;

                if (isset($this->form_template[$form_key]) == false) {
                    $this->form_template[$form_key] = array();
                }

                $this->form_template[$form_key]['type'] = $value['type'];
                if (isset($value['required'])) {
                    $this->form_template[$form_key]['required'] = $value['required'];
                }

                if ($value['type'] == VAR_TYPE_STRING && isset($value['length'])) {
                    $this->form_template[$form_key]['max'] = $value['length'];
                }

                // 2. then activate form
                if (in_array($key, $this->helper_skip_form) == false) {
                    if (isset($this->form[$key]) == false) {
                        $this->form[$key] = array();
                    }
                }
            }
        }
    }

    /**
     *  フォーム値定義を設定する
     *
     *  @access protected
     */
    function _setFormDef()
    {
        foreach ($this->form as $key => $value) {
            if (array_key_exists($key, $this->form_template)
                && is_array($this->form_template)) {
                foreach ($this->form_template[$key] as $def_key => $def_value) {
                    if (array_key_exists($def_key, $value) == false) {
                        $this->form[$key][$def_key] = $def_value;
                    }
                }
            }
        }
    }

    /**
     *  フォーム値定義からプラグインの定義リストを分離する
     *
     *  @access protected
     *  @param  string  $form_name   プラグインの定義リストを取得するフォームの名前
     */
    function _getPluginDef($form_name)
    {
        //  $def = array(
        //               'name'         => 'number',
        //               'max'          => 10,
        //               'max_error'    => 'too large!',
        //               'min'          => 5,
        //               'min_error'    => 'too small!',
        //              );
        //
        // as plugin parameters:
        //
        //  $plugin_def = array(
        //                      'max' => array('max' => 10, 'error' => 'too large!'),
        //                      'min' => array('min' => 5, 'error' => 'too small!'),
        //                     );

        $def = $this->getDef($form_name);
        $plugin = array();
        foreach (array_keys($def) as $key) {
            // 未定義要素をスキップ
            if ($def[$key] === null) {
                continue;
            }

            // プラグイン名とパラメータ名に分割
            $snippet = explode('_', $key, 2);
            $name = $snippet[0];

            // 非プラグイン要素をスキップ
            if (in_array($name, $this->def_noplugin)) {
                continue;
            }

            if (count($snippet) == 1) {
                // プラグイン名だけだった場合
                if (is_array($def[$key])) {
                    // プラグインパラメータがあらかじめ配列で指定されている(とみなす)
                    $tmp = $def[$key];
                } else {
                    $tmp = array($name => $def[$key]);
                }
            } else {
                // plugin_param の場合
                $tmp = array($snippet[1] => $def[$key]);
            }

            // merge
            if (isset($plugin[$name]) == false) {
                $plugin[$name] = array();
            }
            $plugin[$name] = array_merge($plugin[$name], $tmp);
        }

        return $plugin;
    }

    /**
     *  アプリケーションオブジェクト(helper)を生成する
     *
     *  @access protected
     */
    function &_getHelperAppObject($key)
    {
        $app_object =& $this->backend->getObject($key);
        return $app_object;
    }
}
// }}}

?>
