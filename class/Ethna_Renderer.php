<?php
// vim: foldmethod=marker
/**
 *	Ethna_Renderer.php
 *
 *	@author		Kazuhiro Hosoi <hosoi@gree.co.jp>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Renderer
/**
 *	レンダラクラス（Mojaviのまね）
 *
 *	@author		Kazuhiro Hosoi <hosoi@gree.co.jp>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Renderer
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /** @var    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    var $ctl;

    /** @var    string  template directory  */
    var $template_dir;

    /** @var    string  template engine */
    var $engine;

    /** @var    string  template file */
    var $template;

    /** @var    string  テンプレート変数 */
    var $prop;
    
    /** @var    string  レンダラプラグイン(Ethna_Pluginとは関係なし) */
    var $plugin_registry;
    
    /**
     *  Ethna_Rendererクラスのコンストラクタ
     *
     *  @access public
     */
    function Ethna_Renderer(&$controller)
    {
        $this->controller =& $controller;
        $this->ctl =& $this->controller;
        $this->template_dir = null;
        $this->engine = null;
        $this->template = null;
        $this->prop = array();
        $this->plugin_registry = array();
    }

    /**
     *  ビューを出力する
     *
     *  @param string   $template   テンプレート
     *
     *  @access public
     */
    function perform($template = null)
    {
        if ($template == null && $this->template == null) {
            return Ethna::raiseWarning('template is not defined');
        }

        if ($template != null) {
            $this->template = $template;
        }

        // テンプレートの有無のチェック
        if (is_readable($this->template_dir . $this->template)) {
            require_once($this->template_dir . $this->template);
        } else {
            return Ethna::raiseWarning("template is not found: " . $this->template);
        }
    }

    /**
     *  テンプレートエンジンを取得する
     * 
     *  @return object   Template Engine.
     * 
     *  @access public
     */
    function &getEngine()
    {
        return $this->engine;
    }

    /**
     *  テンプレートディレクトリを取得する
     * 
     *  @return string   Template Directory
     * 
     *  @access public
     */
    function getTemplateDir()
    {
        return $this->template_dir;
    }

    /**
     *  テンプレート変数を取得する
     * 
     *  @param string $name  変数名
     * 
     *  @return mixed    変数
     * 
     *  @access public
     */
    function &getProp($name)
    {
        if (isset($this->prop[$name])) {
            return $this->prop[$name];
        }

        return null;
    }

    /**
     *  テンプレート変数を削除する
     * 
     *  @param name    変数名
     * 
     *  @access public
     */
    function &removeProp($name)
    {
        if (isset($this->prop[$name])) {
            unset($this->prop[$name]);
        }
    }

    /**
     *  テンプレート変数に配列を割り当てる
     * 
     *  @param array $array
     * 
     *  @access public
     */
    function setPropArray($array)
    {
        $this->prop = array_merge($this->prop, $array);
    }

    /**
     *  テンプレート変数に配列を参照として割り当てる
     * 
     *  @param array $array
     * 
     *  @access public
     */
    function setPropArrayByRef(&$array)
    {
        $keys  = array_keys($array);
        $count = sizeof($keys);

        for ($i = 0; $i < $count; $i++) {
            $this->prop[$keys[$i]] =& $array[$keys[$i]];
        }
    }

    /**
     * テンプレート変数を割り当てる
     * 
     * @param string $name 変数名
     * @param mixed $value 値
     * 
     * @access public
     */
    function setProp($name, $value)
    {
        $this->prop[$name] = $value;
    }

    /**
     * テンプレート変数に参照を割り当てる
     * 
     * @param string $name 変数名
     * @param mixed $value 値
     * 
     * @access public
     */
    function setPropByRef($name, &$value)
    {
        $this->prop[$name] =& $value;
    }

    /**
     * テンプレートを割り当てる
     * 
     * @param string $template テンプレート名
     * 
     * @access public
     */
    function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * テンプレートディレクトリを割り当てる
     * 
     * @param string $dir ディレクトリ名
     * 
     * @access public
     */
    function setTemplateDir($dir)
    {
        $this->template_dir = $dir;

        if (substr($this->template_dir, -1) != '/') {
            $this->template_dir .= '/';
        }
    }
    
    /**
     *  テンプレートの有無をチェックする
     * 
     *  @param string $template テンプレート名
     * 
     *  @access public
     */
    function templateExists($template)
    {
        if (substr($this->template_dir, -1) != '/') {
            $this->template_dir .= '/';
        }

        return (is_readable($this->template_dir . $template));
    }

    /**
     *  プラグインをセットする
     * 
     *  @param string $name　プラグイン名
     *  @param string $type プラグインタイプ
     *  @param string $plugin プラグイン本体
     * 
     *  @access public
     */
    function setPlugin($name, $type, $plugin)
    {
    	$this->plugin_registry[$type][$name] = $plugin;
    }
}
// }}}
?>
