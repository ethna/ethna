<?php
// vim: foldmethod=marker
/**
 *  Handle.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Handle
/**
 *  Manager class of Ethna (Command Line) Handlers
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Handle
{
    /**#@+
     *  @access     private
     */

    /** @protected    object  Ethna_Controller    controllerオブジェクト */
    protected $controller;

    /** @protected    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    protected $ctl;

    /** @protected    object  Ethna_Pluguin       pluginオブジェクト */
    protected $plugin;

    /**#@-*/

    // {{{ constructor
    /**
     *  Ethna_Handle constructor
     *
     *  @access public
     */
    public function __construct()
    {
        $this->controller = new Ethna_Controller(GATEWAY_CLI);
        Ethna::clearErrorCallback();
        Ethna::setErrorCallback(array('Ethna_Handle', 'handleError'));

        $this->ctl = $this->controller;
        $this->plugin = $this->controller->getPlugin();
    }
    // }}}

    // {{{ getHandler
    /**
     *  get handler object
     *
     *  @access public
     */
    public function getHandler($id)
    {
        $name = preg_replace('/\-(.)/e', "strtoupper('\$1')", ucfirst($id));
        $handler = $this->plugin->getPlugin('Handle', $name);
        if (Ethna::isError($handler)) {
            return $handler;
        }

        return $handler;
    }
    // }}}

    // {{{ getHandlerList
    /**
     *  get an object list of all available handlers
     *
     *  @access public
     */
    public function getHandlerList()
    {
        $handler_list = $this->plugin->getPluginList('Handle');
        usort($handler_list, array($this, "_handler_sort_callback"));

        return $handler_list;
    }

    /**
     *  sort callback method
     */
    public static function _handler_sort_callback($a, $b)
    {
        return strcmp($a->getId(), $b->getId());
    }
    // }}}

    // {{{ getEthnaController
    /**
     *  Ethna_Controllerのインスタンスを取得する
     *  (Ethna_Handlerの文脈で呼び出されることが前提)
     *
     *  @access public
     *  @static
     */
    public static function getEthnaController()
    {
        return Ethna_Controller::getInstance();
    }
    // }}}

    // {{{ getAppController
    /**
     *  アプリケーションのコントローラファイル/クラスを検索する
     *
     *  @access public
     *  @static
     */
    public static function getAppController($app_dir = null)
    {
        static $app_controller = array();

        if (isset($app_controller[$app_dir])) {
            return $app_controller[$app_dir];
        } else if ($app_dir === null) {
            return Ethna::raiseError('$app_dir not specified.');
        }

        $ini_file = null;
        while (is_dir($app_dir)) {
            if (is_file("$app_dir/.ethna")) {
                $ini_file = "$app_dir/.ethna";
                break;
            }
            $app_dir = dirname($app_dir);
            if (Ethna_Util::isRootDir($app_dir)) {
                break;
            }
        }

        if ($ini_file === null) {
            return Ethna::raiseError('no .ethna file found');
        }
        
        $macro = parse_ini_file($ini_file);
        if (isset($macro['controller_file']) == false
            || isset($macro['controller_class']) == false) {
            return Ethna::raiseError('invalid .ethna file');
        }
        $file = $macro['controller_file'];
        $class = $macro['controller_class'];

        $controller_file = "$app_dir/$file";
        if (is_file($controller_file) == false) {
            return Ethna::raiseError("no such file $controller_file");
        }

        include_once $controller_file;
        if (class_exists($class) == false) {
            return Ethna::raiseError("no such class $class");
        }

        $global_controller = $GLOBALS['_Ethna_controller'];
        $app_controller[$app_dir] = new $class(GATEWAY_CLI);
        $GLOBALS['_Ethna_controller'] = $global_controller;
        Ethna::clearErrorCallback();
        Ethna::setErrorCallback(array('Ethna_Handle', 'handleError'));

        return $app_controller[$app_dir];
    }
    // }}}

    // {{{ getMasterSetting
    /**
     *  Ethna 本体の設定を取得する (ethnaコマンド用)
     *
     *  @param  $section    ini ファイルの section
     *  @access public
     */
    public static function getMasterSetting($section = null)
    {
        static $setting = null;
        if ($setting === null) {
            $ini_file = ETHNA_BASE . "/.ethna";
            if (is_file($ini_file) && is_readable($ini_file)) {
                $setting = parse_ini_file($ini_file, true);
            } else {
                $setting = array();
            }
        }

        if ($section === null) {
            return $setting;
        } else if (array_key_exists($section, $setting)) {
            return $setting[$section];
        } else {
            $array = array();
            return $array;
        }
    }
    // }}}

    // {{{ handleError
    /**
     *  Ethna コマンドでのエラーハンドリング
     */
    public static function handleError($eobj)
    {
        // do nothing.
    }
    // }}}
}
// }}}
