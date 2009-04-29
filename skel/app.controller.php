<?php
/**
 *  {$project_id}_Controller.php
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/** Application base directory */
define('BASE', dirname(dirname(__FILE__)));

/** include_path setting (adding "/app" and "/lib" directory to include_path) */
$app = BASE . "/app";
$lib = BASE . "/lib";
set_include_path(implode(PATH_SEPARATOR, array($app, $lib)) . PATH_SEPARATOR . get_include_path());


/** including application library. */
require_once 'Ethna/Ethna.php';
require_once '{$project_id}_Error.php';
require_once '{$project_id}_ActionClass.php';
require_once '{$project_id}_ActionForm.php';
require_once '{$project_id}_ViewClass.php';

/**
 *  {$project_id} application Controller definition.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$project_id}_Controller extends Ethna_Controller
{
    /**#@+
     *  @access private
     */

    /**
     *  @var    string  Application ID(appid)
     */
    var $appid = '{$application_id}';

    /**
     *  @var    array   forward definition.
     */
    var $forward = array(
        /*
         *  TODO: write forward definition here.
         *
         *  Example:
         *
         *  'index'         => array(
         *      'view_name' => '{$project_id}_View_Index',
         *  ),
         */
    );

    /**
     *  @var    array   action definition.
     */
    var $action = array(
        /*
         *  TODO: write action definition here.
         *
         *  Example:
         *
         *  'index'     => array(
         *      'form_name' => 'Sample_Form_SomeAction',
         *      'form_path' => 'Some/Action.php',
         *      'class_name' => 'Sample_Action_SomeAction',
         *      'class_path' => 'Some/Action.php',
         *  ),
         */
    );

    /**
     *  @var    array   SOAP action definition.
     */
    var $soap_action = array(
        /*
         *  TODO: write action definition for SOAP application here.
         *  Example:
         *
         *  'sample'            => array(),
         */
    );

    /**
     *  @var    array       application directory.
     */
    var $directory = array(
        'action'        => 'app/action',
        'action_cli'    => 'app/action_cli',
        'action_xmlrpc' => 'app/action_xmlrpc',
        'app'           => 'app',
        'plugin'        => 'app/plugin',
        'bin'           => 'bin',
        'etc'           => 'etc',
        'filter'        => 'app/filter',
        'locale'        => 'locale',
        'log'           => 'log',
        'plugins'       => array('app/plugin/Smarty',),
        'template'      => 'template',
        'template_c'    => 'tmp',
        'tmp'           => 'tmp',
        'view'          => 'app/view',
        'www'           => 'www',
        'test'          => 'app/test',
    );

    /**
     *  @var    array       database access definition.
     */
    var $db = array(
        ''              => DB_TYPE_RW,
    );

    /**
     *  @var    array       extention(.php, etc) configuration.
     */
    var $ext = array(
        'php'           => 'php',
        'tpl'           => 'tpl',
    );

    /**
     *  @var    array   class definition.
     */
    var $class = array(
        /*
         *  TODO: When you override Configuration class, Logger class,
         *        SQL class, don't forget to change definition as follows!
         */
        'class'         => 'Ethna_ClassFactory',
        'backend'       => 'Ethna_Backend',
        'config'        => 'Ethna_Config',
        'db'            => 'Ethna_DB_PEAR',
        'error'         => 'Ethna_ActionError',
        'form'          => '{$project_id}_ActionForm',
        'i18n'          => 'Ethna_I18N',
        'logger'        => 'Ethna_Logger',
        'plugin'        => 'Ethna_Plugin',
        'session'       => 'Ethna_Session',
        'sql'           => 'Ethna_AppSQL',
        'view'          => '{$project_id}_ViewClass',
        'renderer'      => 'Ethna_Renderer_Smarty',
        'url_handler'   => '{$project_id}_UrlHandler',
    );

    /**
     *  @var    array       list of application id where Ethna searches plugin.
     */
    var $plugin_search_appids = array(
        /*
         *  write list of application id where Ethna searches plugin.
         *
         *  Example:
         *  When there are plugins whose name are like "Common_Plugin_Foo_Bar" in
         *  application plugin directory, Ethna searches them in the following order.
         *
         *  1. Common_Plugin_Foo_Bar,
         *  2. {$project_id}_Plugin_Foo_Bar
         *  3. Ethna_Plugin_Foo_Bar
         *
         *  'Common', '{$project_id}', 'Ethna',
         */
        '{$project_id}', 'Ethna',
    );

    /**
     *  @var    array       filter definition.
     */
    var $filter = array(
        /*
         *  TODO: when you use filter, write filter plugin name here.
         *  (If you specify class name, Ethna reads filter class in 
         *   filter directory)
         *
         *  Example:
         *
         *  'ExecutionTime',
         */
    );

    /**
     *  @var    array   smarty modifier definition.
     */
    var $smarty_modifier_plugin = array(
        /*
         *  TODO: write user defined smarty modifier here.
         *
         *  Example:
         *
         *  'smarty_modifier_foo_bar',
         */
    );

    /**
     *  @var    array   smarty function definition.
     */
    var $smarty_function_plugin = array(
        /*
         *  TODO: write user defined smarty function here.
         *
         *  Example:
         *
         *  'smarty_function_foo_bar',
         */
    );

    /**
     *  @var    array   smarty block definition.
     */
    var $smarty_block_plugin = array(
        /*
         *  TODO: write user defined smarty block here.
         *
         *  Example:
         * 
         *  'smarty_block_foo_bar',
         */
    );

    /**
     *  @var    array   smarty prefilter definition.
     */
    var $smarty_prefilter_plugin = array(
        /*
         *  TODO: write user defined smarty prefilter here.
         *
         *  Example:
         *
         *  'smarty_prefilter_foo_bar',
         */
    );

    /**
     *  @var    array   smarty postfilter definition.
     */
    var $smarty_postfilter_plugin = array(
        /*
         *  TODO: write user defined smarty postfilter here.
         *
         *  Example:
         *
         *  'smarty_postfilter_foo_bar',
         */
    );

    /**
     *  @var    array   smarty outputfilter definition.
     */
    var $smarty_outputfilter_plugin = array(
        /*
         *  TODO: write user defined smarty outputfilter here.
         *
         *  Example:
         *
         *  'smarty_outputfilter_foo_bar',
         */
    );

    /**#@-*/

    /**
     *  Get Default language and locale setting.
     *  If you want to change Ethna's output encoding, override this method.
     *
     *  @access protected
     *  @return array   locale name(e.x ja_JP, en_US .etc),
     *                  system encoding name,
     *                  client encoding name(= template encoding)
     *                  (locale name is "ll_cc" format. ll = language code. cc = country code.)
     */
    function _getDefaultLanguage()
    {
        return array('{$locale}', 'UTF-8', '{$client_enc}');
    }

    /**
     *  テンプレートエンジンのデフォルト状態を設定する
     *
     *  @access protected
     *  @param  object  Ethna_Renderer  レンダラオブジェクト
     *  @obsolete
     */
    function _setDefaultTemplateEngine(&$renderer)
    {
    }
}

?>
