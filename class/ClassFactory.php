<?php
// vim: foldmethod=marker
/**
 *  ClassFactory.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_ClassFactory
/**
 *  Ethnaフレームワークのオブジェクト生成ゲートウェイ
 *
 *  DIコンテナか、ということも考えましたがEthnaではこの程度の単純なものに
 *  留めておきます。アプリケーションレベルDIしたい場合はフィルタチェインを
 *  使って実現することも出来ます。
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_ClassFactory
{
    /**#@+
     *  @access private
     */

    /** @var Ethna_Controller $controller    controllerオブジェクト */
    public $controller;

    /** @var Ethna_Controller $ctl   controllerオブジェクト(省略形) */
    public $ctl;

    /** @var array $class  クラス定義 */
    public $class = array();

    /** @var array $object 生成済みオブジェクトキャッシュ */
    public $object = array();

    /** @var array $manager  生成済みアプリケーションマネージャオブジェクトキャッシュ */
    public $manager = array();

    /** @var array $method_list   メソッド一覧キャッシュ */
    public $method_list = array();

    /**#@-*/


    /**
     *  Ethna_ClassFactoryクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    controllerオブジェクト
     *  @param  array                       $class          クラス定義
     */
    public function __construct($controller, $class)
    {
        $this->controller = $controller;
        $this->ctl = $controller;
        $this->class = $class;
    }

    /**
     *  typeに対応するアプリケーションマネージャオブジェクトを返す
     *  注意： typeは大文字小文字を区別しない
     *         (PHP自体が、クラス名の大文字小文字を区別しないため)
     *
     *  @access public
     *  @param  string  $type   アプリケーションマネージャー名
     *  @param  bool    $weak   オブジェクトが未生成の場合の強制生成フラグ(default: false)
     *  @return object  Ethna_AppManager    マネージャオブジェクト
     */
    function getManager($type, $weak = false)
    {
        $obj = null;

        //  すでにincludeされていなければ、includeを試みる
        //  ここで返されるクラス名は、AppObjectの命名規約によるもの
        //
        //  これは、AppObject のファイル中にAppManagerが含まれる場合が
        //  あるため必要なルーチンである
        $obj_class_name = $this->controller->getObjectClassName($type);
        if (class_exists($obj_class_name) === false) {
            $this->_include($obj_class_name);
        }

        //  すでにincludeされていなければ、includeを試みる
        //  ここで返されるクラス名は、AppManagerの命名規約によるもの
        $class_name = $this->controller->getManagerClassName($type);
        if (class_exists($class_name) === false
            && $this->_include($class_name) === false) {
            return $obj;  //  include 失敗。戻り値はNULL。
        }

        //  メソッド情報を集める
        if (isset($this->method_list[$class_name]) == false) {
            $this->method_list[$class_name] = get_class_methods($class_name);
            for ($i = 0; $i < count($this->method_list[$class_name]); $i++) {
                $this->method_list[$class_name][$i] = strtolower($this->method_list[$class_name][$i]);
            }
        }

        //  PHPのクラス名は大文字小文字を区別しないので、
        //  同じクラス名と見做されるものを指定した場合には
        //  同じインスタンスが返るようにする
        $type = strtolower($type);

        //  以下のルールに従って、キャッシュが利用可能かを判定する
        //  利用可能と判断した場合、キャッシュされていればそれを返す
        //
        //  1. メソッドに getInstance があればキャッシュを利用可能と判断する
        //     この場合、シングルトンかどうかは getInstance 次第
        //  2. weak が true であれば、キャッシュは利用不能と判断してオブジェクトを再生成
        //  3. weak が false であれば、キャッシュは利用可能と判断する(デフォルト)
        if ($this->_isCacheAvailable($class_name, $this->method_list[$class_name], $weak)) {
            if (isset($this->manager[$type]) && is_object($this->manager[$type])) {
                return $this->manager[$type];
            }
        }

        //  インスタンス化のヘルパ(getInstance)があればそれを使う
        if (in_array("getinstance", $this->method_list[$class_name])) {
            $obj = call_user_func(array($class_name, 'getInstance'));
        } else {
            $backend = $this->controller->getBackend();
            $obj = new $class_name($backend);
        }

        //  生成したオブジェクトはとりあえずキャッシュする
        if (isset($this->manager[$type]) == false || is_object($this->manager[$type]) == false) {
            $this->manager[$type] = $obj;
        }

        return $obj;
    }

    /**
     *  クラスキーに対応するオブジェクトを返す/クラスキーが未定義の場合はAppObjectを探す
     *  クラスキーとは、[Appid]_Controller#class に定められたもの。
     *
     *  @access public
     *  @param  string  $key    [Appid]_Controller#class に定められたクラスキー
     *                          このキーは大文字小文字を区別する
     *                          (配列のキーとして使われているため)
     *  @param  bool    $ext    オブジェクトが未生成の場合の強制生成フラグ(default: false)
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function getObject($key, $ext = false)
    {
        $object = null;

        $ext = to_array($ext);
        if (isset($this->class[$key]) == false) {
            // app object
            $class_name = $this->controller->getObjectClassName($key);
            $ext = array_pad($ext, 3, null);
            list($key_type, $key_value, $prop) = $ext;
        } else {
            // ethna classes
            $class_name = $this->class[$key];
            $ext = array_pad($ext, 1, null);
            list($weak) = $ext;
        }

        //  すでにincludeされていなければ、includeを試みる
        if (class_exists($class_name) == false) {
            if ($this->_include($class_name) == false) {
                return $object;  //  include 失敗。返り値はnull
            }
        }

        //  AppObject をはじめに扱う
        //  AppObject はキャッシュされないことに注意
        if (isset($this->class[$key]) == false) {
            $backend = $this->controller->getBackend();
            $object = new $class_name($backend, $key_type, $key_value, $prop);
            return $object;
        }

        //  Ethna_Controllerで定義されたクラスキーの場合
        //  はメソッド情報を集める
        if (isset($this->method_list[$class_name]) == false) {
            $this->method_list[$class_name] = get_class_methods($class_name);
            for ($i = 0; $i < count($this->method_list[$class_name]); $i++) {
                $this->method_list[$class_name][$i] = strtolower($this->method_list[$class_name][$i]);
            }
        }

        //  以下のルールに従って、キャッシュが利用可能かを判定する
        //  利用可能と判断した場合、キャッシュされていればそれを返す
        //
        //  1. メソッドに getInstance があればキャッシュを利用可能と判断する
        //     この場合、シングルトンかどうかは getInstance 次第
        //  2. weak が true であれば、キャッシュは利用不能と判断してオブジェクトを再生成
        //  3. weak が false であれば、キャッシュは利用可能と判断する(デフォルト)
        if ($this->_isCacheAvailable($class_name, $this->method_list[$class_name], $weak)) {
            if (isset($this->object[$key]) && is_object($this->object[$key])) {
                return $this->object[$key];
            }
        }

        //  インスタンス化のヘルパがあればそれを使う
        $method = sprintf('_getObject_%s', ucfirst($key));
        if (method_exists($this, $method)) {
            $object = $this->$method($class_name);
        } else if (in_array("getinstance", $this->method_list[$class_name])) {
            $object = call_user_func(array($class_name, 'getInstance'));
        } else {
            $object = new $class_name();
        }

        //  クラスキーに定められたクラスのインスタンスは
        //  とりあえずキャッシュする
        if (isset($this->object[$key]) == false || is_object($this->object[$key]) == false) {
            $this->object[$key] = $object;
        }

        return $object;
    }

    /**
     *  クラスキーに対応するクラス名を返す
     *
     *  @access public
     *  @param  string  $key    クラスキー
     *  @return string  クラス名
     */
    function getObjectName($key)
    {
        if (isset($this->class[$key]) == false) {
            return null;
        }

        return $this->class[$key];
    }

    /**
     *  オブジェクト生成メソッド(backend)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Backend($class_name)
    {
        $_ret_object = new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(config)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Config($class_name)
    {
        $_ret_object = new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(i18n)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_I18n($class_name)
    {
        $_ret_object = new $class_name($this->ctl->getDirectory('locale'), $this->ctl->getAppId());
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(logger)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Logger($class_name)
    {
        $_ret_object = new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(plugin)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Plugin($class_name)
    {
        $_ret_object = new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(renderer)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Renderer($class_name)
    {
        $_ret_object = new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(session)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Session($class_name)
    {
        $_ret_object = new $class_name($this->ctl, $this->ctl->getAppId());
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(sql)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function _getObject_Sql($class_name)
    {
        $_ret_object = new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  指定されたクラスから想定されるファイルをincludeする
     *
     *  @access protected
     */
    function _include($class_name)
    {
        $file = sprintf("%s.%s", $class_name, $this->controller->getExt('php'));
        if (file_exists_ex($file)) {
            include_once $file;
            return true;
        }

        if (preg_match('/^(\w+?)_(.*)/', $class_name, $match)) {
            // try ethna app style
            // App_Foo_Bar_Baz -> Foo/Bar/App_Foo_Bar_Baz.php
            $tmp = explode("_", $match[2]);
            $tmp[count($tmp)-1] = $class_name;
            $file = sprintf('%s.%s',
                            implode(DIRECTORY_SEPARATOR, $tmp),
                            $this->controller->getExt('php'));
            if (file_exists_ex($file)) {
                include_once $file;
                return true;
            }

            // try ethna app & pear mixed style
            // App_Foo_Bar_Baz -> Foo/Bar/Baz.php
            $file = sprintf('%s.%s',
                            str_replace('_', DIRECTORY_SEPARATOR, $match[2]),
                            $this->controller->getExt('php'));
            if (file_exists_ex($file)) {
                include_once $file;
                return true;
            }

            // try ethna master style
            // Ethna_Foo_Bar -> class/Ethna/Foo/Bar.php
            $tmp = explode('_', $match[2]);
            array_unshift($tmp, 'Ethna', 'class');
            $file = sprintf('%s.%s',
                            implode(DIRECTORY_SEPARATOR, $tmp),
                            $this->controller->getExt('php'));
            if (file_exists_ex($file)) {
                include_once $file;
                return true;
            }

            // try pear style
            // Foo_Bar_Baz -> Foo/Bar/Baz.php
            $file = sprintf('%s.%s',
                            str_replace('_', DIRECTORY_SEPARATOR, $class_name),
                            $this->controller->getExt('php'));
            if (file_exists_ex($file)) {
                include_once $file;
                return true;
            }
        }
        return false;
    }

    /**
     *  指定されたクラスがキャッシュを利用可能かどうかをチェックする
     *
     *  @access protected
     */
    function _isCacheAvailable($class_name, $method_list, $weak)
    {
        // if we have getInstance(), use this anyway
        if (in_array('getinstance', $method_list)) {
            return false;
        }

        // if not, see if weak or not
        return $weak ? false : true;
    }
}
// }}}
