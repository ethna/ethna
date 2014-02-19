<?php
// vim: foldmethod=marker
/**
 *  Controller.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Controller
/**
 *  コントローラクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Controller
{
    /**#@+
     *  @access protected
     */

    /** @var string $appid     アプリケーションID */
    public $appid = 'ETHNA';

    /** @var string $base     アプリケーションベースディレクトリ */
    public $base = '';

    /** @var string $url     アプリケーションベースURL */
    public $url = '';

    /** @var string $dsn      アプリケーションDSN(Data Source Name) */
    public $dsn;

    /** @var array $directory      アプリケーションディレクトリ */
    public $directory = array();

    /** @var  array       アプリケーションディレクトリ(デフォルト) */
    public $directory_default = array(
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
        'test'          => 'app/test',
    );

    /** @var array $db      DBアクセス定義 */
    public $db = array(
        ''              => DB_TYPE_RW,
    );

    /** @var array $ext      拡張子設定 */
    public $ext = array(
        'php'           => 'php',
        'tpl'           => 'tpl',
    );

    /** @var array       クラス設定 */
    public $class = array();

    /** @var array $class_default       クラス設定(デフォルト) */
    public $class_default = array(
        'class'         => 'Ethna_ClassFactory',
        'backend'       => 'Ethna_Backend',
        'config'        => 'Ethna_Config',
        'db'            => 'Ethna_DB',
        'error'         => 'Ethna_ActionError',
        'form'          => 'Ethna_ActionForm',
        'i18n'          => 'Ethna_I18N',
        'logger'        => 'Ethna_Logger',
        'plugin'        => 'Ethna_Plugin',
        'renderer'      => 'Ethna_Renderer_Smarty',
        'session'       => 'Ethna_Session',
        'sql'           => 'Ethna_AppSQL',
        'view'          => 'Ethna_ViewClass',
        'url_handler'   => 'Ethna_UrlHandler',
    );

    /** @var array subscribers */
    protected $subscribers = array(
        //"Ethna_Subscriber_I18nSubscriber",
        "Ethna_Subscriber_ForwardSubscriber",
        "Ethna_Subscriber_ResolveActionNameSubscriber",
        "Ethna_Subscriber_TriggerSubscriber",
    );

    /** @var array       フィルタ設定 */
    public $filter = array(
    );

    /** @var string      使用ロケール設定 */
    public $locale;

    /** @var string      システム側エンコーディング */
    public $system_encoding;

    /** @protected    string      クライアント側エンコーディング */
    /**                     ブラウザからのエンコーディングを指す  */
    public $client_encoding;

    /** FIXME: UnitTestCase から動的に変更されるため、public */
    /** @protected    string  現在実行中のアクション名 */
    public $action_name;

    /** @var    string  現在実行中のXMLRPCメソッド名 */
    public $xmlrpc_method_name;

    /** @var    array   forward定義 */
    public $forward = array();

    /** @vr    array   デフォルトのforward定義 */
    public $forward_default = array(
        '403' => array( 'view_name' => 'Ethna_View_403',),
        '404' => array( 'view_name' => 'Ethna_View_404',),
        '500' => array( 'view_name' => 'Ethna_View_500',),
        'json' => array( 'view_name' => 'Ethna_View_Json',),
        'redirect' => array( 'view_name' => 'Ethna_View_Redirect',),
    );

    /** @var    array   action定義 */
    public $action = array();

    /** @var    array   action(CLI)定義 */
    public $action_cli = array();

    /** @var    array   action(XMLRPC)定義 */
    public $action_xmlrpc = array();

    /** @var    array   アプリケーションマネージャ定義 */
    public $manager = array();

    /** @var    object  レンダラー */
    public $renderer = null;

    /** @var    array   フィルターチェイン(Ethna_Filterオブジェクトの配列) */
    public $filter_chain = array();

    /** @var Ethna_ClassFactory $class_factory  クラスファクトリオブジェクト */
    public $class_factory = null;

    /** @var Ethna_ActionForm $action_form    フォームオブジェクト */
    public $action_form = null;

    /** @var Ethna_View $view         ビューオブジェクト */
    public $view = null;

    /** @var Ethna_Config $config       設定オブジェクト */
    public $config = null;

    /** @var Ethna_Logger $logger        ログオブジェクト */
    public $logger = null;

    /** @var Ethna_Plugin $plugin        プラグインオブジェクト */
    public $plugin = null;

    /** @var string $gateway リクエストのゲートウェイ(www/cli/rest/xmlrpc/soap...) */
    public $gateway = GATEWAY_WWW;

    /** @var  Ethna_EventDispatcher $event_dispatcher */
    protected $event_dispatcher;

    /**#@-*/


    /**
     *  Ethna_Controllerクラスのコンストラクタ
     *
     *  @access     public
     */
    public function __construct($gateway = GATEWAY_WWW)
    {
        $this->setupEventDispatcher();
        $this->registerSubscriber();

        $GLOBALS['_Ethna_controller'] = $this;
        if ($this->base === "") {
            // EthnaコマンドなどでBASEが定義されていない場合がある
            if (defined('BASE')) {
                $this->base = BASE;
            }
        }

        $this->gateway = $gateway;

        // クラス設定の未定義値を補完
        foreach ($this->class_default as $key => $val) {
            if (isset($this->class[$key]) == false) {
                $this->class[$key] = $val;
            }
        }

        // ディレクトリ設定の未定義値を補完
        foreach ($this->directory_default as $key => $val) {
            if (isset($this->directory[$key]) == false) {
                $this->directory[$key] = $val;
            }
        }

        // クラスファクトリオブジェクトの生成
        $class_factory = $this->class['class'];
        $this->class_factory = new $class_factory($this, $this->class);

        // エラーハンドラの設定
        Ethna::setErrorCallback(array($this, 'handleError'));

        // ディレクトリ名の設定(相対パス->絶対パス)
        foreach ($this->directory as $key => $value) {
            if ($key == 'plugins') {
                // Smartyプラグインディレクトリは配列で指定する
                $tmp = array();
                foreach (to_array($value) as $elt) {
                    if (Ethna_Util::isAbsolute($elt) == false) {
                        $tmp[] = $this->base . (empty($this->base) ? '' : '/') . $elt;
                    }
                }
                $this->directory[$key] = $tmp;
            } else {
                if (Ethna_Util::isAbsolute($value) == false) {
                    $this->directory[$key] = $this->base . (empty($this->base) ? '' : '/') . $value;
                }
            }
        }

        // 遷移先設定をマージ
        // 但し、キーは完全に維持する
        $this->forward = $this->forward + $this->forward_default;

        // 初期設定
        // フレームワークとしての内部エンコーディングはクライアント
        // エンコーディング（=ブラウザからのエンコーディング)
        //
        // @see Ethna_Controller#_getDefaultLanguage
        list($this->locale, $this->system_encoding, $this->client_encoding) = $this->_getDefaultLanguage();
        if (extension_loaded('mbstring')) {
            mb_internal_encoding($this->client_encoding);
            mb_regex_encoding($this->client_encoding);
        }

        $this->config = $this->getConfig();
        $this->dsn = $this->_prepareDSN();
        $this->url = $this->config->get('url');
        if (empty($this->url) && PHP_SAPI != 'cli') {
            $this->url = Ethna_Util::getUrlFromRequestUri();
            $this->config->set('url', $this->url);
        }

        // プラグインオブジェクトの用意
        $this->plugin = $this->getPlugin();

        // include Ethna_Plugin_Abstract for all plugins
        $this->plugin->includePlugin('Abstract');

        // ログ出力開始
        $this->logger = $this->getLogger();
        $this->plugin->setLogger($this->logger);
        $this->logger->begin();
    }

    /**
     * 内部イベントのsubscriberを登録する
     *
     * @return void
     */
    protected function registerSubscriber()
    {
        foreach ($this->subscribers as $subscriber_class) {
            $subscriber = new $subscriber_class();
            $this->getEventDispatcher()->addSubscriber($subscriber);
        }
    }

    /**
     * returns event dispatcher
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->event_dispatcher;
    }

    /**
     * setup Event Dispatcher
     */
    protected function setupEventDispatcher()
    {
        $this->event_dispatcher = new Ethna_EventDispatcher();
    }

    /**
     *  アプリケーション実行後の後始末を行います。
     *
     *  @access protected
     */
    public function end()
    {
        //  必要に応じてオーバライドして下さい。
        $this->logger->end();
    }

    /**
     *  (現在アクティブな)コントローラのインスタンスを返す
     *
     *  @access public
     *  @return object  Ethna_Controller    コントローラのインスタンス
     *  @static
     */
    public static function getInstance()
    {
        if (isset($GLOBALS['_Ethna_controller'])) {
            return $GLOBALS['_Ethna_controller'];
        } else {
            $_ret_object = null;
            return $_ret_object;
        }
    }

    /**
     *  アプリケーションIDを返す
     *
     *  @access public
     *  @return string  アプリケーションID
     */
    public function getAppId()
    {
        return ucfirst(strtolower($this->appid));
    }

    /**
     *  アプリケーションIDをチェックする
     *
     *  @access public
     *  @param  string  $id     アプリケーションID
     *  @return mixed   true:OK Ethna_Error:NG
     *  @static
     */
    public static function checkAppId($id)
    {
        $true = true;
        if (strcasecmp($id, 'ethna') === 0
            || strcasecmp($id, 'app') === 0) {
            return Ethna::raiseError("Application Id [$id] is reserved\n");
        }

        //    アプリケーションIDはクラス名のprefixともなるため、
        //    数字で始まっていてはいけない
        //    @see http://www.php.net/manual/en/language.variables.php
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $id) === 0) {
            $msg = (preg_match('/^[0-9]$/', $id[0]))
                 ? "Application ID must NOT start with Number.\n"
                 : "Only Numeric(0-9) and Alphabetical(A-Z) is allowed for Application Id\n";
            return Ethna::raiseError($msg);
        }
        return $true;
    }

    /**
     *  アクション名をチェックする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return mixed   true:OK Ethna_Error:NG
     *  @static
     */
    public static function checkActionName($action_name)
    {
        $true = true;
        if (preg_match('/^[a-zA-Z\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
                       $action_name) === 0) {
            return Ethna::raiseError("invalid action name [$action_name]");
        }
        return $true;
    }

    /**
     *  ビュー名をチェックする
     *
     *  @access public
     *  @param  string  $view_name    ビュー名
     *  @return mixed   true:OK Ethna_Error:NG
     *  @static
     */
    public static function checkViewName($view_name)
    {
        $true = true;
        if (preg_match('/^[a-zA-Z\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
                       $view_name) === 0) {
            return Ethna::raiseError("invalid view name [$view_name]");
        }
        return $true;
    }

    /**
     *  DSNを返す
     *
     *  @access public
     *  @param  string  $db_key DBキー
     *  @return string  DSN
     */
    public function getDSN($db_key = "")
    {
        if (isset($this->dsn[$db_key]) == false) {
            return null;
        }
        return $this->dsn[$db_key];
    }

    /**
     *  DSNの持続接続設定を返す
     *
     *  @access public
     *  @param  string  $db_key DBキー
     *  @return bool    true:persistent false:non-persistent(あるいは設定無し)
     */
    public function getDSN_persistent($db_key = "")
    {
        $key = sprintf("dsn%s_persistent", $db_key == "" ? "" : "_$db_key");

        $dsn_persistent = $this->config->get($key);
        if (is_null($dsn_persistent)) {
            return false;
        }
        return $dsn_persistent;
    }

    /**
     *  DB設定を返す
     *
     *  @access public
     *  @param  string  $db_key DBキー("", "r", "rw", "default", "blog_r"...)
     *  @return string  $db_keyに対応するDB種別定義(設定が無い場合はnull)
     */
    public function getDBType($db_key = null)
    {
        if (is_null($db_key)) {
            // 一覧を返す
            return $this->db;
        }

        if (isset($this->db[$db_key]) == false) {
            return null;
        }
        return $this->db[$db_key];
    }

    /**
     *  アプリケーションベースURLを返す
     *
     *  @access public
     *  @return string  アプリケーションベースURL
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     *  アプリケーションベースディレクトリを返す
     *
     *  @access public
     *  @return string  アプリケーションベースディレクトリ
     */
    public function getBasedir()
    {
        return $this->base;
    }

    /**
     *  クライアントタイプ/言語からテンプレートディレクトリ名を決定する
     *  デフォルトでは [appid]/template/ja_JP/ (ja_JPはロケール名)
     *  ロケール名は _getDefaultLanguage で決定される。
     *
     *  @access public
     *  @return string  テンプレートディレクトリ
     *  @see    Ethna_Controller#_getDefaultLanguage
     */
    public function getTemplatedir()
    {
        $template = $this->getDirectory('template');

        // 言語別ディレクトリ
        // _getDerfaultLanguageメソッドでロケールが指定されていた場合は、
        // テンプレートディレクトリにも自動的にそれを付加する。
        if (!empty($this->locale)) {
            $template .= '/' . $this->locale;
        }

        return $template;
    }

    /**
     *  アクションディレクトリ名を決定する
     *
     *  @access public
     *  @return string  アクションディレクトリ
     */
    public function getActiondir($gateway = null)
    {
        $key = 'action';
        $gateway = is_null($gateway) ? $this->getGateway() : $gateway;

        $result = $this->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_GATEWAY_ACTIONDIR,
            new Ethna_Event_Gateway($gateway));
        $key = $result->getActionDirKey();

        return (empty($this->directory[$key]) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory[$key] . "/"));
    }

    /**
     *  ビューディレクトリ名を決定する
     *
     *  @access public
     *  @return string  ビューディレクトリ
     */
    public function getViewdir()
    {
        return (empty($this->directory['view']) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory['view'] . "/"));
    }

    /**
     *  (action,view以外の)テストケースを置くディレクトリ名を決定する
     *
     *  @access public
     *  @return string  テストケースを置くディレクトリ
     */
    public function getTestdir()
    {
        return (empty($this->directory['test']) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory['test'] . "/"));
    }

    /**
     *  アプリケーションディレクトリ設定を返す
     *
     *  @access public
     *  @param  string  $key    ディレクトリタイプ("tmp", "template"...)
     *  @return string  $keyに対応したアプリケーションディレクトリ(設定が無い場合はnull)
     */
    public function getDirectory($key)
    {
        if (isset($this->directory[$key]) == false) {
            return null;
        }
        return $this->directory[$key];
    }
    /**
     *  アプリケーションディレクトリ設定を返す
     *
     *  @access public
     *  @param  string  $key    type
     *  @return string  $key    directory
     */
    public function setDirectory($key, $value)
    {
        $this->directory[$key] = $value;
    }


    /**
     *  アプリケーション拡張子設定を返す
     *
     *  @access public
     *  @param  string  $key    拡張子タイプ("php", "tpl"...)
     *  @return string  $keyに対応した拡張子(設定が無い場合はnull)
     */
    public function getExt($key)
    {
        if (isset($this->ext[$key]) == false) {
            return null;
        }
        return $this->ext[$key];
    }

    /**
     *  クラスファクトリオブジェクトのアクセサ(R)
     *
     *  @access public
     *  @return object  Ethna_ClassFactory  クラスファクトリオブジェクト
     */
    public function getClassFactory()
    {
        return $this->class_factory;
    }

    /**
     *  アクションエラーオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_ActionError   アクションエラーオブジェクト
     */
    public function getActionError()
    {
        return $this->class_factory->getObject('error');
    }

    /**
     *  Accessor for ActionForm
     *
     *  @access public
     *  @return object  Ethna_ActionForm    アクションフォームオブジェクト
     */
    public function getActionForm()
    {
        // 明示的にクラスファクトリを利用していない
        return $this->action_form;
    }

    public function setActionName($action_name)
    {
        $this->action_name = $action_name;
    }

    /**
     *  Setter for ActionForm
     *  if the ::$action_form class is not null, then cannot set the view
     *
     *  @access public
     *  @return object  Ethna_ActionForm    アクションフォームオブジェクト
     */
    public function setActionForm($af)
    {
        if ($this->action_form !== null) {
            return false;
        }
        $this->action_form = $af;
        return true;
    }


    /**
     *  Accessor for ViewClass
     *
     *  @access public
     *  @return object  Ethna_View          ビューオブジェクト
     */
    public function getView()
    {
        // 明示的にクラスファクトリを利用していない
        return $this->view;
    }

    /**
     *  Setter for ViewClass
     *  if the ::$view class is not null, then cannot set the view
     *
     *  @access public
     *  @param  $view object  Ethna_ViewClass
     *  @return boolean
     */
    public function setView($view)
    {
        if ($this->view !== null) {
            return false;
        }
        $this->view = $view;
        return true;
    }

    /**
     *  backendオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Backend   backendオブジェクト
     */
    public function getBackend()
    {
        return $this->class_factory->getObject('backend');
    }

    /**
     *  設定オブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Config    設定オブジェクト
     */
    public function getConfig()
    {
        return $this->class_factory->getObject('config');
    }

    /**
     *  i18nオブジェクトのアクセサ(R)
     *
     *  @access public
     *  @return object  Ethna_I18N  i18nオブジェクト
     */
    public function getI18N()
    {
        return $this->class_factory->getObject('i18n');
    }

    /**
     *  ログオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Logger        ログオブジェクト
     */
    public function getLogger()
    {
        return $this->class_factory->getObject('logger');
    }

    /**
     *  セッションオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Session       セッションオブジェクト
     */
    public function getSession()
    {
        return $this->class_factory->getObject('session');
    }

    /**
     *  SQLオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_AppSQL    SQLオブジェクト
     */
    public function getSQL()
    {
        return $this->class_factory->getObject('sql');
    }

    /**
     *  プラグインオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Plugin    プラグインオブジェクト
     */
    public function getPlugin()
    {
        return $this->class_factory->getObject('plugin');
    }

    /**
     *  URLハンドラオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_UrlHandler    URLハンドラオブジェクト
     */
    public function getUrlHandler()
    {
        return $this->class_factory->getObject('url_handler');
    }

    /**
     *  マネージャ一覧を返す
     *
     *  @access public
     *  @return array   マネージャ一覧
     *  @obsolete
     */
    public  function getManagerList()
    {
        return $this->manager;
    }

    /**
     *  実行中のアクション名を返す
     *
     *  @access public
     *  @return string  実行中のアクション名
     */
    public function getCurrentActionName()
    {
        return $this->action_name;
    }

    /**
     *  実行中のXMLRPCメソッド名を返す
     *
     *  @access public
     *  @return string  実行中のXMLRPCメソッド名
     */
    public function getXmlrpcMethodName()
    {
        return $this->xmlrpc_method_name;
    }

    /**
     *  ロケール設定、使用言語を取得する
     *
     *  @access public
     *  @return array   ロケール名(e.x ja_JP, en_US 等),
     *                  システムエンコーディング名,
     *                  クライアントエンコーディング名 の配列
     *                  (ロケール名は、ll_cc の形式。ll = 言語コード cc = 国コード)
     *  @see http://www.gnu.org/software/gettext/manual/html_node/Locale-Names.html
     */
    public function getLanguage()
    {
        return array($this->locale, $this->system_encoding, $this->client_encoding);
    }

    /**
     *  ロケール名へのアクセサ(R)
     *
     *  @access public
     *  @return string  ロケール名(e.x ja_JP, en_US 等),
     *                  (ロケール名は、ll_cc の形式。ll = 言語コード cc = 国コード)
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     *  ロケール名へのアクセサ(W)
     *
     *  @access public
     *  @param $locale ロケール名(e.x ja_JP, en_US 等),
     *                 (ロケール名は、ll_cc の形式。ll = 言語コード cc = 国コード)
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        $this->getEventDispatcher()->dispatch("ethna.core.language",
            new Ethna_Event_SetLanguage($this,
                $this->getLocale(),
                $this->getSystemEncoding(),
                $this->getClientEncoding()
            ));
    }

    /**
     *  クライアントエンコーディング名へのアクセサ(R)
     *
     *  @access public
     *  @return string  $client_encoding クライアントエンコーディング名
     */
    public function getClientEncoding()
    {
        return $this->client_encoding;
    }

    /**
     *  クライアントエンコーディング名へのアクセサ(W)
     *
     *  @access public
     *  @param  string  $client_encoding クライアントエンコーディング名
     */
    public function setClientEncoding($client_encoding)
    {
        $this->client_encoding = $client_encoding;

        $this->getEventDispatcher()->dispatch("ethna.core.language",
            new Ethna_Event_SetLanguage($this,
                $this->getLocale(),
                $this->getSystemEncoding(),
                $this->getClientEncoding()
            ));
    }

    /**
     *  ゲートウェイを取得する
     *
     *  @access public
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     *  ゲートウェイモードを設定する
     *
     *  @access public
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     *  アプリケーションのエントリポイント
     *
     *  @access public
     *  @param  string  $class_name     アプリケーションコントローラのクラス名
     *  @param  mixed   $action_name    指定のアクション名(省略可)
     *  @param  mixed   $fallback_action_name   アクションが決定できなかった場合に実行されるアクション名(省略可)
     *  @static
     */
    public static function main($class_name, $action_name = "", $fallback_action_name = "")
    {
        $c = new $class_name;
        $c->trigger($action_name, $fallback_action_name);
        $c->end();
    }

    /**
     *  CLIアプリケーションのエントリポイント
     *
     *  @access public
     *  @param  string  $class_name     アプリケーションコントローラのクラス名
     *  @param  string  $action_name    実行するアクション名
     *  @param  bool    $enable_filter  フィルタチェインを有効にするかどうか
     *  @static
     */
    public static function main_CLI($class_name, $action_name, $enable_filter = true)
    {
        $c = new $class_name(GATEWAY_CLI);
        $c->action_cli[$action_name] = array();
        $c->trigger($action_name, "", $enable_filter);
        $c->end();
    }

    /**
     *  SOAPアプリケーションのエントリポイント
     *
     *  @access public
     *  @param  string  $class_name     アプリケーションコントローラのクラス名
     *  @param  mixed   $action_name    指定のアクション名(省略可)
     *  @param  mixed   $fallback_action_name   アクションが決定できなかった場合に実行されるアクション名(省略可)
     *  @static
     */
    public static function main_SOAP($class_name, $action_name = "", $fallback_action_name = "")
    {
        $c = new $class_name(GATEWAY_SOAP);
        $c->trigger($action_name, $fallback_action_name);
        $c->end();
    }


    protected function executePreTriggerFilter()
    {
        // 実行前フィルタ
        for ($i = 0; $i < count($this->filter_chain); $i++) {
            $r = $this->filter_chain[$i]->preFilter();
            if (Ethna::isError($r)) {
                return $r;
            }
        }
    }

    protected function executePostTriggerFilter()
    {
        // 実行後フィルタ
        for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
            $r = $this->filter_chain[$i]->postFilter();
            if (Ethna::isError($r)) {
                return $r;
            }
        }
    }

    /**
     *  フレームワークの処理を開始する
     *
     *  @access public
     *  @param  mixed   $default_action_name    指定のアクション名
     *  @param  mixed   $fallback_action_name   アクション名が決定できなかった場合に実行されるアクション名
     *  @param  bool    $enable_filter  フィルタチェインを有効にするかどうか
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    public function trigger($default_action_name = "", $fallback_action_name = "", $enable_filter = true)
    {
        // フィルターの生成
        if ($enable_filter) {
            $this->_createFilterChain();
        }

        if (Ethna::isError($error = $this->executePreTriggerFilter())) {
            // TODO(chobie): これ普通に考えてErrorで落としたらサダメだよね
            return $error;
        }

        // trigger
        $this->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_TRIGGER,
            new Ethna_Event_Trigger($this, $default_action_name, $fallback_action_name, $this->getGateway()));


        $this->executePostTriggerFilter();
    }

    /**
     * pre action filterを実行する
     *
     * @param $action_name
     * @return mixed
     */
    public function executePreActionFilter(&$action_name)
    {
        for ($i = 0; $i < count($this->filter_chain); $i++) {
            $r = $this->filter_chain[$i]->preActionFilter($action_name);
            if ($r != null) {
                $this->logger->log(LOG_DEBUG, 'action [%s] -> [%s] by %s', $action_name, $r, get_class($this->filter_chain[$i]));
                $action_name = $r;
            }
        }

        return $action_name;
    }

    /**
     * post action filterを実行する
     *
     * @param $forward_name
     * @param $action_name
     */
    public function executePostActionFilter(&$forward_name, $action_name)
    {
        // アクション実行後フィルタ
        for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
            $r = $this->filter_chain[$i]->postActionFilter($action_name, $forward_name);
            if ($r != null) {
                $this->logger->log(LOG_DEBUG, 'forward [%s] -> [%s] by %s', $forward_name, $r, get_class($this->filter_chain[$i]));
                $forward_name = $r;
            }
        }

    }

    public function verifyActionObject(&$action_obj, &$action_name, $fallback_action_name)
    {
        if (is_null($action_obj)) {
            if ($fallback_action_name != "") {
                $this->logger->log(LOG_DEBUG, 'undefined action [%s] -> try fallback action [%s]', $action_name, $fallback_action_name);
                $action_obj = $this->_getAction($fallback_action_name);
            }

            if (is_null($action_obj)) {
                return Ethna::raiseError("undefined action [%s]", E_APP_UNDEFINED_ACTION, $action_name);
            } else {
                $action_name = $fallback_action_name;
            }
        }

        return;
    }

    /**
     * action formを設定する
     *
     * @param $action_name
     */
    public function setupActionForm($action_name)
    {
        $form_name = $this->getActionFormName($action_name);
        $this->action_form = new $form_name($this);
        $this->getBackend()->setActionForm($this->action_form);
        $this->action_form->setFormDef_PreHelper();
        $this->action_form->setFormVars();
    }

    /**
     *  エラーハンドラ
     *
     *  エラー発生時の追加処理を行いたい場合はこのメソッドをオーバーライドする
     *  (アラートメール送信等−デフォルトではログ出力時にアラートメール
     *  が送信されるが、エラー発生時に別にアラートメールをここで送信
     *  させることも可能)
     *
     *  @access public
     *  @param  object  Ethna_Error     エラーオブジェクト
     */
    public function handleError($error)
    {
        // ログ出力
        list ($log_level, $dummy) = $this->logger->errorLevelToLogLevel($error->getLevel());
        $message = $error->getMessage();
        $this->logger->log($log_level, sprintf("%s [ERROR CODE(%d)]", $message, $error->getCode()));
    }

    /**
     *  エラーメッセージを取得する
     *
     *  @access public
     *  @param  int     $code       エラーコード
     *  @return string  エラーメッセージ
     */
    public function getErrorMessage($code)
    {
        $message_list = $GLOBALS['_Ethna_error_message_list'];
        for ($i = count($message_list)-1; $i >= 0; $i--) {
            if (array_key_exists($code, $message_list[$i])) {
                return $message_list[$i][$code];
            }
        }
        return null;
    }

    /**
     *  実行するアクション名を返す
     *
     *  @access protected
     *  @param  mixed   $default_action_name    指定のアクション名
     *  @return string  実行するアクション名
     */
    public function _getActionName($default_action_name, $fallback_action_name)
    {
        // フォームから要求されたアクション名を取得する
        $event = $this->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_RESOLVE_ACTION,
            new Ethna_Event_ResolveActionName($this, $default_action_name, $fallback_action_name));

        $action_name = $event->getActionName();
        $this->logger->log(LOG_DEBUG, '<<< action_name[%s] >>>', $action_name);
        return $action_name;
    }

    /**
     *  アクション名を指定するクエリ/HTMLを生成する
     *
     *  @access public
     *  @param  string  $action action to request
     *  @param  string  $type   hidden, url...
     *  @todo   consider gateway
     */
    public function getActionRequest($action, $type = "hidden")
    {
        $s = null;
        if ($type == "hidden") {
            $s = sprintf('<input type="hidden" name="action_%s" value="true" />', htmlspecialchars($action, ENT_QUOTES));
        } else if ($type == "url") {
            $s = sprintf('action_%s=true', urlencode($action));
        }
        return $s;
    }

    /**
     *  フォームにより要求されたアクション名に対応する定義を返す
     *
     *  @access private
     *  @param  string  $action_name    アクション名
     *  @return array   アクション定義
     */
    public function _getAction($action_name, $gateway = null)
    {
        $action = array();
        $gateway = is_null($gateway) ? $this->getGateway() : $gateway;
        $result = $this->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_GATEWAY_ACTIONDIR,
            new Ethna_Event_Gateway($gateway));
        $target = $result->getActionDirKey();
        $action = $this->$target;

        $action_obj = array();
        if (isset($action[$action_name])) {
            $action_obj = $action[$action_name];
            if (isset($action_obj['inspect']) && $action_obj['inspect']) {
                return $action_obj;
            }
        } else {
            $this->logger->log(LOG_DEBUG, "action [%s] is not defined -> try default", $action_name);
        }

        // アクションスクリプトのインクルード
        $this->_includeActionScript($action_obj, $action_name);

        // 省略値の補正
        if (isset($action_obj['class_name']) == false) {
            $action_obj['class_name'] = $this->getDefaultActionClass($action_name);
        }

        if (isset($action_obj['form_name']) == false) {
            $action_obj['form_name'] = $this->getDefaultFormClass($action_name);
        } else if (class_exists($action_obj['form_name']) == false) {
            // 明示指定されたフォームクラスが定義されていない場合は警告
            $this->logger->log(LOG_WARNING, 'stated form class is not defined [%s]', $action_obj['form_name']);
        }

        // 必要条件の確認
        if (class_exists($action_obj['class_name']) == false) {
            $this->logger->log(LOG_NOTICE, 'action class is not defined [%s]', $action_obj['class_name']);
            $_ret_object = null;
            return $_ret_object;
        }
        if (class_exists($action_obj['form_name']) == false) {
            // フォームクラスは未定義でも良い
            $class_name = $this->class_factory->getObjectName('form');
            $this->logger->log(LOG_DEBUG, 'form class is not defined [%s] -> falling back to default [%s]', $action_obj['form_name'], $class_name);
            $action_obj['form_name'] = $class_name;
        }

        $action_obj['inspect'] = true;
        $action[$action_name] = $action_obj;
        return $action[$action_name];
    }

    /**
     *  アクション名とアクションクラスからの戻り値に基づいて遷移先を決定する
     *
     *  @access protected
     *  @param  string  $action_name    アクション名
     *  @param  string  $retval         アクションクラスからの戻り値
     *  @return string  遷移先
     */
    public function _sortForward($action_name, $retval)
    {
        return $retval;
    }

    /**
     *  フィルタチェインを生成する
     *
     *  @access protected
     */
    public function _createFilterChain()
    {
        $this->filter_chain = array();
        foreach ($this->filter as $filter) {
            $filter_plugin = $this->plugin->getPlugin('Filter', $filter);
            if (Ethna::isError($filter_plugin)) {
                continue;
            }

            $this->filter_chain[] = $filter_plugin;
        }
    }

    /**
     *  アクション名が実行許可されているものかどうかを返す
     *
     *  @access private
     *  @param  string  $action_name            リクエストされたアクション名
     *  @param  array   $default_action_name    許可されているアクション名
     *  @return bool    true:許可 false:不許可
     */
    public function _isAcceptableActionName($action_name, $default_action_name)
    {
        foreach (to_array($default_action_name) as $name) {
            if ($action_name == $name) {
                return true;
            } else if ($name{strlen($name)-1} == '*') {
                if (strncmp($action_name, substr($name, 0, -1), strlen($name)-1) == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  指定されたアクションのフォームクラス名を返す(オブジェクトの生成は行わない)
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションのフォームクラス名
     */
    public function getActionFormName($action_name)
    {
        $action_obj = $this->_getAction($action_name);
        if (is_null($action_obj)) {
            return null;
        }

        return $action_obj['form_name'];
    }

    /**
     *  アクションに対応するフォームクラス名が省略された場合のデフォルトクラス名を返す
     *
     *  デフォルトでは[プロジェクトID]_Form_[アクション名]となるので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションフォーム名
     */
    public function getDefaultFormClass($action_name, $gateway = null)
    {
        $gateway_prefix = $this->_getGatewayPrefix($gateway);

        $postfix = preg_replace_callback('/_(.)/', function(array $matches){return strtoupper($matches[1]);}, ucfirst($action_name));
        $r = sprintf("%s_%sForm_%s", $this->getAppId(), $gateway_prefix ? $gateway_prefix . "_" : "", $postfix);
        $this->logger->log(LOG_DEBUG, "default action class [%s]", $r);

        return $r;
    }

    /**
     *  getDefaultFormClass()で取得したクラス名からアクション名を取得する
     *
     *  getDefaultFormClass()をオーバーライドした場合、こちらも合わせてオーバーライド
     *  することを推奨(必須ではない)
     *
     *  @access public
     *  @param  string  $class_name     フォームクラス名
     *  @return string  アクション名
     */
    public function actionFormToName($class_name)
    {
        $prefix = sprintf("%s_Form_", $this->getAppId());
        if (preg_match("/$prefix(.*)/", $class_name, $match) == 0) {
            // 不明なクラス名
            return null;
        }
        $target = $match[1];

        $action_name = substr(preg_replace_callback('/([A-Z])/', function(array $matches){return strtolower($matches[1]);}, $target), 1);

        return $action_name;
    }

    /**
     *  アクションに対応するフォームパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは_getDefaultActionPath()と同じ結果を返す(1ファイルに
     *  アクションクラスとフォームクラスが記述される)ので、好みに応じて
     *  オーバーライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  form classが定義されるスクリプトのパス名
     */
    public function getDefaultFormPath($action_name)
    {
        return $this->getDefaultActionPath($action_name);
    }

    /**
     *  指定されたアクションのクラス名を返す(オブジェクトの生成は行わない)
     *
     *  @access public
     *  @param  string  $action_name    アクションの名称
     *  @return string  アクションのクラス名
     */
    public function getActionClassName($action_name)
    {
        $action_obj = $this->_getAction($action_name);
        if ($action_obj == null) {
            return null;
        }

        return $action_obj['class_name'];
    }

    /**
     *  アクションに対応するアクションクラス名が省略された場合のデフォルトクラス名を返す
     *
     *  デフォルトでは[プロジェクトID]_Action_[アクション名]となるので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションクラス名
     */
    public function getDefaultActionClass($action_name, $gateway = null)
    {
        $gateway_prefix = $this->_getGatewayPrefix($gateway);

        $postfix = preg_replace_callback('/_(.)/', function(array $matches){return strtoupper($matches[1]);}, ucfirst($action_name));
        $r = sprintf("%s_%sAction_%s", $this->getAppId(), $gateway_prefix ? $gateway_prefix . "_" : "", $postfix);
        $this->logger->log(LOG_DEBUG, "default action class [%s]", $r);

        return $r;
    }

    /**
     *  getDefaultActionClass()で取得したクラス名からアクション名を取得する
     *
     *  getDefaultActionClass()をオーバーライドした場合、こちらも合わせてオーバーライド
     *  することを推奨(必須ではない)
     *
     *  @access public
     *  @param  string  $class_name     アクションクラス名
     *  @return string  アクション名
     */
    public function actionClassToName($class_name)
    {
        $prefix = sprintf("%s_Action_", $this->getAppId());
        if (preg_match("/$prefix(.*)/", $class_name, $match) == 0) {
            // 不明なクラス名
            return null;
        }
        $target = $match[1];

        $action_name = substr(preg_replace_callback('/([A-Z])/', function(array $matches){return '_' . strtolower($matches[1]);} , $target), 1);

        return $action_name;
    }

    /**
     *  アクションに対応するアクションパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは"foo_bar" -> "/Foo/Bar.php"となるので好み応じてオーバーライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションクラスが定義されるスクリプトのパス名
     */
    public function getDefaultActionPath($action_name)
    {
        $r = preg_replace_callback('/_(.)/', function(array $matches){return '/' . strtoupper($matches[1]);}, ucfirst($action_name)) . '.' . $this->getExt('php');
        $this->logger->log(LOG_DEBUG, "default action path [%s]", $r);

        return $r;
    }

    /**
     *  指定された遷移名に対応するビュークラス名を返す(オブジェクトの生成は行わない)
     *
     *  @access public
     *  @param  string  $forward_name   遷移先の名称
     *  @return string  view classのクラス名
     */
    public function getViewClassName($forward_name)
    {
        if ($forward_name == null) {
            return null;
        }

        if (isset($this->forward[$forward_name])) {
            $forward_obj = $this->forward[$forward_name];
        } else {
            $forward_obj = array();
        }

        if (isset($forward_obj['view_name'])) {
            $class_name = $forward_obj['view_name'];
            if (class_exists($class_name)) {
                return $class_name;
            }
        } else {
            $class_name = null;
        }

        // viewのインクルード
        $this->_includeViewScript($forward_obj, $forward_name);

        if (is_null($class_name) == false && class_exists($class_name)) {
            return $class_name;
        } else if (is_null($class_name) == false) {
            $this->logger->log(LOG_WARNING, 'stated view class is not defined [%s] -> try default', $class_name);
        }

        $class_name = $this->getDefaultViewClass($forward_name);
        if (class_exists($class_name)) {
            return $class_name;
        } else {
            $class_name = $this->class_factory->getObjectName('view');
            $this->logger->log(LOG_DEBUG, 'view class is not defined for [%s] -> use default [%s]', $forward_name, $class_name);
            return $class_name;
        }
    }

    /**
     *  遷移名に対応するビュークラス名が省略された場合のデフォルトクラス名を返す
     *
     *  デフォルトでは[プロジェクトID]_View_[遷移名]となるので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $forward_name   forward名
     *  @return string  view classクラス名
     */
    public function getDefaultViewClass($forward_name, $gateway = null)
    {
        $gateway_prefix = $this->_getGatewayPrefix($gateway);

        $postfix = preg_replace_callback('/_(.)/', function(array $matches){return strtoupper($matches[1]);}, ucfirst($forward_name));
        $r = sprintf("%s_%sView_%s", $this->getAppId(), $gateway_prefix ? $gateway_prefix . "_" : "", $postfix);
        $this->logger->log(LOG_DEBUG, "default view class [%s]", $r);

        return $r;
    }

    /**
     *  遷移名に対応するビューパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは"foo_bar" -> "/Foo/Bar.php"となるので好み応じてオーバーライドする
     *
     *  @access public
     *  @param  string  $forward_name   forward名
     *  @return string  view classが定義されるスクリプトのパス名
     */
    public function getDefaultViewPath($forward_name)
    {
        $r = preg_replace_callback('/_(.)/', function(array $matches){return '/' . strtoupper($matches[1]); }, ucfirst($forward_name)) . '.' . $this->getExt('php');
        $this->logger->log(LOG_DEBUG, "default view path [%s]", $r);

        return $r;
    }

    /**
     *  遷移名に対応するテンプレートパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは"foo_bar"というforward名が"foo/bar" + テンプレート拡張子となる
     *  ので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $forward_name   forward名
     *  @return string  forwardパス名
     */
    public function getDefaultForwardPath($forward_name)
    {
        return str_replace('_', '/', $forward_name) . '.' . $this->ext['tpl'];
    }

    /**
     *  テンプレートパス名から遷移名を取得する
     *
     *  getDefaultForwardPath()をオーバーライドした場合、こちらも合わせてオーバーライド
     *  することを推奨(必須ではない)
     *
     *  @access public
     *  @param  string  $forward_path   テンプレートパス名
     *  @return string  遷移名
     */
    public function forwardPathToName($forward_path)
    {
        $forward_path = preg_replace('/^\/+/', '', $forward_path);
        $forward_path = preg_replace(sprintf('/\.%s$/', $this->getExt('tpl')), '', $forward_path);

        return str_replace('/', '_', $forward_path);
    }

    /**
     *  遷移名からテンプレートファイルのパス名を取得する
     *
     *  @access private
     *  @param  string  $forward_name   forward名
     *  @return string  テンプレートファイルのパス名
     */
    public function _getForwardPath($forward_name)
    {
        $forward_obj = null;

        if (isset($this->forward[$forward_name]) == false) {
            // try default
            $this->forward[$forward_name] = array();
        }
        $forward_obj = $this->forward[$forward_name];
        if (isset($forward_obj['forward_path']) == false) {
            // 省略値補正
            $forward_obj['forward_path'] = $this->getDefaultForwardPath($forward_name);
        }

        return $forward_obj['forward_path'];
    }

    /**
     *  レンダラを取得する
     *
     *  @access public
     *  @return object  Ethna_Renderer  レンダラオブジェクト
     */
    public function getRenderer()
    {
        if ($this->renderer instanceof Ethna_Renderer) {
            return $this->renderer;
        }

        $this->renderer = $this->class_factory->getObject('renderer');
        $this->_setDefaultTemplateEngine($this->renderer);
        return $this->renderer;
    }

    /**
     *  テンプレートエンジンのデフォルト状態を設定する
     *
     *  @access protected
     *  @param  object  Ethna_Renderer  レンダラオブジェクト
     *  @obsolete
     */
    public function _setDefaultTemplateEngine($renderer)
    {
    }

    /**
     * システムエンコーディングを取得する
     *
     * @return mixed
     */
    public function getSystemEncoding()
    {
        return $this->system_encoding;
    }

    /**
     * システムエンコーディングを設定する
     *
     * @param $encode
     */
    public function setSystemEncoding($encode)
    {
        $this->system_encoding = $encode;

        $this->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_I18N,
            new Ethna_Event_SetLanguage($this,
                $this->getLocale(),
                $this->getSystemEncoding(),
                $this->getClientEncoding()
            ));
    }

    /**
     *  デフォルト状態での使用言語を取得する
     *  外部に出力されるEthnaのエラーメッセージ等のエンコーディングを
     *  切り替えたい場合は、このメソッドをオーバーライドする。
     *
     *  @access protected
     *  @return array   ロケール名(e.x ja_JP, en_US 等),
     *                  システムエンコーディング名,
     *                  クライアントエンコーディング名
     *                  (= テンプレートのエンコーディングと考えてよい) の配列
     *                  (ロケール名は ll_cc の形式。ll = 言語コード cc = 国コード)
     *
     *  WARNING!! : クライアントエンコーディング名が、フレームワークの内部エンコーデ
     *              ィングとして設定されます。つまり、クライアントエンコーディングで
     *              ブラウザからの入力は入ってくるものと想定しています！
     */
    public function _getDefaultLanguage()
    {
        return array('ja_JP', 'UTF-8', 'UTF-8');
    }

    /**
     *  ゲートウェイに対応したクラス名のプレフィクスを取得する
     *
     *  @access public
     *  @param  string  $gateway    ゲートウェイ
     *  @return string  ゲートウェイクラスプレフィクス
     */
    public function _getGatewayPrefix($gateway = null)
    {
        $gateway = is_null($gateway) ? $this->getGateway() : $gateway;
        $result = $this->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_GATEWAY_PREFIX,
            new Ethna_Event_Gateway($gateway));

        return $result->getPrefix();
    }

    /**
     *  マネージャクラス名を取得する
     *
     *  @access public
     *  @param  string  $name   マネージャキー
     *  @return string  マネージャクラス名
     */
    public function getManagerClassName($name)
    {
        //   アプリケーションIDと、渡された名前のはじめを大文字にして、
        //   組み合わせたものが返される
        $manager_id = preg_replace_callback('/_(.)/', function(array $matches){return strtoupper($matches[1]);}, ucfirst($name));
        return sprintf('%s_%sManager', $this->getAppId(), ucfirst($manager_id));
    }

    /**
     *  アプリケーションオブジェクトクラス名を取得する
     *
     *  @access public
     *  @param  string  $name   アプリケーションオブジェクトキー
     *  @return string  マネージャクラス名
     */
    public function getObjectClassName($name)
    {
        //  引数のはじめの一文字目と、アンダーバー直後の
        //  1文字を必ず大文字にする。アンダーバーは削除される。
        $name = preg_replace_callback('/_(.)/', function(array $matches){return strtoupper($matches[1]);}, ucfirst($name));

        //  $name に foo_bar を渡し、AppID が Hogeの場合
        //  [Appid]_FooBar が返される
        return sprintf('%s_%s', $this->getAppId(), $name);
    }

    /**
     *  アクションスクリプトをインクルードする
     *
     *  ただし、インクルードしたファイルにクラスが正しく定義されているかどうかは保証しない
     *
     *  @access private
     *  @param  array   $action_obj     アクション定義
     *  @param  string  $action_name    アクション名
     */
    public function _includeActionScript($action_obj, $action_name)
    {
        $class_path = $form_path = null;

        $action_dir = $this->getActiondir();

        // class_path属性チェック
        if (isset($action_obj['class_path'])) {
            // フルパス指定サポート
            $tmp_path = $action_obj['class_path'];
            if (Ethna_Util::isAbsolute($tmp_path) == false) {
                $tmp_path = $action_dir . $tmp_path;
            }

            if (file_exists($tmp_path) == false) {
                $this->logger->log(LOG_WARNING, 'class_path file not found [%s] -> try default', $tmp_path);
            } else {
                include_once $tmp_path;
                $class_path = $tmp_path;
            }
        }

        // デフォルトチェック
        if (is_null($class_path)) {
            $class_path = $this->getDefaultActionPath($action_name);
            if (file_exists($action_dir . $class_path)) {
                include_once $action_dir . $class_path;
            } else {
                $this->logger->log(LOG_DEBUG, 'default action file not found [%s] -> try all files', $class_path);
                return;
            }
        }

        // form_path属性チェック
        if (isset($action_obj['form_path'])) {
            // フルパス指定サポート
            $tmp_path = $action_obj['form_path'];
            if (Ethna_Util::isAbsolute($tmp_path) == false) {
                $tmp_path = $action_dir . $tmp_path;
            }

            if ($tmp_path == $class_path) {
                return;
            }
            if (file_exists($tmp_path) == false) {
                $this->logger->log(LOG_WARNING, 'form_path file not found [%s] -> try default', $tmp_path);
            } else {
                include_once $tmp_path;
                $form_path = $tmp_path;
            }
        }

        // デフォルトチェック
        if (is_null($form_path)) {
            $form_path = $this->getDefaultFormPath($action_name);
            if ($form_path == $class_path) {
                return;
            }
            if (file_exists($action_dir . $form_path)) {
                include_once $action_dir . $form_path;
            } else {
                $this->logger->log(LOG_DEBUG, 'default form file not found [%s] -> maybe falling back to default form class', $form_path);
            }
        }
    }

    /**
     *  ビュースクリプトをインクルードする
     *
     *  ただし、インクルードしたファイルにクラスが正しく定義されているかどうかは保証しない
     *
     *  @access private
     *  @param  array   $forward_obj    遷移定義
     *  @param  string  $forward_name   遷移名
     */
    public function _includeViewScript($forward_obj, $forward_name)
    {
        $view_dir = $this->getViewdir();

        // view_path属性チェック
        if (isset($forward_obj['view_path'])) {
            // フルパス指定サポート
            $tmp_path = $forward_obj['view_path'];
            if (Ethna_Util::isAbsolute($tmp_path) == false) {
                $tmp_path = $view_dir . $tmp_path;
            }

            if (file_exists($tmp_path) == false) {
                $this->logger->log(LOG_WARNING, 'view_path file not found [%s] -> try default', $tmp_path);
            } else {
                include_once $tmp_path;
                return;
            }
        }

        // デフォルトチェック
        $view_path = $this->getDefaultViewPath($forward_name);
        if (file_exists($view_dir . $view_path)) {
            include_once $view_dir . $view_path;
            return;
        } else {
            $this->logger->log(LOG_DEBUG, 'default view file not found [%s]', $view_path);
            $view_path = null;
        }
    }

    /**
     *  ディレクトリ以下の全てのスクリプトをインクルードする
     *
     *  @access private
     */
    public function _includeDirectory($dir)
    {
        $ext = "." . $this->ext['php'];
        $ext_len = strlen($ext);

        if (is_dir($dir) == false) {
            return;
        }

        $dh = opendir($dir);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..' && is_dir("$dir/$file")) {
                    $this->_includeDirectory("$dir/$file");
                }
                if (substr($file, -$ext_len, $ext_len) != $ext) {
                    continue;
                }
                include_once $dir . '/' . $file;
            }
        }
        closedir($dh);
    }

    /**
     *  設定ファイルのDSN定義から使用するデータを再構築する(スレーブアクセス分岐等)
     *
     *  DSNの定義方法(デフォルト:設定ファイル)を変えたい場合はここをオーバーライドする
     *
     *  @access protected
     *  @return array   DSN定義(array('DBキー1' => 'dsn1', 'DBキー2' => 'dsn2', ...))
     */
    public function _prepareDSN()
    {
        $r = array();

        foreach ($this->db as $key => $value) {
            $config_key = "dsn";
            if ($key != "") {
                $config_key .= "_$key";
            }
            $dsn = $this->config->get($config_key);
            if (is_array($dsn)) {
                // 種別1つにつき複数DSNが定義されている場合はアクセス分岐
                $dsn = $this->_selectDSN($key, $dsn);
            }
            $r[$key] = $dsn;
        }
        return $r;
    }

    /**
     *  DSNのアクセス分岐を行う
     *
     *  スレーブサーバへの振分け処理(デフォルト:ランダム)を変更したい場合はこのメソッドをオーバーライドする
     *
     *  @access protected
     *  @param  string  $type       DB種別
     *  @param  array   $dsn_list   DSN一覧
     *  @return string  選択されたDSN
     */
    public function _selectDSN($type, $dsn_list)
    {
        if (is_array($dsn_list) == false) {
            return $dsn_list;
        }

        // デフォルト:ランダム
        list($usec, $sec) = explode(' ', microtime());
        mt_srand($sec + ((float) $usec * 100000));
        $n = mt_rand(0, count($dsn_list)-1);

        return $dsn_list[$n];
    }

    public function setActionCli($value)
    {
        $this->action_cli[$value] = array();
    }
}
// }}}
