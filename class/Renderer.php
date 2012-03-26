<?php
// vim: foldmethod=marker
/**
 *  Renderer.php
 *
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Renderer
/**
 *  Template Renderer
 *
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Renderer
{
    /**#@+
     *  @access private
     */

    /** @protected    object  Ethna_Controller    controllerオブジェクト */
    protected $controller;

    /** @protected    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    protected $ctl;

    /** @protected    array   [appid]-ini.phpのレンダラ設定 */
    protected $config;

    /** @protected    array   default configuration for the renderer */
    protected $config_default = array();

    /** @protected    string  template directory  */
    protected $template_dir;

    /** @protected    string  template engine */
    protected $engine;

    /** @protected    string  path of template engine */
    protected $engine_path = false;

    /** @protected    string  template file */
    protected $template;

    /** @protected    string  テンプレート変数 */
    protected $prop;

    /** @protected    string  レンダラプラグイン(Ethna_Pluginとは関係なし) */
    protected $plugin_registry;

    /** @protected    object  Ethna_Logger    ログオブジェクト */
    protected $logger;


    /**
     *  Ethna_Rendererクラスのコンストラクタ
     *
     *  @access public
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->ctl = $this->controller;
        $this->engine = null;
        $this->template = null;
        $this->prop = array();
        $this->plugin_registry = array();

        $template_dir = $controller->getTemplatedir();
        $this->template_dir = $template_dir;

        // load configuration
        $config = $this->ctl->getConfig();
        $renderer_config = $config->get('renderer');
        $this->config = $this->mergeConfig(
            $this->config_default,
            (isset($renderer_config[$this->getName()]) ? $renderer_config[$this->getName()] : array())
        );

        $this->logger = $this->controller->getBackend()->getLogger();
    }

    /**
     *  getName
     *
     *  @return string  renreder name
     */
    public function getName()
    {
        return 'ethna';
    }

    /**
     * getConfig
     *
     * Get renderer configuration
     *
     * @return  array   renderer configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *  ビューを出力する
     *
     *  @param string   $template   テンプレート
     *  @param  bool    $capture    true ならば出力を表示せずに返す
     *
     *  @access public
     */
    public function perform($template = null, $capture = false)
    {
        if ($template == null && $this->template == null) {
            return Ethna::raiseWarning('template is not defined');
        }

        if ($template != null) {
            $this->template = $template;
        }

        // テンプレートの有無のチェック
        if (is_readable($this->template_dir . $this->template) === false) {
            return Ethna::raiseWarning("template is not found: " . $this->template);
        }

        extract($this->prop);
        if ($capture === true) {
            ob_start();
            include $this->template_dir . $this->template;
            $captured = ob_get_contents();
            ob_end_clean();
            return $captured;
        } else {
            include $this->template_dir . $this->template;
            return true;
        }
    }

    /**
     *  テンプレートエンジンを取得する
     *
     *  @return object   Template Engine.
     *
     *  @access public
     */
    public function getEngine()
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
    public function getTemplateDir()
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
    public function getProp($name)
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
    public function removeProp($name)
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
    public function setPropArray($array)
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
    public function setPropArrayByRef(&$array)
    {
        $keys  = array_keys($array);
        $count = sizeof($keys);

        for ($i = 0; $i < $count; $i++) {
            $this->prop[$keys[$i]] = $array[$keys[$i]];
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
    public function setProp($name, $value)
    {
        $this->prop[$name] = $value;
    }

    /**
     *  テンプレート変数に参照を割り当てる
     *
     *  @param string $name 変数名
     *  @param mixed $value 値
     *
     *  @access public
     */
    public function setPropByRef($name, &$value)
    {
        $this->prop[$name] = $value;
    }

    /**
     *  テンプレートを割り当てる
     *
     *  @param string $template テンプレート名
     *
     *  @access public
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     *  Get template name
     *
     *  @return string  template name
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     *  テンプレートディレクトリを割り当てる
     *
     *  @param string $dir ディレクトリ名
     *
     *  @access public
     */
    public function setTemplateDir($dir)
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
    public function templateExists($template)
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
    public function setPlugin($name, $type, $plugin)
    {
        $this->plugin_registry[$type][$name] = $plugin;
    }

    // {{{ proxy methods (for B.C.)
    /**
     *  テンプレート変数を割り当てる(後方互換)
     *
     *  @access public
     */
    public function assign($name, $value)
    {
        $this->setProp($name, $value);
    }
    // }}}

    /**
     *  テンプレート変数に参照を割り当てる(後方互換)
     *
     *  @access public
     */
    public function assign_by_ref($name, &$value)
    {
        $this->setPropByRef($name, $value);
    }

    /**
     *  ビューを出力する
     *
     *  @access public
     */
    public function display($template = null)
    {
        return $this->perform($template);
    }
    // }}}

    // {{{ loadEngine
    /**
     *  Load renderer engine class.
     *
     *  @param  array   $config     render config array. (i.e. config.renderer.renderer_name)
     *  @access public
     */
    protected function loadEngine(array $config)
    {
        // load template engine
        $engine_path = isset($config['path'])
            ? $config['path']
            : $this->engine_path;
        if ($engine_path) {
            if (file_exists_ex($engine_path)) {
                require_once $engine_path;
            }
            else {
                trigger_error("template engine is not available: path=" . $engine_path, E_USER_ERROR);
            }
        }
    }
    // }}}

    /**
     *  mergeConfig
     *
     *  Merge renderer configuration default and user config.
     */
    public function mergeConfig(array $config_default, array $user_config)
    {
        return array_merge(
            $config_default,
            $user_config
        );
    }

    /**
     * compiled template used by i18n command
     *
     * @return string or Ethna_Error
     */
    public function getCompiledContent($file)
    {
        return $this->template_dir . $this->template;
    }
}
// }}}
