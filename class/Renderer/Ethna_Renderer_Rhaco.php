<?php
/**
 *  Ethna_Renderer_Rhaco.php (experimental)
 *
 *  @author     TSURUOKA Naoya <tsuruoka@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

set_include_path(get_include_path() . PATH_SEPARATOR . BASE . '/lib/rhaco');

require_once 'rhaco/Rhaco.php';
require_once 'rhaco/tag/TemplateParser.php';
require_once ETHNA_BASE . '/class/Ethna_SmartyPlugin.php';

/**
 *  Rhacoレンダラクラス
 *
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Renderer_Rhaco extends Ethna_Renderer
{
    /** @var    string compile directory  */
    var $compile_dir = '';

    var $template_dir = '';

    /**
     * plugin list
     * @var     array
     * @access  protected
     */
    var $smarty_plugin_list = array();

    /**
     * Rhaco TemplateParser
     * @var     TemplateParser_Ethna $engine
     * @access  protected
     */
    var $engine;
    
    /**
     *  Ethna_Renderer_Rhacoクラスのコンストラクタ
     *
     *  @access public
     */
    function Ethna_Renderer_Rhaco(&$controller)
    {
        parent::Ethna_Renderer($controller);
        
        $this->template_dir = $controller->getTemplatedir() . '/';
        $this->compile_dir = $controller->getDirectory('template_c');

        Rhaco::constant('TEMPLATE_PATH', $this->template_dir);
        
        $this->engine = new TemplateParser_Ethna();

        /*
        $this->setTemplateDir($template_dir);
        $this->compile_dir = $compile_dir;
         */
        $this->engine->template_dir = $this->template_dir;
        $this->engine->compile_dir = $this->compile_dir;
        $this->engine->compile_id = md5($this->template_dir);

        // 一応がんばってみる
        if (is_dir($this->engine->compile_dir) === false) {
            Ethna_Util::mkdir($this->engine->compile_dir, 0755);
        }

        $this->_setDefaultPlugin();
    }
    
    /**
     *  ビューを出力する
     *
     *  @access public
     *  @param  string  $template   テンプレート名
     *  @param  bool    $capture    true ならば出力を表示せずに返す
     */
    function perform($template = null, $capture = false)
    {
        if ($template === null && $this->template === null) {
            return Ethna::raiseWarning('template is not defined');
        }

        if ($template !== null) {
            $this->template = $template;
        }

        if ((is_absolute_path($this->template) && is_readable($this->template))
            || is_readable($this->template_dir . $this->template)) {
                if ($capture === true) {
                    $captured = $this->engine->read($this->template);
                    return $captured;
                } else {
                    $captured = $this->engine->read($this->template);
                    print($captured);
                }
        } else {
            return Ethna::raiseWarning('template not found ' . $this->template);
        }
    }
    
    /**
     * テンプレート変数を取得する
     * 
     *  @todo fixme
     *  @access public
     *  @param string $name  変数名
     *  @return mixed　変数
     */
    function &getProp($name = null)
    {
        $property =& $this->engine->variables[$name];

        if ($property !== null) {
            return $property;
        }

        return null;
    }

    /**
     *  テンプレート変数を削除する
     * 
     *  @param name    変数名
     *  @todo
     *  @access public
     */
    function removeProp()
    {
        $this->engine->clearVariable(func_num_args());
    }

    /**
     *  テンプレート変数に配列を割り当てる
     * 
     *  @param array $array
     *  @access public
     */
    function setPropArray($array)
    {
        $this->engine->setVariable($array);
    }

    /**
     *  テンプレート変数に配列を参照として割り当てる
     * 
     *  @param array $array
     *  @todo no implement
     *  @access public
     */
    function setPropArrayByRef(&$array)
    {
        //$this->engine->assign_by_ref($array);
    }

    /**
     *  テンプレート変数を割り当てる
     * 
     *  @param string $name 変数名
     *  @param mixed $value 値
     * 
     *  @access public
     */
    function setProp($name, $value)
    {
        $this->engine->setVariable($name, $value);
    }

    /**
     *  テンプレート変数に参照を割り当てる
     * 
     *  @access public
     *  @todo fixme
     *  @param string $name 変数名
     *  @param mixed $value 値
     */
    function setPropByRef($name, &$value)
    {
        $this->engine->setVariable($name, $value);
        //$this->engine->assign_by_ref($name, $value);
    }

    /**
     * setPlugin
     *
     * @access public
     */
    function setPlugin($name, $type, $plugin)
    {
        //Smartyプラグイン関数の有無をチェック
        if (is_callable($plugin) === false) {
            return Ethna::raiseWarning('Does not exists.');
        }

        $this->smarty_plugin_list[$name] = array(
            'plugin' => $plugin,
            'type' => $type
        );
    }

    /**
     * getPluginList
     *
     * @access public
     */
    function getPluginList()
    {
        return $this->smarty_plugin_list;
    }

    /**
     *  デフォルトの設定.
     *
     *  @access public
     */
    function _setDefaultPlugin()
    {
        /*
        // default modifiers
        $this->setPlugin('number_format','modifier','smarty_modifier_number_format');
        $this->setPlugin('strftime','modifier','smarty_modifier_strftime');
        $this->setPlugin('count','modifier','smarty_modifier_count');
        $this->setPlugin('join','modifier','smarty_modifier_join');
        $this->setPlugin('filter','modifier', 'smarty_modifier_filter');
        $this->setPlugin('unique','modifier','smarty_modifier_unique');
        $this->setPlugin('wordwrap_i18n','modifier','smarty_modifier_wordwrap_i18n');
        $this->setPlugin('truncate_i18n','modifier','smarty_modifier_truncate_i18n');
        $this->setPlugin('i18n','modifier','smarty_modifier_i18n');
        $this->setPlugin('checkbox','modifier','smarty_modifier_checkbox');
        $this->setPlugin('select','modifier','smarty_modifier_select');
        $this->setPlugin('form_value','modifier','smarty_modifier_form_value');
         */

        // default functions
        $this->setPlugin('is_error','function','smarty_function_is_error');
        $this->setPlugin('message','function','smarty_function_message');
        $this->setPlugin('uniqid','function','smarty_function_uniqid');
        $this->setPlugin('select','function','smarty_function_select');
        $this->setPlugin('checkbox_list','function','smarty_function_checkbox_list');
        $this->setPlugin('form_name','function','smarty_function_form_name');
        $this->setPlugin('form_input','function','smarty_function_form_input');
        $this->setPlugin('form_submit','function','smarty_function_form_submit');
        $this->setPlugin('url','function','smarty_function_url');
        $this->setPlugin('csrfid','function','smarty_function_csrfid');

        // default blocks
        $this->setPlugin('form','block','smarty_block_form');       

        $this->engine->setSmartyPluginList($this->getPluginList());
    }


}

/**
 * TemplateParser_Ethna
 */
class TemplateParser_Ethna extends TemplateParser
{
    /**
     * smarty_function list
     * @var     array
     * @access  protected
     */
    var $smarty_plugin_list = array();
    
    /**
     * fake property for Smaty
     *
     * @access public
     */
    var $_tag_stack = array();

    /**
     * setSmartyPluginList
     *
     */
    function setSmartyPluginList($plugin_list)
    {
        if (!is_array($plugin_list)) {
            return false;
        }
        $this->smarty_plugin_list = $plugin_list;
    }

    /**
     * getSmartyPluginList
     *
     * @access public
     */
    function getSmartyPluginList()
    {
        return $this->smarty_plugin_list;
    }

    /**
     * smarty_function dispatcher
     *
     * @access protected
     * @param string $src
     */
    function _exec9002_smartyfunctions($src)
    {
        $tag = new SimpleTag();
        $smarty_plugin_list = $this->getSmartyPluginList();

        foreach($smarty_plugin_list as $name => $plugin_config) {
            
            while ($tag->set($src, $this->_getTagName($name))) {

                if ($plugin_config['type'] == 'function') {

                    $param = $tag->toHash();
                    $src = str_replace(
                        $tag->getPlain(),
                        $plugin_config['plugin']($param, $this),
                        $src
                    );

                } else if ($plugin_config['type'] == 'block') {
                    
                    $repeat_before = true;
                    $repeat_after = false;
                    $param_list = $tag->getParameter();
                    foreach ($param_list as $param_tag) {
                        $param[$param_tag->getName()] = $param_tag->getValue();
                    }
                    $content = $tag->getValue();

                    //before(not return value)
                    $result = $plugin_config['plugin']($param, $content, $this, $repeat_before);

                    //after
                    $result = $plugin_config['plugin']($param, $content, $this, $repeat_after);
                    $src = str_replace(
                        $tag->getPlain(),
                        $result,
                        $src
                    );

                }
            }

        }

        return $src;
    }

    /**
     * TemplateParser _getTagName
     *
     * @access protected
     * @param string $value
     */
    function _getTagName($value)
    {
        return sprintf("rt:%s",$value);
    }
}

