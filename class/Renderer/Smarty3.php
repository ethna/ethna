<?php
// vim: foldmethod=marker
/**
 *  Smarty3.php
 *
 *  @author     Sotaro Karasawa <sotaro.k@gmail.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Renderer_Smarty3
/**
 *  Smarty 3.x
 *
 *  @author     Sotaro Karasawa <sotaro.k@gmail.com>
 *  @package    Ethna
 */
class Ethna_Renderer_Smarty3 extends Ethna_Renderer
{
    /** @private    string compile directory  */
    private $compile_dir;

    /**
     *  Constructor for Ethna_Renderer_Smarty3
     *
     *  @access public
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        // get renderer config
        $smarty_config = $this->config;
        $this->loadEngine($smarty_config);

        $this->engine = new Smarty();

        // Configurerd by controller
        $template_dir = $controller->getTemplatedir();
        $compile_dir = $controller->getDirectory('template_c');

        $this->setTemplateDir($template_dir);
        $this->compile_dir = $compile_dir;

        $this->engine->setTemplateDir($template_dir);
        $this->engine->setCompileDir($compile_dir);
        $this->engine->compile_id = md5($this->template_dir);

        if (isset($smarty_config['left_delimiter'])) {
            $this->engine->left_delimiter = $smarty_config['left_delimiter'];
        }
        if (isset($smarty_config['right_delimiter'])) {
            $this->engine->right_delimiter = $smarty_config['right_delimiter'];
        }

        // make compile dir
        if (is_dir($this->engine->getCompileDir()) === false) {
            Ethna_Util::mkdir($this->engine->getCompileDir(), 0755);
        }

        $this->engine->setPluginsDir(array_merge(
            $controller->getDirectory('plugins'),
            array(ETHNA_BASE . '/class/Plugin/Smarty', SMARTY_DIR . 'plugins')
        ));
    }

    public function getName()
    {
        return 'smarty3';
    }

    /**
     *  Display the template
     *
     *  @param  string  $template   template name
     *  @param  bool    $capture    if true, not display but return as string
     *
     *  @access public
     */
    public function perform($template = null, $capture = false)
    {
        if ($template === null && $this->template === null) {
            return Ethna::raiseWarning('template is not defined');
        }

        if ($template !== null) {
            $this->template = $template;
        }

        try {
            if ((is_absolute_path($this->template) && is_readable($this->template))
                || is_readable($this->template_dir . $this->template)) {
                    if ($capture === true) {
                        $captured = $this->engine->fetch($this->template);
                        return $captured;
                    } else {
                        $this->engine->display($this->template);
                    }
            } else {
                return Ethna::raiseWarning('template not found ' . $this->template);
            }
        } catch (SmartyCompilerException $e) {
            return Ethna::raiseWarning("smarty compile error: msg='{$e->getMessage()}'", 500);
        }
    }

    /**
     * get tamplate variable
     *
     *  @param      string      $name  variable name
     *  @return     mixed       variables
     *  @access     public
     */
    public function getProp($name = null)
    {
        $property = $this->engine->get_template_vars($name);

        if ($property !== null) {
            return $property;
        }
        return null;
    }

    /**
     *  remove template variable
     *
     *  @param  name    variable name
     *
     *  @access public
     */
    public function removeProp($name)
    {
        $this->engine->clear_assign($name);
    }

    /**
     *  set array to template variable
     *
     *  @param  array   $array
     *
     *  @access public
     */
    public function setPropArray($array)
    {
        $this->engine->assign($array);
    }

    /**
     *  set array to template variable by reference
     *
     *  @param  array   $array
     *  @access public
     */
    public function setPropArrayByRef(&$array)
    {
        $this->engine->assignByRef($array);
    }

    /**
     *  set template variable
     *
     *  @param  string  $name   variable name
     *  @param  mixed   $value  value
     *
     *  @access public
     */
    public function setProp($name, $value)
    {
        $this->engine->assign($name, $value);
    }

    /**
     *  set template variable by reference
     *
     *  @param  string  $name   variable name
     *  @param  mixed   $value  value
     *
     *  @access public
     */
    public function setPropByRef($name, &$value)
    {
        $this->engine->assignByRef($name, $value);
    }

    /**
     *  プラグインをセットする
     *
     *  @param  string  $name   plugin name
     *  @param  string  $type   plugin type
     *  @param  mixed   $plugin plugin
     *  @TODO   i don't know whether this is working or not
     *  @access public
     */
    public function setPlugin($name, $type, $plugin)
    {
        //プラグイン関数の有無をチェック
        if (is_callable($plugin) === false) {
            return Ethna::raiseWarning('Does not exists.');
        }

        //プラグインの種類をチェック
        $register_method = 'register_' . $type;
        if (method_exists($this->engine, $register_method) === false) {
            return Ethna::raiseWarning('This plugin type does not exist');
        }

        // フィルタは名前なしで登録
        if ($type === 'prefilter' || $type === 'postfilter' || $type === 'outputfilter') {
            parent::setPlugin($name, $type, $plugin);
            $this->engine->$register_method($plugin);
            return;
        }

        // プラグインの名前をチェック
        if ($name === '') {
            return Ethna::raiseWarning('Please set plugin name');
        }

        // プラグインを登録する
        parent::setPlugin($name, $type, $plugin);
        $this->engine->$register_method($name, $plugin);
    }

    /**
     * compiled template used by i18n command
     *
     * @return string or Ethna_Error
     */
    public function getCompiledContent($file)
    {
        $engine = $this->getEngine();
        $tpl = $engine->createTemplate($file);

        $compiled = $tpl->source->getCompiled($tpl);
        if (!$compiled->isCompiled) {
            $tpl->compileTemplateSource();
        }

        $compile_result = file_get_contents($compiled->filepath);
        if (empty($compile_result)) {
            return Ethna::raiseError(
                "Could not compile template file : $file"
            );
        }

        return $compile_result;
    }
}
// }}}
