<?php
// vim: foldmethod=marker
/**
 *  Ethna_ClassFactory.php
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

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /** @var    object  Ethna_Controller    controllerオブジェクト(省略形) */
    var $ctl;
    
    /** @var    array   クラス定義 */
    var $class = array();

    /** @var    array   生成済みオブジェクトキャッシュ */
    var $object = array();

    /** @var    array   生成済みアプリケーションマネージャオブジェクトキャッシュ */
    var $manager = array();

    /** @var    array   メソッド一覧キャッシュ */
    var $method_list = array();

    /**#@-*/


    /**
     *  Ethna_ClassFactoryクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    controllerオブジェクト
     *  @param  array                       $class          クラス定義
     */
    function Ethna_ClassFactory(&$controller, $class)
    {
        $this->controller =& $controller;
        $this->ctl =& $controller;
        $this->class = $class;
    }

    /**
     *  typeに対応するアプリケーションマネージャオブジェクトを返す
     *
     *  @access public
     *  @return object  Ethna_AppManager    マネージャオブジェクト
     */
    function &getManager($type, $weak = false)
    {
        $class_name = $this->controller->getManagerClassName($type);

        // try to include if not defined
        if (class_exists($class_name) == false) {
            $this->_include($class_name);
        }

        if (isset($this->method_list[$class_name]) == false) {
            $this->method_list[$class_name] = get_class_methods($class_name);
            for ($i = 0; $i < count($this->method_list[$class_name]); $i++) {
                $this->method_list[$class_name][$i] = strtolower($this->method_list[$class_name][$i]);
            }
        }

        // see if this should be singlton or not
        if ($this->_isCacheAvailable($class_name, $this->method_list[$class_name], $weak)) {
            if (isset($this->manager[$type]) && is_object($this->manager[$type])) {
                return $this->manager[$type];
            }
        }

        // see if we have helper methods
        if (in_array("getinstance", $this->method_list[$class_name])) {
            $obj =& call_user_func(array($class_name, 'getInstance'));
        } else {
            $backend =& $this->controller->getBackend();
            $obj =& new $class_name($backend);
        }

        if (isset($this->manager[$type]) == false || is_object($this->manager[$type]) == false) {
            $this->manager[$type] =& $obj;
        }

        return $obj;
    }

    /**
     *  クラスキーに対応するオブジェクトを返す/クラスキーが未定義の場合はAppObjectを探す
     *
     *  @access public
     *  @param  string  $key    クラスキー
     *  @param  bool    $weak   オブジェクトが未生成の場合の強制生成フラグ(default: false)
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &getObject($key, $ext = false)
    {
        $object = null;

        $ext = to_array($ext);
        if (isset($this->class[$key]) == false) {
            // app object
            $class_name = $this->controller->getObjectClassName($key);
            $ext = array_pad($ext, 3, null);
            list($key_type, $key_value, $prop) = $ext;
        } else {
            $class_name = $this->class[$key];
            $ext = array_pad($ext, 1, null);
            list($weak) = $ext;
        }

        // try to include if not defined
        if (class_exists($class_name) == false) {
            if ($this->_include($class_name) == false) {
                return $object;
            }
        }

        // handle app object first
        if (isset($this->class[$key]) == false) {
            $backend =& $this->controller->getBackend();
            $object =& new $class_name($backend, $key_type, $key_value, $prop);
            return $object;
        }

        if (isset($this->method_list[$class_name]) == false) {
            $this->method_list[$class_name] = get_class_methods($class_name);
            for ($i = 0; $i < count($this->method_list[$class_name]); $i++) {
                $this->method_list[$class_name][$i] = strtolower($this->method_list[$class_name][$i]);
            }
        }

        // see if this should be singlton or not
        if ($this->_isCacheAvailable($class_name, $this->method_list[$class_name], $weak)) {
            if (isset($this->object[$key]) && is_object($this->object[$key])) {
                return $this->object[$key];
            }
        }

        // see if we have helper methods
        $method = sprintf('_getObject_%s', ucfirst($key));
        if (method_exists($this, $method)) {
            $object =& $this->$method($class_name);
        } else if (in_array("getinstance", $this->method_list[$class_name])) {
            $object =& call_user_func(array($class_name, 'getInstance'));
        } else {
            $object =& new $class_name();
        }

        if (isset($this->object[$key]) == false || is_object($this->object[$key]) == false) {
            $this->object[$key] =& $object;
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
    function &_getObject_Backend($class_name)
    {
        $_ret_object =& new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(config)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_Config($class_name)
    {
        $_ret_object =& new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(i18n)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_I18n($class_name)
    {
        $_ret_object =& new $class_name($this->ctl->getDirectory('locale'), $this->ctl->getAppId());
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(logger)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_Logger($class_name)
    {
        $_ret_object =& new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(plugin)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_Plugin($class_name)
    {
        $_ret_object =& new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(renderer)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_Renderer($class_name)
    {
        $_ret_object =& new $class_name($this->ctl);
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(session)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_Session($class_name)
    {
        $_ret_object =& new $class_name($this->ctl->getAppId(), $this->ctl->getDirectory('tmp'), $this->ctl->getLogger());
        return $_ret_object;
    }

    /**
     *  オブジェクト生成メソッド(sql)
     *
     *  @access protected
     *  @param  string  $class_name     クラス名
     *  @return object  生成されたオブジェクト(エラーならnull)
     */
    function &_getObject_Sql($class_name)
    {
        $_ret_object =& new $class_name($this->ctl);
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
            include_once($file);
            return true;
        }

        if (preg_match('/^(\w+?)_(.*)/', $class_name, $match)) {
            // try pear style
            $file = sprintf('%s.%s', str_replace('_', DIRECTORY_SEPARATOR, $class_name), $this->controller->getExt('php'));
            if (file_exists_ex($file)) {
                include_once($file);
                return true;
            }

            // try ethna style
            $tmp = explode("_", $match[2]);
            $tmp[count($tmp)-1] = $class_name;
            $file = sprintf('%s.%s', implode(DIRECTORY_SEPARATOR, $tmp), $this->controller->getExt('php'));
            if (file_exists_ex($file)) {
                include_once($file);
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
?>
