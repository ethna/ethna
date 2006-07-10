<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin
/**
 *  プラグインクラス
 *  
 *  プラグインは $type (ex. validator) と $name (ex. Regexp) のペアで
 *  識別します。 $type は Ethna_Plugin_Validator のように自動で ucfirst
 *  されますが、 $name は case-sensitive なので注意してください。
 *  また、各 $type ごとに基底クラスが必要になります。
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Controller    コントローラオブジェクト */
    var $controller;

    /** @var    object  Ethna_Logger        ログオブジェクト */
    var $logger;

    /** @var    array   プラグインのオブジェクト(インスタンス)を保存する配列 */
    var $obj_registry = array();

    /** @var    array   プラグインのクラス名、ソースファイル名を保存する配列 */
    var $src_registry = array();

    /**#@-*/

    // {{{ コンストラクタ
    /**
     *  Ethna_Pluginのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller コントローラオブジェクト
     */
    function Ethna_Plugin(&$controller)
    {
        $this->controller =& $controller;
        $this->logger =& $controller->getLogger();
    }
    // }}}

    // {{{ プラグイン呼び出しインタフェース
    /**
     *  プラグインのインスタンスを取得
     *
     *  @access public
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     *  @return object  プラグインのインスタンス
     */
    function &getPlugin($type, $name)
    {
        return $this->_getPlugin(strtolower($type), $name);
    }

    /**
     *  ある種類 ($type) のプラグイン名 ($name) の全リストを取得
     *
     *  @access public
     *  @param  string  $type   プラグインの種類
     *  @return array   プラグインの名前 ($name) の配列
     */
    function getPluginList($type)
    {
        $plugin_list = array();

        $this->_searchAllPluginSrc($type);
        foreach ($this->src_registry[$type] as $name => $value) {
            $plugin_list[$name] =& $this->getPlugin($type, $name);
        }
        return $plugin_list;
    }
    // }}}

    // {{{ obj_registry のアクセサ
    /**
     *  プラグインのインスタンスをレジストリから取得
     *
     *  @access private
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     *  @return object  プラグインのインスタンス
     */
    function &_getPlugin($type, $name)
    {
        if (isset($this->obj_registry[$type]) == false) {
            $this->obj_registry[$type] = array();

            // プラグインの親クラスを(存在すれば)読み込み
            $base_src = ETHNA_BASE . '/class/Plugin/Ethna_Plugin_'.ucfirst($type).'.php';
            if (file_exists($base_src)) {
                include_once($base_src);
            }
        }

        // key がないときはプラグインをロードする
        if (array_key_exists($name, $this->obj_registry[$type]) == false) {
            $this->_loadPlugin($type, $name);
        }

        // null のときはロードに失敗している
        if (is_null($this->obj_registry[$type][$name])) {
            return Ethna::raiseWarning('plugin not found: type=%s, name=%s',
                                        E_PLUGIN_NOTFOUND, $type, $name);
        }

        // プラグインのインスタンスを返す
        return $this->obj_registry[$type][$name];
    }

    /**
     *  プラグインをincludeしてnewし，レジストリに登録
     *
     *  @access private
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     */
    function _loadPlugin($type, $name)
    {
        // プラグインのファイル名を取得
        $plugin_src = $this->_getPluginSrc($type, $name);
        if (is_null($plugin_src)) {
            $this->obj_registry[$type][$name] = null;
            return;
        }
        list($plugin_class, $plugin_file) = $plugin_src;

        // プラグインのファイルを読み込み
        include_once($plugin_file);
        if (class_exists($plugin_class) == false) {
            $this->logger->log(LOG_WARNING, 'plugin class [%s] is not found', $plugin_class);
            $this->obj_registry[$type][$name] = null;
            return;
        }

        // プラグイン作成
        $instance =& new $plugin_class($this->controller, $type, $name);
        if (is_object($instance) == false
            || strcasecmp(get_class($instance), $plugin_class) != 0) {
            $this->logger->log(LOG_WARNING, 'plugin [%s::%s] instantiation failed', $type, $name);
            $this->obj_registry[$type][$name] = null;
            return;
        }
        $this->obj_registry[$type][$name] =& $instance;
    }

    /**
     *  プラグインのインスタンスをレジストリから消す
     *
     *  @access private
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     */
    function _unloadPlugin($type, $name)
    {
        unset($this->obj_registry[$type][$name]);
    }
    // }}}

    // {{{ src_registry のアクセサ
    /**
     *  プラグインのソースファイル名とクラス名をレジストリから取得
     *
     *  @access private
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     *  @return array   ソースファイル名とクラス名からなる配列
     */
    function _getPluginSrc($type, $name)
    {
        if (isset($this->src_registry[$type]) == false) {
            $this->src_registry[$type] = array();
        }

        // key がないときはプラグインの検索をする
        if (array_key_exists($name, $this->src_registry[$type]) == false) {
            $this->_searchPluginSrc($type, $name);
        }

        // プラグインのソースを返す
        return $this->src_registry[$type][$name];
    }
    // }}}

    // {{{ プラグインファイル検索部分
    /**
     *  プラグインのクラス名、ディレクトリ、ファイル名を決定
     *
     *  @access private
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     *  @param  string  $appid  アプリケーションID
     *  @return array   プラグインのクラス名、ディレクトリ、ファイル名の配列
     */
    function _getPluginClassFile($type, $name, $appid)
    {
        $_type = ucfirst(strtolower($type));

        if ($appid == 'Ethna') {
            $ext = 'php';
            $dir = ETHNA_BASE . "/class/Plugin/{$_type}";
        } else {
            $ext = $this->controller->getExt('php');
            $dir = $this->controller->getDirectory('app_plugin') . "/{$_type}";
        }

        $class = "{$appid}_Plugin_{$_type}_{$name}";
        $file  = "{$class}.{$ext}";
        return array($class, $dir, $file);
    }

    /**
     *  アプリケーション, Ethna の順でプラグインのソースを検索する
     *
     *  @access private
     *  @param  string  $type   プラグインの種類
     *  @param  string  $name   プラグインの名前
     */
    function _searchPluginSrc($type, $name)
    {
        // さきにアプリケーションをさがし、見つかったらreturn
        $appid_list = array($this->controller->getAppId(), 'Ethna');

        foreach ($appid_list as $appid) {
            list($class, $dir, $file) = $this->_getPluginClassFile($type, $name, $appid);
            if (file_exists("{$dir}/{$file}")) {
                $this->logger->log(LOG_DEBUG, 'plugin found: %s', "{$dir}/{$file}");
                $this->src_registry[$type][$name] = array($class, "{$dir}/{$file}");
                return;
            }
        }

        // 見つからなかった
        $this->logger->log(LOG_WARNING, 'plugin file not found: type=%s, name=%s', $type, $name);
        $this->src_registry[$type][$name] = null;
    }

    /**
     *  指定された $type のプラグイン (のソース) をすべて検索する
     *
     *  @todo   InfoManager から呼び出すだけだが、public にすべき
     *  @access private
     *  @param  string  $type   プラグインの種類
     */
    function _searchAllPluginSrc($type)
    {
        if (isset($this->src_registry[$type]) == false) {
            $this->src_registry[$type] = array();
        }

        // さきにEthna本体を調べてからアプリケーションで上書きする
        $appid_list = array('Ethna', $this->controller->getAppId());
        $name_list = array();

        foreach ($appid_list as $appid) {
            list($class_regexp, $dir, $file_regexp)
                = $this->_getPluginClassFile($type, '([^_]+)', $appid);

            //ディレクトリの存在のチェック
            if (is_dir($dir) == false) {
                $this->logger->log(LOG_DEBUG, 'plugin directory not found: %s', $dir);
                continue;
            }

            // ディレクトリを開く
            $dh = opendir($dir);
            if (is_resource($dh) == false) {
                $this->logger->log(LOG_DEBUG, 'cannot open plugin directory: %s', $dir);
                continue;
            }

            // 条件にあう $name をリストに追加
            while (($file = readdir($dh)) !== false) {
                if (preg_match('#^'.$file_regexp.'$#', $file, $matches)
                        && file_exists("{$dir}/{$file}")) {
                    $name_list[$matches[1]] = true;
                }
            }
        }

        foreach (array_keys($name_list) as $name) {
            // 冗長だがもう一度探しなおす
            $this->_searchPluginSrc($type, $name);
        }
    }
    // }}}
}
// }}}
?>
