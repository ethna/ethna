<?php
/**
 *  {$project_id}_Controller.php
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/** アプリケーションベースディレクトリ */
define('BASE', dirname(dirname(__FILE__)));

// include_pathの設定(アプリケーションディレクトリを追加)
$app = BASE . "/app";
$lib = BASE . "/lib";
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . implode(PATH_SEPARATOR, array($app, $lib)));


/** アプリケーションライブラリのインクルード */
include_once('Ethna/Ethna.php');
include_once('{$project_id}_Error.php');
include_once('{$project_id}_ActionClass.php');
include_once('{$project_id}_ActionForm.php');
include_once('{$project_id}_ViewClass.php');

/**
 *  {$project_id}アプリケーションのコントローラ定義
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
     *  @var    string  アプリケーションID
     */
    var $appid = '{$application_id}';

    /**
     *  @var    array   forward定義
     */
    var $forward = array(
        /*
         *  TODO: ここにforward先を記述してください
         *
         *  記述例：
         *
         *  'index'         => array(
         *      'view_name' => '{$project_id}_View_Index',
         *  ),
         */
    );

    /**
     *  @var    array   action定義
     */
    var $action = array(
        /*
         *  TODO: ここにaction定義を記述してください
         *
         *  記述例：
         *
         *  'index'     => array(),
         */
    );

    /**
     *  @var    array   soap action定義
     */
    var $soap_action = array(
        /*
         *  TODO: ここにSOAPアプリケーション用のaction定義を
         *  記述してください
         *  記述例：
         *
         *  'sample'            => array(),
         */
    );

    /**
     *  @var    array       アプリケーションディレクトリ
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
        'plugins'       => array(),
        'template'      => 'template',
        'template_c'    => 'tmp',
        'tmp'           => 'tmp',
        'view'          => 'app/view',
        'www'           => 'www',
    );

    /**
     *  @var    array       DBアクセス定義
     */
    var $db = array(
        ''              => DB_TYPE_RW,
    );

    /**
     *  @var    array       拡張子設定
     */
    var $ext = array(
        'php'           => 'php',
        'tpl'           => 'tpl',
    );

    /**
     *  @var    array   クラス定義
     */
    var $class = array(
        /*
         *  TODO: 設定クラス、ログクラス、SQLクラスをオーバーライド
         *  した場合は下記のクラス名を忘れずに変更してください
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
     *  @var    array       検索対象となるプラグインのアプリケーションIDのリスト
     */
    var $plugin_search_appids = array(
        /*
         *  プラグイン検索時に検索対象となるアプリケーションIDのリストを記述します。
         *
         *  記述例：
         *  Common_Plugin_Foo_Bar のような命名のプラグインがアプリケーションの
         *  プラグインディレクトリに存在する場合、以下のように指定すると
         *  Common_Plugin_Foo_Bar, {$project_id}_Plugin_Foo_Bar, Ethna_Plugin_Foo_Bar
         *  の順にプラグインが検索されます。 
         *
         *  'Common', '{$project_id}', 'Ethna',
         */
        '{$project_id}', 'Ethna',
    );

    /**
     *  @var    array       フィルタ設定
     */
    var $filter = array(
        /*
         *  TODO: フィルタを利用する場合はここにそのプラグイン名を
         *  記述してください
         *  (クラス名を指定するとfilterディレクトリからフィルタクラス
         *  を読み込みます)
         *
         *  記述例：
         *
         *  'ExecutionTime',
         */
    );

    /**
     *  @var    array   マネージャ一覧
     */
    var $manager = array(
        /*
         *  TODO: ここにアプリケーションのマネージャオブジェクト一覧を
         *  記述してください
         *
         *  記述例：
         *
         *  'um'    => 'User',
         */
    );

    /**
     *  @var    array   smarty modifier定義
     */
    var $smarty_modifier_plugin = array(
        /*
         *  TODO: ここにユーザ定義のsmarty modifier一覧を記述してください
         *
         *  記述例：
         *
         *  'smarty_modifier_foo_bar',
         */
    );

    /**
     *  @var    array   smarty function定義
     */
    var $smarty_function_plugin = array(
        /*
         *  TODO: ここにユーザ定義のsmarty function一覧を記述してください
         *
         *  記述例：
         *
         *  'smarty_function_foo_bar',
         */
    );

    /**
     *  @var    array   smarty block定義
     */
    var $smarty_block_plugin = array(
        /*
         *  TODO: ここにユーザ定義のsmarty block一覧を記述してください
         *
         *  記述例：
         *
         *  'smarty_block_foo_bar',
         */
    );

    /**
     *  @var    array   smarty prefilter定義
     */
    var $smarty_prefilter_plugin = array(
        /*
         *  TODO: ここにユーザ定義のsmarty prefilter一覧を記述してください
         *
         *  記述例：
         *
         *  'smarty_prefilter_foo_bar',
         */
    );

    /**
     *  @var    array   smarty postfilter定義
     */
    var $smarty_postfilter_plugin = array(
        /*
         *  TODO: ここにユーザ定義のsmarty postfilter一覧を記述してください
         *
         *  記述例：
         *
         *  'smarty_postfilter_foo_bar',
         */
    );

    /**
     *  @var    array   smarty outputfilter定義
     */
    var $smarty_outputfilter_plugin = array(
        /*
         *  TODO: ここにユーザ定義のsmarty outputfilter一覧を記述してください
         *
         *  記述例：
         *
         *  'smarty_outputfilter_foo_bar',
         */
    );

    /**#@-*/

    /**
     *  遷移時のデフォルトマクロを設定する
     *
     *  @access protected
     *  @param  object  Ethna_Renderer  レンダラオブジェクト
     *  @obsolete
     */
    function _setDefaultTemplateEngine(&$renderer)
    {
        // 可能であればビューの基底クラスを利用してください
    }
}
?>
