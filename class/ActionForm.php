<?php
// vim: foldmethod=marker
/**
 *  ActionForm.php
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
     *  @access protected
     */

    /** @var    array   フォーム値定義(デフォルト) */
    protected $form_template = array();

    /** @var    array   フォーム値定義 */
    protected $form = array();

    /** @FIXME @protected    array   フォーム値 */
    public $form_vars = array();

    /** @protected    array   アプリケーション設定値 */
    protected $app_vars = array();

    /** @protected    array   アプリケーション設定値(自動エスケープなし) */
    protected $app_ne_vars = array();

    /** @protected    object  Ethna_Backend       バックエンドオブジェクト */
    protected $backend;

    /** @protected    object  Ethna_ActionError   アクションエラーオブジェクト */
    protected $action_error;

    /** @protected    object  Ethna_ActionError   アクションエラーオブジェクト(省略形) */
    protected $ae;

    /** @protected    object  Ethna_I18N  i18nオブジェクト */
    protected $i18n;

    /** @protected    object  Ethna_Logger    ログオブジェクト */
    protected $logger;

    /** @protected    object  Ethna_Plugin    プラグインオブジェクト */
    protected $plugin;

    /** @var    array   フォーム定義要素 */
    protected $def = array('name', 'required', 'max', 'min', 'regexp', 'mbregexp',
                     'custom', 'filter', 'form_type', 'type');

    /** @protected    array   フォーム定義のうち非プラグイン要素とみなすprefix */
    protected $def_noplugin = array('type', 'form', 'name', 'plugin', 'filter',
                              'option', 'default');

    /** @protected    bool    追加検証強制フラグ */
    protected $force_validate_plus = false;

    /** @protected    array   アプリケーションオブジェクト(helper) */
    protected $helper_app_object = array();

    /** @protected    array   アプリケーションオブジェクト(helper)で利用しないフォーム名 */
    protected $helper_skip_form = array();

    /** @protected    int   フォーム配列で使用可能な深さの上限 */
    protected $max_form_deps = 10;

    /**#@-*/

    /**
     *  Ethna_ActionFormクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    controllerオブジェクト
     */
    public function __construct($controller)
    {
        $this->backend = $controller->getBackend();
        $this->action_error = $controller->getActionError();
        $this->ae = $this->action_error;
        $this->i18n = $controller->getI18N();
        $this->logger = $controller->getLogger();
        $this->plugin = $controller->getPlugin();

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
            $this->helper_app_object[$key] = $this->_getHelperAppObject($key);
        }

        // フォーム値定義の設定
        $this->_setFormDef_HelperObj();
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
    public function get($name)
    {
        return $this->_getVarsByFormName($this->form_vars, $name);
    }

    /**
     *  フォーム値定義を取得する
     *
     *  @access public
     *  @param  string  $name   取得するフォーム名(nullなら全ての定義を取得)
     *  @return array   フォーム値定義
     */
    public function getDef($name = null)
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
    public function getName($name)
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
     *  フォーム名に対応するキーの配列を返す
     *
     *  @access private
     *  @param  string  $name   フォーム名
     *  @return array   キーの配列
     */
    private function _getFormNameArray($name)
    {
        // 多次元配列を指定した場合
        if (preg_match('/^.*\[[^\]]+\]$/', $name)) { 
            $buff = preg_replace('/\]\[/', '[', $name); // hoge[foo][bar] => hoge[foo[bar]
            $buff = preg_replace('/\]/', "", $buff);    // hoge][foo[bar] => hoge[foo[bar
            $ret = explode('[', $buff);                 // hoge[foo[bar   => array('hoge', 'foo', 'var')
        } else {
            // 多次元配列を指定していない場合
            $ret = array($name);
        }
        return $ret;
    }

    /**
     *  配列の中からキーで指定された要素を取り出す
     *
     *  @access private
     *  @param  array   &$target    対象とする配列
     *  @param  string  $nane       キー
     *  @return string  指定された要素
     */
    private function _getVarsByFormName(&$target, $name)
    {
        $keys = $this->_getFormNameArray($name);
        return $this->_getVarsByKeys($target, $keys);
    }

    /**
     *  配列の中にキーで指定された要素を登録する
     *  フォーム定義にキーがあるかどうかは無関係に登録します
     *
     *  @access private
     *  @param  array   &$target    対象とする配列
     *  @param  string  $nane       キー
     *  @param  mixde   $value      登録する値
     */
    private function _setVarsByFormName(&$target, $name, $vars)
    {
        $keys = $this->_getFormNameArray($name);
        $this->_setVarsByKeys($target, $keys, $vars);
    }

    /**
     *  配列の中からキーで指定された要素を取り出す
     *
     *  @access private
     *  @param  array   &$target    対象とする配列
     *  @param  array   $keys       キーの配列
     *  @return string  指定された要素
     */
    private function _getVarsByKeys(&$target, $keys)
    {
        $count = count($keys);
        if ($count == 0) { // 探索完了
            return $target;
        } elseif ($this->max_form_deps + 1 <= $count) { // 深すぎる配列を制限する
            return null;
        }

        // まだ探索するキーが残っている
        $curval = array_shift($keys);
        if (is_array($target) && array_key_exists($curval, $target)) {
            return $this->_getVarsByKeys($target[$curval], $keys);
        }
        return null;
    }

    private function _setVarsByKeys(&$target, $keys, &$var)
    {
        $count = count($keys);
        if ($count == 0) { // 探索完了
            $target = $var;
            return;
        } elseif ($this->max_form_deps + 1 <= $count) { // 深すぎる配列を制限する
            return;
        }

        // まだ探索するキーが残っている
        $curval = array_shift($keys);
        if (is_array($target)) {
            if (! array_key_exists($curval, $target)) {
                $target[$curval] = null;
            }
        } else {
            $target = array($curval => null);
        }

        $this->_setVarsByKeys($target[$curval], $keys, $var);
    }

    /**
     *  $_FILESの中からキーで指定された要素を取り出す
     *
     *  @access private
     *  @param  array   &$target    対象とする配列
     *  @param  string  $nane       キー
     *  @param  string  $key        $_FILESに含まれる項目(tmp_name等)
     *  @return string  指定された要素
     */
    private function _getFilesInfoByFormName(&$target, $name, $key)
    {
        $form_keys = $this->_getFormNameArray($name);
        array_splice($form_keys, 1, 0, $key);
        return $this->_getVarsByKeys($target, $form_keys);
    }

    /**
     *  ユーザから送信されたフォーム値をフォーム値定義に従ってインポートする
     *
     *  @access public
     */
    public function setFormVars()
    {
        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            return;
        } else if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
            $http_vars = $_POST;
        } else {
            $http_vars = $_GET;
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
                if (is_null($this->_getFilesInfoByFormName($_FILES, $name, 'tmp_name'))) {
                    $this->set($name, null);
                    continue;
                }

                // 配列構造の検査
                if (is_array($def['type'])) {
                    if (is_array($this->_getFilesInfoByFormName($_FILES, $name, 'tmp_name')) == false) {
                        $this->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                        $this->set($name, null);
                        continue;
                    }
                } else {
                    if (is_array($this->_getFilesInfoByFormName($_FILES, $name, 'tmp_name'))) {
                        $this->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                        $this->set($name, null);
                        continue;
                    }
                }

                $files = null;
                if (is_array($def['type'])) {
                    $files = array();
                    // ファイルデータを再構成
                    foreach (array_keys($this->_getFilesInfoByFormName($_FILES, $name, 'name')) as $key) {
                        $files[$key] = array();
                        $files[$key]['name'] = $this->_getFilesInfoByFormName($_FILES, $name."[".$key."]", 'name');
                        $files[$key]['type'] = $this->_getFilesInfoByFormName($_FILES, $name."[".$key."]", 'type');
                        $files[$key]['size'] = $this->_getFilesInfoByFormName($_FILES, $name."[".$key."]", 'size');
                        $files[$key]['tmp_name'] = $this->_getFilesInfoByFormName($_FILES, $name."[".$key."]", 'tmp_name');
                        if ($this->_getFilesInfoByFormName($_FILES, $name."[".$key."]", 'error') == null) {
                            // PHP 4.2.0 以前
                            $files[$key]['error'] = 0;
                        } else {
                            $files[$key]['error'] = $this->_getFilesInfoByFormName($_FILES, $name."[".$key."]", 'error');
                        }
                    }
                } else {
                    $files['name'] = $this->_getFilesInfoByFormName($_FILES, $name, 'name');
                    $files['type'] = $this->_getFilesInfoByFormName($_FILES, $name, 'type');
                    $files['size'] = $this->_getFilesInfoByFormName($_FILES, $name, 'size');
                    $files['tmp_name'] = $this->_getFilesInfoByFormName($_FILES, $name, 'tmp_name');
                    if ($this->_getFilesInfoByFormName($_FILES, $name, 'error') == null) {
                        // PHP 4.2.0 以前
                        $files['error'] = 0;
                    } else {
                        $files['error'] = $this->_getFilesInfoByFormName($_FILES, $name, 'error');
                    }
                }

                // 値のインポート
                $this->set($name, $files);

            } else {
                // ファイル以外の場合

                $target_var = $this->_getVarsByFormName($http_vars, $name);

                // 値の有無の検査
                if (isset($target_var) == false
                    || is_null($target_var)) {
                    $this->set($name, null);
                    if (isset($http_vars["{$name}_x"])
                     && isset($http_vars["{$name}_y"])) {
                        // 以前の仕様に合わせる
                        $this->set($name, $http_vars["{$name}_x"]);
                    }
                    continue;
                }

                // 配列構造の検査
                if (is_array($def['type'])) {
                    if (is_array($target_var) == false) {
                        // 厳密には、この配列の各要素はスカラーであるべき
                        $this->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                        $this->set($name, null);
                        continue;
                    }
                } else {
                    if (is_array($target_var)) {
                        $this->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                        $this->set($name, null);
                        continue;
                    }
                }

                // 値のインポート
                $this->set($name, $target_var);
            }
        }
    }

    /**
     *  ユーザから送信されたフォーム値をクリアする
     *
     *  @access public
     */
    public function clearFormVars()
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
    public function set($name, $value)
    {
        $this->_setVarsByFormName($this->form_vars, $name, $value);
    }

    /**
     *  フォーム値定義を設定する
     *
     *  @access public
     *  @param  string  $name   設定するフォーム名(nullなら全ての定義を設定)
     *  @param  array   $value  設定するフォーム値定義
     *  @return array   フォーム値定義
     */
    public function setDef($name, $value)
    {
        if (is_null($name)) {
            $this->form = $value;
        }
        else {
            $this->form[$name] = $value;
        }
    }

    /**
     *  フォーム値を配列にして返す
     *
     *  @access public
     *  @param  bool    $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    public function getArray($escape = true)
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
    public function getApp($name)
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
    public function setApp($name, $value)
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
    public function getAppArray($escape = true)
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
    public function getAppNE($name)
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
    public function setAppNE($name, $value)
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
    public function getAppNEArray($escape = false)
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
    public function _getArray(&$vars, &$retval, $escape)
    {
        foreach (array_keys($vars) as $name) {
            if (is_array($vars[$name])) {
                $retval[$name] = array();
                $this->_getArray($vars[$name], $retval[$name], $escape);
            } else if (is_null($vars[$name])) {
                $retval[$name] = null;
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
    public function isForceValidatePlus()
    {
        return $this->force_validate_plus;
    }

    /**
     *  追加検証強制フラグを設定する
     *
     *  @access public
     *  @param  $force_validate_plus    追加検証強制フラグ
     */
    public function setForceValidatePlus($force_validate_plus)
    {
        $this->force_validate_plus = $force_validate_plus;
    }

    /**
     *  フォーム値検証メソッド
     *
     *  @access public
     *  @return int     発生したエラーの数
     */
    public function validate()
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
    private function _validateWithPlugin($form_name)
    {
        // (pre) filter
        if ($this->form[$form_name]['type'] != VAR_TYPE_FILE) {

            //    入力値とフィルタ定義を取り出す
            $form_var = $this->get($form_name);
            $filter = (isset($this->form[$form_name]['filter']))
                    ? $this->form[$form_name]['filter']
                    : null;

            //    フィルタを適用
            if (is_array($this->form[$form_name]['type']) == false) {
                $this->set($form_name, $this->_filter($form_var, $filter));
            } else if ($form_var != null) {  //  配列の場合
                foreach (array_keys($form_var) as $key) {
                    $this->set($form_name."[".$key."]", $this->_filter($form_var[$key], $filter));
                }
            } else {  //  配列で値が空の場合
                $this->set($form_name, $this->_filter($form_var, $filter));
            }
        }

        $form_vars = $this->get($form_name);
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
                $v = $this->plugin->getPlugin('Validator',
                                               ucfirst(strtolower($name)));
                if (Ethna::isError($v)) {
                    continue;
                }

                // バリデーション実行
                unset($r);
                $r = $v->validate($form_name, $form_vars, $plugin[$name]);

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
            $v = $this->plugin->getPlugin('Validator', ucfirst(strtolower($name)));
            if (Ethna::isError($v)) {
                continue;
            }

            // 配列全体を受け取るプラグインの場合
            if (isset($v->accept_array) && $v->accept_array == true) {
                // バリデーション実行
                unset($r);
                $r = $v->validate($form_name, $form_vars, $plugin[$name]);

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
                $r = $v->validate($form_name, $form_vars[$key], $plugin[$name]);

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
    public function check($name)
    {
        if (is_null($this->get($name)) || $this->get($name) === "") {
            return null;
        }

        // Ethna_Backendの設定
        $c = Ethna_Controller::getInstance();
        $this->backend = $c->getBackend();

        return to_array($this->get($name));
    }

    /**
     *  チェックメソッド: 機種依存文字
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    public function checkVendorChar($name)
    {
        $null = null;
        $string = $this->get($name);

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
    public function checkBoolean($name)
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
    public function checkMailaddress($name)
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
    public function checkURL($name)
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
    public function getHiddenVars($include_list = null, $exclude_list = null)
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

            $form_value = $this->get($key);
            if (is_array($value['type'])) {
                $form_array = true;
                //  フォーム定義が配列なのにスカラーが
                //  渡ってきた場合は値を配列扱いとし、
                //  フォーム定義を尊重する
                if (is_array($form_value) == false) {
                    $form_value = array($form_value);
                }
            } else {
                //  フォーム定義がスカラーなのに配列が渡ってきた
                //  場合は救いようがないのでNOTICE扱いとし、タグも出力しない
                if (is_array($form_value)) {
                    $this->handleError($key, E_FORM_WRONGTYPE_ARRAY);
                    continue;
                }
                $form_value = array($form_value);
                $form_array = false;
            }

            if (is_null($this->get($key))) {
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
                            htmlspecialchars($form_name, ENT_QUOTES),
                            htmlspecialchars($v, ENT_QUOTES)
                    );
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
    public function handleError($name, $code)
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
    protected function _validatePlus()
    {
    }

    /**
     *  カスタムチェックメソッドを実行する
     *
     *  @access protected
     *  @param  string  $method_list    カスタムメソッド名(カンマ区切り)
     *  @param  string  $name           フォーム項目名
     */
    protected function _validateCustom($method_list, $name)
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
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @param  int     $filter フィルタ定義
     *  @return mixed   変換結果
     */
    protected function _filter($value, $filter)
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
    protected function _filter_alnum_zentohan($value)
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
    protected function _filter_numeric_zentohan($value)
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
    protected function _filter_alphabet_zentohan($value)
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
    protected function _filter_ltrim($value)
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
    protected function _filter_rtrim($value)
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
    protected function _filter_ntrim($value)
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
    protected function _filter_kana_hantozen($value)
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
    protected function _setFormTemplate($form_template)
    {
        return $form_template;
    }

    /**
     *  フォーム定義変更用、ユーザ定義ヘルパメソッド
     *
     *  Ethna_ActionForm#prepare() が実行される前に
     *  ユーザが動的にフォーム定義を変更したい場合に
     *  このメソッドをオーバーライドします。
     *
     *  $this->backend も初期化済みのため、DBやセッション
     *  の値に基づいてフォーム定義を変更することができます。
     *
     *  @access public 
     */
    public function setFormDef_PreHelper()
    {
        //  TODO: override this method. 
    }

    /**
     *  フォーム定義変更用、ユーザ定義ヘルパメソッド
     *
     *  フォームヘルパを使うときに、フォーム定義を動的に
     *  変更したい場合に、このメソッドをオーバーライドします。
     *
     *  以下の定義をテンプレートで行った場合に呼び出されます。
     *  
     *  {form ethna_action=...} (ethna_action がない場合は呼び出されません)
     *  {form_input action=...} (action がない場合は呼び出されません)
     *
     *  @access public 
     */
    public function setFormDef_ViewHelper()
    {
        //   TODO: デフォルト実装は Ethna_ActionClass#prepare 前に
        //   呼び出されるものと同じ。異なる場合にオーバライドする
        $this->setFormDef_PreHelper(); 
    }

    /**
     *  ヘルパオブジェクト(アプリケーションオブジェクト)
     *  経由でのフォーム値定義を設定する
     *
     *  @access protected
     */
    protected function _setFormDef_HelperObj()
    {
        foreach (array_keys($this->helper_app_object) as $key) {
            $object = $this->helper_app_object[$key];
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
    protected function _setFormDef()
    {
        foreach ($this->form as $key => $value) {
            if (is_numeric($key)) {
                $this->form[$value] = array();
                unset($this->form[$key]);
            }
        }

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
    protected function _getPluginDef($form_name)
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
    protected function _getHelperAppObject($key)
    {
        $app_object = $this->backend->getObject($key);
        return $app_object;
    }
}
// }}}

