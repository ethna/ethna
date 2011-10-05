<?php
// vim: foldmethod=marker
/**
 *  InfoManager.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_InfoManager
/**
 *  Ethnaマネージャクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_InfoManager extends Ethna_AppManager
{
    /**#@+
     *  @access private
     */
    
    /** @var    object  Ethna_Controller    コントローラオブジェクト */
    var $ctl;

    /** @var    object  Ethna_ClassFactory  クラスファクトリオブジェクト */
    var $class_factory;

    /** @var    array   アクションスクリプト解析結果キャッシュファイル */
    var $cache_class_list_file;

    /** @var    array   アクションスクリプト解析結果キャッシュ */
    var $cache_class_list;

    /** @var    array   [属性]DBタイプ一覧 */
    var $db_type_list = array(
        DB_TYPE_RW      => array('name' => 'DB_TYPE_RW'),
        DB_TYPE_RO      => array('name' => 'DB_TYPE_RO'),
        DB_TYPE_MISC    => array('name' => 'DB_TYPE_MISC'),
    );

    /** @var    array   [属性]フォーム型一覧 */
    var $form_type_list;
 
    /** @var    array   [属性]変数型一覧 */
    var $var_type_list;

    /**#@-*/

    /**
     *  Ethna_InfoManagerのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   $backend   Ethna_Backendオブジェクト
     */
    public function __construct($backend)
    {
        $this->form_type_list = array(
            FORM_TYPE_TEXT      => array('name' => _et('TextBox')),
            FORM_TYPE_PASSWORD  => array('name' => _et('Password')),
            FORM_TYPE_TEXTAREA  => array('name' => _et('TextArea')),
            FORM_TYPE_SELECT    => array('name' => _et('SelectBox')),
            FORM_TYPE_RADIO     => array('name' => _et('RadioButton')),
            FORM_TYPE_CHECKBOX  => array('name' => _et('CheckBox')),
            FORM_TYPE_SUBMIT    => array('name' => _et('SubmitButton')),
            FORM_TYPE_FILE      => array('name' => _et('File')),
        );

        $this->var_type_list = array(
            VAR_TYPE_INT        => array('name' => _et('Integer')),
            VAR_TYPE_FLOAT      => array('name' => _et('Float')),
            VAR_TYPE_STRING     => array('name' => _et('String')),
            VAR_TYPE_DATETIME   => array('name' => _et('Datetime')),
            VAR_TYPE_BOOLEAN    => array('name' => _et('Boolean')),
            VAR_TYPE_FILE       => array('name' => _et('File')),
        );

        parent::__construct($backend);
        $this->ctl = Ethna_Controller::getInstance();
        $this->class_factory = $this->ctl->getClassFactory();

        // アクションスクリプト解析結果キャッシュ取得
        $this->cache_class_list_file = sprintf('%s/ethna_info_class_list',
            $this->ctl->getDirectory('tmp')
        );

        if (file_exists($this->cache_class_list_file)
            && filesize($this->cache_class_list_file) > 0)
        {
            $fp = fopen($this->cache_class_list_file, 'r');
            $s = fread($fp, filesize($this->cache_class_list_file));
            fclose($fp);
            $this->cache_class_list = unserialize($s);
        }
    }

    /**
     *  定義済みアクション一覧を取得する
     *
     *  @access public
     *  @return array   アクション一覧
     */
    function getActionList()
    {
        $r = array();

        // アクションスクリプトを解析する
        $class_list = $this->_analyzeActionList();

        // アクション定義エントリ一覧
        list($manifest_action_list, $manifest_class_list) = $this->_getActionList_Manifest($class_list);

        // アクション定義省略エントリ一覧
        $implicit_action_list = $this->_getActionList_Implicit($class_list, $manifest_action_list, $manifest_class_list);

        $r = array_merge($manifest_action_list, $implicit_action_list);
        ksort($r);

        // アクション定義情報補完
        $r = $this->_addActionList($r);

        return $r;
    }

    /**
     *  定義済み遷移先一覧を取得する
     *
     *  @access public
     *  @return array   遷移先一覧
     */
    function getForwardList()
    {
        $r = array();

        // テンプレート/ビュースクリプトを解析する
        $forward_list = $this->_analyzeForwardList();

        // ビュー定義エントリ一覧
        $manifest_forward_list = $this->_getForwardList_Manifest();

        // ビュー定義省略エントリ一覧
        $implicit_forward_list = $this->_getForwardList_Implicit($forward_list, $manifest_forward_list);

        $r = $manifest_forward_list + $implicit_forward_list;
        ksort($r);

        return $r;
    }

    /**
     *  ディレクトリ以下のアクションスクリプトを解析する
     *
     *  @access private
     *  @param  string  $action_dir     解析対象のディレクトリ
     *  @return array   アクションクラス定義一覧
     */
    function _analyzeActionList($action_dir = null)
    {
        $r = array();
        $cache_update = false;

        if (is_null($action_dir)) {
            $cache_update = true;
            $action_dir = $this->ctl->getActiondir();
        }
        $prefix_len = strlen($this->ctl->getActiondir());

        $child_dir_list = array();

        $dh = opendir($action_dir);
        if ($dh == false) {
            return;
        }

        $ext = $this->ctl->getExt('php');
        while (($file = readdir($dh)) !== false) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $file = $action_dir . $file;

            if (is_dir($file)) {
                $child_dir_list[] = $file;
                continue;
            }

            if (preg_match("/\.$ext\$/", $file) == 0) {
                continue;
            }

            $key = substr($file, $prefix_len);
            
            // キャッシュチェック
            include_once $file;
            if ($this->cache_class_list[$key]['.mtime'] >= filemtime($file)) {
                $class_list = $this->cache_class_list[$key];
            } else {
                $class_list = $this->_analyzeActionScript($file);
            }
            if (is_null($class_list) == false) {
                $r[$key] = $class_list;
            }
        }

        closedir($dh);

        foreach ($child_dir_list as $child_dir) {
            $tmp = $this->_analyzeActionList($child_dir . "/");
            $r = array_merge($r, $tmp);
        }

        if ($cache_update) {
            // キャッシュファイル更新
            $fp = fopen($this->cache_class_list_file, 'w');
            fwrite($fp, serialize($r));
            fclose($fp);
        }

        return $r;
    }

    /**
     *  アクションスクリプトを解析する
     *
     *  @access private
     *  @param  string  $script ファイル名
     *  @return array   アクションクラス定義一覧
     */
    function _analyzeActionScript($script)
    {
        $class_list = array();
        $class_list['.mtime'] = filemtime($script);

        $source = "";
        $fp = fopen($script, 'r');
        if ($fp == false) {
            return null;
        }
        while (feof($fp) == false) {
            $source .= fgets($fp, 8192);
        }
        fclose($fp);

        // トークンに分割してクラス定義情報を取得
        $token_list = token_get_all($source);
        $state = 'T_OUT';
        $nest = 0;
        $method_nest = 0;
        $current = null;
        for ($i = 0; $i < count($token_list); $i++) {
            $token = $token_list[$i];

            if (is_string($token)) {
                if ($token == '{') {
                    $nest++;
                } else if ($token == '}') {
                    $nest--;
                    if ($state == 'T_PREPARE' || $state == 'T_PERFORM') {
                        if ($nest == $method_nest) {
                            $state = 'T_ACTION_CLASS';
                        }
                    } else if ($nest == 0) {
                        $state = 'T_OUT';
                    }
                }
                continue;
            }

            if ($token[0] == T_CLASS) {
                // クラス定義開始
                $i += 2;
                $class_name = $token_list[$i][1];       // should be T_STRING
                if ($this->_isSubclassOf($class_name, 'Ethna_ActionClass')) {
                    $state = 'T_ACTION_CLASS';
                    $current = $class_name;
                    $class_list[$current] = array('type' => 'action_class');
                } else if ($this->_isSubclassOf($class_name, 'Ethna_ActionForm')) {
                    $state = 'T_ACTION_FORM';
                    $current = $class_name;
                    $class_list[$current] = array('type' => 'action_form');
                }
                $nest = 0;  // for safe
            } else if ($token[0] == T_COMMENT && strncmp($token[1], "/**", 3) == 0 && is_array($token_list[$i+2]) && $token_list[$i+2][0] == T_CLASS) {
                // DocComment for class
            } else if ($state == 'T_ACTION_CLASS' && $token[0] == T_FUNCTION) {
                $i += 2;
                $method_name = $token_list[$i][1];
                if (strcasecmp($method_name, 'prepare') == 0) {
                    $state = 'T_PREPARE';
                    $method_nest = $nest;
                } else if (strcasecmp($method_name, 'perform') == 0) {
                    $state = 'T_PERFORM';
                    $method_nest = $nest;
                }
            } else if (($state == 'T_PREPARE' || $state == 'T_PERFORM') && $token[0] == T_RETURN) {
                $s = "";
                $n = 1;
                while ($token_list[$i+$n] !== ";") {
                    $s .= is_string($token_list[$i+$n]) ? $token_list[$i+$n] : $token_list[$i+$n][1];
                    $n++;
                }
                $key = $state == 'T_PREPARE' ? 'prepare' : 'perform';
                $class_list[$current]['return'][$key][] = $s;
            }
        }

        if (count($class_list) == 0) {
            return null;
        }
        return $class_list;
    }

    /**
     *  指定されたクラス名を継承しているかどうかを返す
     *
     *  @access private
     *  @param  string  $class_name     チェック対象のクラス名
     *  @param  string  $parent_name    親クラス名
     *  @return bool    true:継承している false:いない
     */
    function _isSubclassOf($class_name, $parent_name)
    {
        while ($tmp = get_parent_class($class_name)) {
            if (strcasecmp($tmp, $parent_name) == 0) {
                return true;
            }
            $class_name = $tmp;
        }
        return false;
    }

    /**
     *  コントローラに明示的に定義されているアクション一覧を取得する
     *
     *  @access private
     *  @param  array   定義されているクラス一覧
     *  @return array   array(アクション一覧, クラス一覧)
     */
    function _getActionList_Manifest($class_list)
    {
        $manifest_action_list = array();
        $manifest_class_list = array();
        foreach ($this->_getInvisibleProperty($this->ctl, 'action') as $action_name => $action) {
            if ($action_name == '__ethna_info__' || $action_name == '__ethna_unittest__') {
                continue;
            }
            $action = $this->ctl->_getAction($action_name);

            $elt = array();
            // _analyzeActionList()で取得したクラス定義データから対応関係を取得
            foreach ($class_list as $file => $elts) {
                foreach ($elts as $class_name => $def) {
                    if ($def['type'] == 'action_class' && strcasecmp($class_name, $action['class_name']) == 0) {
                        $elt['action_class'] = $class_name;
                        $elt['action_class_file'] = $file;
                        $elt['action_class_info'] = $def;
                    } else if ($def['type'] == 'action_form' && strcasecmp($class_name, $action['form_name']) == 0) {
                        $elt['action_form'] = $class_name;
                        $elt['action_form_file'] = $file;
                        $elt['action_form_info'] = $def;
                    }
                }
            }

            // 未定義チェック
            if (isset($elt['action_class']) == false) {
                $elt['action_class'] = $action['class_name'];
                if (class_exists($action['class_name']) == false) {
                    $elt['action_class_info'] = array('undef' => true);
                }
                else {
                    $elt['action_class_info'] = array('undef' => false);
                }
            }

            if (isset($elt['action_form']) == false && $action['form_name'] != 'Ethna_ActionForm') {
                $elt['action_form'] = $action['form_name'];
                if (class_exists($action['form_name']) == false) {
                    $elt['action_form_info'] = array('undef' => true);
                }
                else {
                    $elt['action_form_info'] = array('undef' => false);
                }
            }
            $manifest_action_list[$action_name] = $elt;
            $manifest_class_list[] = strtolower($elt['action_class']);
        }

        return array($manifest_action_list, $manifest_class_list);
    }

    /**
     *  暗黙に定義されているアクション一覧を取得する
     *
     *  @access private
     *  @param  array   $class_list             定義されているクラス一覧
     *  @param  array   $manifest_action_list   明示的に定義済みのアクション一覧
     *  @param  array   $manifest_class_list    明示的に定義済みのクラス一覧
     *  @return array   array:アクション一覧
     */
    function _getActionList_Implicit($class_list, $manifest_action_list, $manifest_class_list)
    {
        $implicit_action_list = array();

        foreach ($class_list as $file => $elts) {
            foreach ($elts as $class_name => $def) {
                if (in_array(strtolower($class_name), $manifest_class_list)) {
                    continue;
                }

                // クラス名からアクション名を取得
                if ($def['type'] == 'action_class') {
                    $action_name = $this->ctl->actionClassToName($class_name);
                    if (array_key_exists($action_name, $manifest_action_list)) {
                        continue;
                    }
                    $implicit_action_list[$action_name]['action_class'] = $class_name;
                    $implicit_action_list[$action_name]['action_class_file'] = $file;
                    $implicit_action_list[$action_name]['action_class_info'] = $def;
                } else if ($def['type'] == 'action_form') {
                    $action_name = $this->ctl->actionFormToName($class_name);
                    if (array_key_exists($action_name, $manifest_action_list)) {
                        continue;
                    }
                    $implicit_action_list[$action_name]['action_form'] = $class_name;
                    $implicit_action_list[$action_name]['action_form_file'] = $file;
                    $implicit_action_list[$action_name]['action_form_info'] = $def;
                } else {
                    continue;
                }
            }
        }

        return $implicit_action_list;
    }
    
    /**
     *  アクション定義一覧を補完する
     *
     *  @access private
     *  @param  array   $action_list    取得したアクション一覧
     *  @return array   修正後のアクション一覧
     */
    function _addActionList($action_list)
    {
        foreach ($action_list as $action_name => $action) {
            // アクションフォームにフォーム定義情報を追加
            $form_name = $action['action_form'];
            if (class_exists($form_name) == false) {
                continue;
            }
            $af = new $form_name($this->ctl);

            $form = array();
            foreach ($af->getDef() as $name => $def) {
                $form[$name]['required'] = $def['required'] ? 'true' : 'false';
                foreach (array('name', 'max', 'min', 'regexp', 'custom') as $key) {
                    $form[$name][$key] = $def[$key];
                }
                $form[$name]['filter'] = str_replace(",", "\n", $def['filter']);
                $form[$name]['form_type'] = $this->getAttrName('form_type', $def['form_type']);
                $form[$name]['type_is_array'] = is_array($def['type']);
                $form[$name]['type'] = $this->getAttrName('var_type', is_array($def['type'])
                                     ? $def['type'][0] : $def['type']);
            }
            $action['action_form_info']['form'] = $form;
            $action_list[$action_name] = $action;
        }

        return $action_list;
    }

    /**
     *  ディレクトリ以下のテンプレートを解析する
     *
     *  @access private
     *  @param  string  $action_dir     解析対象のディレクトリ
     *  @return array   遷移定義一覧
     */
    function _analyzeForwardList($template_dir = null)
    {
        $r = array();

        if (is_null($template_dir)) {
            $template_dir = $this->ctl->getTemplatedir();
        }
        $prefix_len = strlen($this->ctl->getTemplatedir());

        $child_dir_list = array();

        $dh = opendir($template_dir);
        if ($dh == false) {
            return;
        }

        $ext = $this->ctl->getExt('tpl');
        while (($file = readdir($dh)) !== false) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $file = $template_dir . '/' . $file;

            if (is_dir($file)) {
                $child_dir_list[] = $file;
                continue;
            }

            if (preg_match("/\.$ext\$/", $file) == 0) {
                continue;
            }

            $tpl = substr($file, $prefix_len);
            $r[] = $this->ctl->forwardPathToName($tpl);
        }

        closedir($dh);

        foreach ($child_dir_list as $child_dir) {
            $tmp = $this->_analyzeForwardList($child_dir);
            $r = array_merge($r, $tmp);
        }

        return $r;
    }

    /**
     *  コントローラに明示的に定義されている遷移先一覧を取得する
     *
     *  @access private
     *  @return array   ビュー一覧
     */
    function _getForwardList_Manifest()
    {
        $manifest_forward_list = array();
        foreach ($this->_getInvisibleProperty($this->ctl, 'forward') as $forward_name => $forward) {
            if ($forward_name == '__ethna_info__' || $forward_name == '__ethna_unittest__') {
                continue;
            }

            $elt = array();
            $elt['template_file'] = $this->ctl->_getForwardPath($forward_name);
            if (file_exists(sprintf("%s/%s", $this->ctl->getTemplatedir(), $elt['template_file'])) == false) {
                $elt['template_file_info'] = array('undef' => true);
            }
            else {
                $elt['template_file_info'] = array('undef' => false);
            }

            $elt['view_class'] = $this->ctl->getViewClassName($forward_name);
            if ($elt['view_class'] == 'Ethna_ViewClass') {
                $elt['view_class'] = null;
                $elt['view_class_info'] = array('undef' => false);
            } else if (class_exists($elt['view_class']) == false) {
                $elt['view_class_info'] = array('undef' => true);
            }

            if (isset($forward['view_path']) && $forward['view_path']) {
                $elt['view_path'] = $forward['view_path'];
            } else if ($this->_isSubclassOf($elt['view_class'], 'Ethna_ViewClass')) {
                $elt['view_class_file'] = $this->ctl->getDefaultViewPath($forward_name);
            } else {
                foreach ($this->cache_class_list as $file => $elts) {
                    foreach ($elts as $name => $def) {
                        if (strcasecmp($elt['view_class'], $name) == 0) {
                            $elt['view_class_file'] = $file;
                            break 2;
                        }
                    }
                }
            }

            $manifest_forward_list[$forward_name] = $elt;
        }

        return $manifest_forward_list;
    }

    /**
     *  暗黙に定義されているビュー一覧を取得する
     *
     *  @access private
     *  @param  array   $forward_list           定義されている遷移名一覧
     *  @param  array   $manifest_forward_list  明示的に定義済みのビュー一覧
     *  @return array   array:ビュー一覧
     */
    function _getForwardList_Implicit($forward_list, $manifest_forward_list)
    {
        $implicit_forward_list = array();
        $manifest_forward_name_list = array_keys($manifest_forward_list);

        foreach ($forward_list as $forward_name) {
            //
            //    エラー用のテンプレートはEthnaのデフォルトの命名規則に
            //    従っていないため、暫定修正。
            //    TODO: 2.5.0 の最終版が出るまでにこの点は改善される予定
            //    なので、改善され次第このif文は削除すべきである。
            //
            if (preg_match('/error[403|404|500]/', $forward_name)) {
                continue;
            }

            if (in_array($forward_name, $manifest_forward_name_list)) {
                continue;
            }

            $elt = array();
            $elt['template_file'] = $this->ctl->_getForwardPath($forward_name);
            $elt['view_class'] = $this->ctl->getViewClassName($forward_name);
            if ($elt['view_class'] == 'Ethna_ViewClass') {
                $elt['view_class'] = null;
            } else if (class_exists($elt['view_class']) == false) {
                $elt['view_class'] = null;
            } else {
                $elt['view_class_file'] = $this->ctl->getDefaultViewPath($forward_name);
            }

            $implicit_forward_list[$forward_name] = $elt;
        }

        return $implicit_forward_list;
    }

    /**
     *  Ethnaの設定一覧を取得する
     *
     *  @access public
     *  @return array   設定一覧を格納した配列
     *  @todo   respect access controll
     */
    function getConfiguration()
    {
        $r = array();

        // core
        $elts = array();
        $elts[_et('Application ID')] = $this->ctl->getAppId();
        $elts[_et('Application URL')] = $this->ctl->getURL();
        $elts[_et('Ethna Version')] = ETHNA_VERSION;
        $elts[_et('Ethna Base Directory')] = ETHNA_BASE;
        $r['Core'] = $elts;

        // class
        $elts = array();
        $elts[_et('Backend')] = $this->class_factory->getObjectName('backend');
        $elts[_et('ClassFactory')] = $this->class_factory->getObjectName('class');
        $elts[_et('Config')] = $this->class_factory->getObjectName('config');
        $elts['DB'] = $this->class_factory->getObjectName('db');
        $elts[_et('Error')] = $this->class_factory->getObjectName('error');
        $elts[_et('Form')] = $this->class_factory->getObjectName('form');
        $elts[_et('Log')] = $this->class_factory->getObjectName('logger');
        $elts['i18n'] = $this->class_factory->getObjectName('i18n');
        $elts[_et('Plugin')] = $this->class_factory->getObjectName('plugin');
        $elts[_et('Session')] = $this->class_factory->getObjectName('session');
        $elts['SQL'] = $this->class_factory->getObjectName('sql');
        $elts[_et('View')] = $this->class_factory->getObjectName('view');
        $r[_et('Class')] = $elts;

        // DB
        $elts = array();
        $db_list = array();
        foreach ($dbl = $this->_getInvisibleProperty($this->ctl, 'db') as $key => $db) {
            if ($key == "") {
                $tmp = '$db';
            } else {
                $tmp = sprintf('$db_%s', $key);
            }
            $elts[$tmp] = $this->getAttrName('db_type', $db);
            $db_list[$key] = $tmp;
        }
        $r[_et('DB Type')] = $elts;

        // DSN
        $elts = array();
        foreach ($db_list as $key => $name) {
            $config_key = "dsn";
            if ($key != "") {
                $config_key .= "_$key";
            }
            $dsn = $this->config->get($config_key);
            if ($dsn) {
                $elts[$name] = implode("\n", to_array($dsn));
            }
        }
        $r['DSN'] = $elts;

        // directory
        $elts = array();
        $elts[_et('Application')] = $this->ctl->getBasedir();
        $elts[_et('Action')] = $this->ctl->getActiondir();
        $elts[_et('View')] = $this->ctl->getViewdir();
        $elts[_et('Filter')] = $this->ctl->getDirectory('filter');
        $elts[_et('Plugin')] = $this->ctl->getDirectory('plugin');
        $elts[_et('Template')] = $this->ctl->getTemplatedir();
        $elts[_et('Template Cache')] = $this->ctl->getDirectory('template_c');
        $elts[_et('Smarty Plugin')] = implode(',', $this->ctl->getDirectory('plugins'));
        $elts[_et('Configuration File')] = $this->ctl->getDirectory('etc');
        $elts[_et('Locale')] = $this->ctl->getDirectory('locale');
        $elts[_et('Logging')] = $this->ctl->getDirectory('log');
        $elts[_et('Temporary File')] = $this->ctl->getDirectory('tmp');
        $r[_et('Directory')] = $elts;

        // ext
        $elts = array();
        $elts[_et('Template')] = $this->ctl->getExt('tpl');
        $elts[_et('PHP Script')] = $this->ctl->getExt('php');
        $r[_et('File Extention')] = $elts;

        // filter
        $elts = array();
        $n = 1;
        foreach ($this->_getInvisibleProperty($this->ctl, 'filter') as $filter) {
            $key = sprintf(_et('Filter(%d)'), $n);
            if (class_exists($filter)) {
                $elts[$key] = $filter;
                $n++;
            }
        }
        $r[_et('Filter')] = $elts;

        // manager
        $elts = array();
        foreach ($this->ctl->getManagerList() as $key => $manager) {
            $name = sprintf('$%s', $key);
            $elts[$name] = $this->ctl->getManagerClassName($manager);
        }
        $r[_et('Application Manager')] = $elts;

        return $r;
    }

    /**
     *  プラグインの一覧を取得する
     *
     *  @access public
     *  @return array   設定一覧を格納した配列
     *  @todo   respect access controll
     */
    function getPluginList()
    {
        $r = array();
        $plugin = $this->ctl->getPlugin();
        foreach ($plugin->searchAllPluginType() as $type) {
            $plugin->searchAllPluginSrc($type);
            $src_registry = $this->_getInvisibleProperty($plugin, 'src_registry');
            if (isset($src_registry[$type])) {
                $elts = array();
                foreach ($src_registry[$type] as $name => $src) {
                    if (empty($src)) {
                        continue;
                    }
                    $elts[$name] = $src[2];
                }
                ksort($elts);
                $r[$type] = $elts;
            }
        }
        ksort($r);
        return $r;
    }

    private function _getInvisibleProperty($obj, $prop_name)
    {
        $ref = new ReflectionClass($obj);
        $prop = $ref->getProperty($prop_name);
        $prop->setAccessible(true);
        $prop_ret = $prop->getValue($obj);
        return $prop_ret;
    }
}
// }}}

