<?php
// vim: foldmethod=marker
/**
 *	Ethna_Controller.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Controller
/**
 *	コントローラクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Controller
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	string		アプリケーションID
	 */
	var $appid = 'ETHNA';

	/**
	 *	@var	string		アプリケーションベースディレクトリ
	 */
	var $base = '';

	/**
	 *	@var	string		アプリケーションベースURL
	 */
	var	$url = '';

	/**
	 *	@var	string		アプリケーションDSN(Data Source Name)
	 */
	var $dsn;

	/**
	 *	@var	array		アプリケーションディレクトリ
	 */
	var $directory = array(
		'action'		=> 'app/action',
		'etc'			=> 'etc',
		'filter'		=> 'app/filter',
		'locale'		=> 'locale',
		'log'			=> 'log',
		'plugins'		=> array(),
		'template'		=> 'template',
		'template_c'	=> 'tmp',
		'tmp'			=> 'tmp',
		'view'			=> 'app/view',
	);

	/**
	 *	@var	array		DBアクセス定義
	 */
	var	$db = array(
		''				=> DB_TYPE_RW,
	);

	/**
	 *	@var	array		拡張子設定
	 */
	var $ext = array(
		'php'			=> 'php',
		'tpl'			=> 'tpl',
	);

	/**
	 *	@var	array		クラス設定
	 */
	var $class = array(
		'config'		=> 'Ethna_Config',
		'db'			=> 'Ethna_DB',
		'logger'		=> 'Ethna_Logger',
		'sql'			=> 'Ethna_AppSQL',
	);

	/**
	 *	@var	array		フィルタ設定
	 */
	var $filter = array(
	);

	/**
	 *	@var	string		使用言語設定
	 */
	var $language;

	/**
	 *	@var	string		システム側エンコーディング
	 */
	var	$system_encoding;

	/**
	 *	@var	string		クライアント側エンコーディング
	 */
	var	$client_encoding;

	/**
	 *	@var	string		クライアントタイプ
	 */
	var $client_type;

	/**
	 *	@var	string	現在実行中のアクション名
	 */
	var	$action_name;

	/**
	 *	@var	array	forward定義
	 */
	var $forward = array();

	/**
	 *	@var	array	action定義
	 */
	var $action = array();

	/**
	 *	@var	array	soap action定義
	 */
	var $soap_action = array();

	/**
	 *	@var	array	アプリケーションマネージャ定義
	 */
	var	$manager = array();

	/**
	 *	@var	array	smarty modifier定義
	 */
	var $smarty_modifier_plugin = array();

	/**
	 *	@var	array	smarty function定義
	 */
	var $smarty_function_plugin = array();

	/**
	 *	@var	array	smarty prefilter定義
	 */
	var $smarty_prefilter_plugin = array();

	/**
	 *	@var	array	smarty postfilter定義
	 */
	var $smarty_postfilter_plugin = array();

	/**
	 *	@var	array	smarty outputfilter定義
	 */
	var $smarty_outputfilter_plugin = array();

	/**
	 *	@var	object	Ethna_Backend	backendオブジェクト
	 */
	var $backend = null;

	/**
	 *	@var	object	Ethna_I18N		i18nオブジェクト
	 */
	var $i18n = null;

	/**
	 *	@var	object	Ethna_ActionError	アクションエラーオブジェクト
	 */
	var $action_error = null;

	/**
	 *	@var	object	Ethna_ActionForm	アクションフォームオブジェクト
	 */
	var $action_form = null;

	/**
	 *	@var	object	Ethna_Session		セッションオブジェクト
	 */
	var $session = null;

	/**
	 *	@var	object	Ethna_Config		設定オブジェクト
	 */
	var	$config = null;

	/**
	 *	@var	object	Ethna_Logger		ログオブジェクト
	 */
	var	$logger = null;

	/**
	 *	@var	object	Ethna_AppSQL		SQLオブジェクト
	 */
	var	$sql = null;

	/**
	 *	@var	array	フィルターチェイン(Ethna_Filterオブジェクトの配列)
	 */
	var	$filter_chain = array();

	/**#@-*/


	/**
	 *	Ethna_Controllerクラスのコンストラクタ
	 *
	 *	@access		public
	 */
	function Ethna_Controller()
	{
		$GLOBALS['controller'] =& $this;
		$this->base = BASE;

		// エラーハンドラの設定
		Ethna::setErrorCallback(array(&$this, 'handleError'));

		// ディレクトリ名の設定(相対パス->絶対パス)
		foreach ($this->directory as $key => $value) {
			if ($key == 'plugins') {
				// Smartyプラグインディレクトリは配列で指定する
				$tmp = array(SMARTY_DIR . 'plugins');
				foreach (to_array($value) as $elt) {
					if ($elt{0} != '/') {
						$tmp[] = $this->base . (empty($this->base) ? '' : '/') . $elt;
					}
				}
				$this->directory[$key] = $tmp;
			} else {
				if ($value{0} != '/') {
					$this->directory[$key] = $this->base . (empty($this->base) ? '' : '/') . $value;
				}
			}
		}
		$this->i18n =& new Ethna_I18N($this->getDirectory('locale'), $this->getAppId());
		list($this->language, $this->system_encoding, $this->client_encoding) = $this->_getDefaultLanguage();
		$this->client_type = $this->_getDefaultClientType();

		// 設定ファイル読み込み
		$config_class = $this->class['config'];
		$this->config =& new $config_class($this);
		$this->dsn = $this->_prepareDSN();
		$this->url = $this->config->get('url');

		// ログ出力開始
		$logger_class = $this->class['logger'];
		$this->logger =& new $logger_class($this);
		$this->logger->begin();

		// SQLオブジェクト生成
		$sql_class = $this->class['sql'];
		$this->sql =& new $sql_class($this);

		// Ethnaマネージャ設定
		$this->_activateEthnaManager();
	}

	/**
	 *	(現在アクティブな)コントローラのインスタンスを返す
	 *
	 *	@access	public
	 *	@return	object	Ethna_Controller	コントローラのインスタンス
	 *	@static
	 */
	function &getInstance()
	{
		if (isset($GLOBALS['controller'])) {
			return $GLOBALS['controller'];
		} else {
			return null;
		}
	}

	/**
	 *	アプリケーションIDを返す
	 *
	 *	@access	public
	 *	@return	string	アプリケーションID
	 */
	function getAppId()
	{
		return ucfirst(strtolower($this->appid));
	}

	/**
	 *	DSNを返す
	 *
	 *	@access	public
	 *	@param	string	$type	DB種別
	 *	@return	string	DSN
	 */
	function getDSN($type = "")
	{
		if (isset($this->dsn[$type]) == false) {
			return null;
		}
		return $this->dsn[$type];
	}

	/**
	 *	DSNの持続接続設定を返す
	 *
	 *	@access	public
	 *	@param	string	$type	DB種別
	 *	@return	bool	true:persistent false:non-persistent(あるいは設定無し)
	 */
	function getDSN_persistent($type = "")
	{
		$key = sprintf("dsn%s_persistent", $type == "" ? "" : "_$type");
		$dsn_persistent = $this->config->get($key);
		if (is_null($dsn_persistent)) {
			return false;
		}
		return $dsn_persistent;
	}

	/**
	 *	アプリケーションベースURLを返す
	 *
	 *	@access	public
	 *	@return	string	アプリケーションベースURL
	 */
	function getURL()
	{
		return $this->url;
	}

	/**
	 *	アプリケーションベースディレクトリを返す
	 *
	 *	@access	public
	 *	@return	string	アプリケーションベースディレクトリ
	 */
	function getBasedir()
	{
		return $this->base;
	}

	/**
	 *	クライアントタイプ/言語からテンプレートディレクトリ名を決定する
	 *
	 *	@access	public
	 *	@return	string	テンプレートディレクトリ
	 */
	function getTemplatedir()
	{
		$template = $this->getDirectory('template');

		// 言語別ディレクトリ
		if (file_exists($template . '/' . $this->language)) {
			$template .= '/' . $this->language;
		}

		// クライアント別ディレクトリ(if we need)
		if ($this->client_type == CLIENT_TYPE_MOBILE_AU && file_exists($template . '/au')) {
			$template .= '/au';
		}

		return $template;
	}

	/**
	 *	アクションディレクトリ名を決定する
	 *
	 *	@access	public
	 *	@return	string	アクションディレクトリ
	 */
	function getActiondir()
	{
		return (empty($this->directory['action']) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory['action'] . "/"));
	}

	/**
	 *	ビューディレクトリ名を決定する
	 *
	 *	@access	public
	 *	@return	string	アクションディレクトリ
	 */
	function getViewdir()
	{
		return (empty($this->directory['view']) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory['view'] . "/"));
	}

	/**
	 *	アプリケーションクラス設定を返す
	 *
	 *	@access	public
	 *	@param	string	$key	クラスタイプ("config", "db", "logger", "sql")
	 *	@return	string	$keyに対応したクラス名(設定がない場合はnull)
	 */
	function getClass($key)
	{
		if (isset($this->class[$key]) == false) {
			return null;
		}
		return $this->class[$key];
	}

	/**
	 *	アプリケーションディレクトリ設定を返す
	 *
	 *	@access	public
	 *	@param	string	$key	ディレクトリタイプ("tmp", "template"...)
	 *	@return	string	$keyに対応したアプリケーションディレクトリ(設定が無い場合はnull)
	 */
	function getDirectory($key)
	{
		if (isset($this->directory[$key]) == false) {
			return null;
		}
		return $this->directory[$key];
	}

	/**
	 *	DB設定を返す
	 *
	 *	@access	public
	 *	@param	string	$key	DBタイプ("r", ...)
	 *	@return	string	$keyに対応するDB種別定義(設定が無い場合はnull)
	 */
	function getDB($key)
	{
		if (isset($this->db[$key]) == false) {
			return null;
		}
		return $this->db[$key];
	}

	/**
	 *	アプリケーション拡張子設定を返す
	 *
	 *	@access	public
	 *	@param	string	$key	拡張子タイプ("php", "tpl"...)
	 *	@return	string	$keyに対応した拡張子(設定が無い場合はnull)
	 */
	function getExt($key)
	{
		if (isset($this->ext[$key]) == false) {
			return null;
		}
		return $this->ext[$key];
	}

	/**
	 *	i18nオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_I18N	i18nオブジェクト
	 */
	function &getI18N()
	{
		return $this->i18n;
	}

	/**
	 *	設定オブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_Config	設定オブジェクト
	 */
	function &getConfig()
	{
		return $this->config;
	}

	/**
	 *	backendオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_Backend	backendオブジェクト
	 */
	function &getBackend()
	{
		return $this->backend;
	}

	/**
	 *	アクションエラーオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionError	アクションエラーオブジェクト
	 */
	function &getActionError()
	{
		return $this->action_error;
	}

	/**
	 *	アクションフォームformオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionForm	アクションフォームformオブジェクト
	 */
	function &getActionForm()
	{
		return $this->action_form;
	}

	/**
	 *	セッションオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_Session		セッションオブジェクト
	 */
	function &getSession()
	{
		return $this->session;
	}

	/**
	 *	ログオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_Logger		ログオブジェクト
	 */
	function &getLogger()
	{
		return $this->logger;
	}

	/**
	 *	SQLオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_AppSQL	SQLオブジェクト
	 */
	function &getSQL()
	{
		return $this->sql;
	}

	/**
	 *	マネージャ一覧を返す
	 *
	 *	@access	public
	 *	@return	array	マネージャ一覧
	 */
	function getManagerList()
	{
		return $this->manager;
	}

	/**
	 *	実行中のアクション名を返す
	 *
	 *	@access	public
	 *	@return	string	実行中のアクション名
	 */
	function getCurrentActionName()
	{
		return $this->action_name;
	}

	/**
	 *	使用言語を取得する
	 *
	 *	@access	public
	 *	@return	array	使用言語,システムエンコーディング名,クライアントエンコーディング名
	 */
	function getLanguage()
	{
		return array($this->language, $this->system_encoding, $this->client_encoding);
	}

	/**
	 *	クライアントタイプを取得する
	 *
	 *	@access	public
	 *	@return	int		クライアントタイプ定義(CLIENT_TYPE_WWW...)
	 */
	function getClientType()
	{
		return $this->client_type;
	}

	/**
	 *	クライアントタイプを設定する
	 *
	 *	@access	public
	 *	@param	int		$client_type	クライアントタイプ定義(CLIENT_TYPE_WWW...)
	 */
	function setClientType($client_type)
	{
		$this->client_type = $client_type;
	}

	/**
	 *	テンプレートエンジン取得する(現在はsmartyのみ対応)
	 *
	 *	@access	public
	 *	@return	object	Smarty	テンプレートエンジンオブジェクト
	 *	@todo	ブロック関数プラグイン(etc)対応
	 */
	function &getTemplateEngine()
	{
		$smarty =& new Smarty();
		$smarty->template_dir = $this->getTemplatedir();
		$smarty->compile_dir = $this->getDirectory('template_c') . '/tpl_' . md5($smarty->template_dir);
		if (@is_dir($smarty->compile_dir) == false) {
			mkdir($smarty->compile_dir, 0755);
		}
		$smarty->plugins_dir = $this->getDirectory('plugins');

		// default modifiers
		$smarty->register_modifier('number_format', 'smarty_modifier_number_format');
		$smarty->register_modifier('strftime', 'smarty_modifier_strftime');
		$smarty->register_modifier('count', 'smarty_modifier_count');
		$smarty->register_modifier('join', 'smarty_modifier_join');
		$smarty->register_modifier('filter', 'smarty_modifier_filter');
		$smarty->register_modifier('unique', 'smarty_modifier_unique');
		$smarty->register_modifier('wordwrap_i18n', 'smarty_modifier_wordwrap_i18n');
		$smarty->register_modifier('i18n', 'smarty_modifier_i18n');
		$smarty->register_modifier('checkbox', 'smarty_modifier_checkbox');
		$smarty->register_modifier('select', 'smarty_modifier_select');
		$smarty->register_modifier('form_value', 'smarty_modifier_form_value');

		// user defined modifiers
		foreach ($this->smarty_modifier_plugin as $modifier) {
			$name = str_replace('smarty_modifier_', '', $modifier);
			$smarty->register_modifier($name, $modifier);
		}

		// default functions
		$smarty->register_function('message', 'smarty_function_message');
		$smarty->register_function('uniqid', 'smarty_function_uniqid');
		$smarty->register_function('select', 'smarty_function_select');
		$smarty->register_function('checkbox_list', 'smarty_function_checkbox_list');

		// user defined functions
		foreach ($this->smarty_function_plugin as $function) {
			$name = str_replace('smarty_function_', '', $function);
			$smarty->register_function($name, $function);
		}

		// user defined prefilters
		foreach ($this->smarty_prefilter_plugin as $prefilter) {
			$smarty->register_prefilter($prefilter);
		}

		// user defined postfilters
		foreach ($this->smarty_postfilter_plugin as $postfilter) {
			$smarty->register_postfilter($postfilter);
		}

		// user defined outputfilters
		foreach ($this->smarty_outputfilter_plugin as $outputfilter) {
			$smarty->register_outputfilter($outputfilter);
		}

		$this->_setDefaultTemplateEngine($smarty);

		return $smarty;
	}

	/**
	 *	アプリケーションのエントリポイント
	 *
	 *	@access	public
	 *	@param	string	$class_name		アプリケーションコントローラのクラス名
	 *	@param	mixed	$action_name	指定のアクション名(省略可)
	 *	@param	mixed	$fallback_action_name	アクションが決定できなかった場合に実行されるアクション名(省略可)
	 *	@static
	 */
	function main($class_name, $action_name = "", $fallback_action_name = "")
	{
		$c =& new $class_name;
		$c->trigger('www', $action_name, $fallback_action_name);
	}

	/**
	 *	コマンドラインアプリケーションのエントリポイント
	 *
	 *	@access	public
	 *	@param	string	$class_name		アプリケーションコントローラのクラス名
	 *	@param	string	$action_name	実行するアクション名
	 *	@static
	 */
	function main_CLI($class_name, $action_name)
	{
		$c =& new $class_name;
		$c->action[$action_name] = array();
		$c->trigger('www', $action_name);
	}

	/**
	 *	SOAPアプリケーションのエントリポイント
	 *
	 *	@access	public
	 *	@param	string	$class_name	アプリケーションコントローラのクラス名
	 *	@static
	 */
	function main_SOAP($class_name)
	{
		$c =& new $class_name;
		$c->setClientType(CLIENT_TYPE_SOAP);
		$c->trigger('soap');
	}

	/**
	 *	AMF(Flash Remoting)アプリケーションのエントリポイント
	 *
	 *	@access	public
	 *	@param	string	$class_name	アプリケーションコントローラのクラス名
	 *	@static
	 */
	function main_AMF($class_name)
	{
		$c =& new $class_name;
		$c->setClientType(CLIENT_TYPE_AMF);
		$c->trigger('amf');
	}

	/**
	 *	フレームワークの処理を開始する
	 *
	 *	@access	public
	 *	@param	strint	$type					処理タイプ(WWW/SOAP/AMF)
	 *	@param	mixed	$default_action_name	指定のアクション名
	 *	@param	mixed	$fallback_action_name	アクション名が決定できなかった場合に実行されるアクション名
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function trigger($type, $default_action_name = "", $fallback_action_name = "")
	{
		// フィルターの生成
		$this->_createFilterChain();

		// prefilter
		for ($i = 0; $i < count($this->filter_chain); $i++) {
			$this->filter_chain[$i]->prefilter();
		}

		// trigger
		if ($type == 'www') {
			$this->_trigger($default_action_name, $fallback_action_name);
		} else if ($type == 'soap') {
			$this->_trigger_SOAP();
		} else if ($type == 'amf') {
			$this->_trigger_AMF();
		}

		// postfilter
		for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
			$this->filter_chain[$i]->postfilter();
		}
	}

	/**
	 *	フレームワークの処理を実行する(WWW)
	 *
	 *	引数$default_action_nameに配列が指定された場合、その配列で指定された
	 *	アクション以外は受け付けない(それ以外のアクションが指定された場合、
	 *	配列の先頭で指定されたアクションが実行される)
	 *
	 *	@access	private
	 *	@param	mixed	$default_action_name	指定のアクション名
	 *	@param	mixed	$fallback_action_name	アクション名が決定できなかった場合に実行されるアクション名
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function _trigger($default_action_name = "", $fallback_action_name = "")
	{
		// アクション名の取得
		$action_name = $this->_getActionName($default_action_name, $fallback_action_name);

		// アクション定義の取得
		$action_obj =& $this->_getAction($action_name);
		if (is_null($action_obj)) {
			if ($fallback_action_name != "") {
				$this->logger->log(LOG_DEBUG, 'undefined action [%s] -> try fallback action [%s]', $action_name, $fallback_action_name);
				$action_obj =& $this->_getAction($fallback_action_name);
			}
			if ($action_obj == null) {
				return Ethna::raiseError(E_APP_UNDEFINED_ACTION, "undefined action [%s]", $action_name);
			} else {
				$action_name = $fallback_action_name;
			}
		}
		$this->action_name = $action_name;

		// 言語設定
		$this->_setLanguage($this->language, $this->system_encoding, $this->client_encoding);

		// オブジェクト生成
		$this->action_error =& new Ethna_ActionError();
		$form_name = $this->getActionFormName($action_name);
		$this->action_form =& new $form_name($this);
		$this->session =& new Ethna_Session($this->getAppId(), $this->getDirectory('tmp'), $this->logger);

		// バックエンド処理実行
		$backend =& new Ethna_Backend($this);
		$this->backend =& $backend;
		$forward_name = $backend->perform($action_name);

		// コントローラで遷移先を決定する(オプション)
		$forward_name = $this->_sortForward($action_name, $forward_name);

		if ($forward_name != null) {
			$view_name = $this->getViewClassName($forward_name);
			$view_class =& new $view_name($this->backend, $forward_name, $this->_getForwardPath($forward_name));
			$view_class->preforward();
			// 後方互換処理:(
			if (is_subclass_of($view_class, 'Ethna_ViewClass') == false) {
				$view_class =& new Ethna_ViewClass($this->backend, $forward_name, $this->_getForwardPath($forward_name));
			}
			$view_class->forward();
		}

		return 0;
	}

	/**
	 *  SOAPフレームワークの処理を実行する
	 *
	 *	(experimental)
 	 *
	 *  @access private
	 */
	function _trigger_SOAP()
	{
		// アクションスクリプトをインクルード
		$this->_includeActionScript();

		// SOAPエントリクラス
		$gg =& new Ethna_SoapGatewayGenerator();
		$script = $gg->generate();
		eval($script);

		// SOAPリクエスト処理
		$server =& new SoapServer(null, array('uri' => $this->config->get('url')));
		$server->setClass($gg->getClassName());
		$server->handle();
	}

	/**
	 *	AMF(Flash Remoting)フレームワークの処理を実行する
	 *
	 *	(experimental)
	 *
	 *	@access	public
	 */
	function _trigger_AMF()
	{
		include_once('ethna/contrib/amfphp/app/Gateway.php');

		$this->action_error =& new Ethna_ActionError();

		// Credentialヘッダでセッションを処理するのでここではnullに設定
		$this->session = null;

		$this->_setLanguage($this->language, $this->system_encoding, $this->client_encoding);

		// backendオブジェクト
		$backend =& new Ethna_Backend($this);
		$this->backend =& $backend;

		// アクションスクリプトをインクルード
		$this->_includeActionScript();

		// amfphpに処理を委譲
		$gateway =& new Gateway();
		$gateway->setBaseClassPath('');
		$gateway->service();
	}

	/**
	 *	エラーハンドラ
	 *
	 *	エラー発生時の追加処理を行いたい場合はこのメソッドをオーバーライドする
	 *	(アラートメール送信等−デフォルトではログ出力時にアラートメール
	 *	が送信されるが、エラー発生時に別にアラートメールをここで送信
	 *	させることも可能)
	 *
	 *	@access	public
	 *	@param	object	Ethna_Error		エラーオブジェクト
	 */
	function handleError(&$error)
	{
		// ログ出力
		list ($log_level, $dummy) = $this->logger->errorLevelToLogLevel($error->getLevel());
		$message = $error->getMessage();
		$this->logger->log($log_level, sprintf("[APP(%d)] %s", $error->getCode(), $message));
	}

	/**
	 *	エラーメッセージを取得する
	 *
	 *	@access	public
	 *	@param	int		$code		エラーコード
	 *	@return	string	エラーメッセージ
	 */
	function getErrorMessage($code)
	{
		$message_list =& $GLOBALS['_Ethna_error_message_list'];
		for ($i = count($message_list)-1; $i >= 0; $i--) {
			if (array_key_exists($code, $message_list[$i])) {
				return $message_list[$i][$code];
			}
		}
		return null;
	}

	/**
	 *	指定されたアクションのフォームクラス名を返す(オブジェクトの生成は行わない)
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクション名
	 *	@return	string	アクションのフォームクラス名
	 */
	function getActionFormName($action_name)
	{
		$action_obj =& $this->_getAction($action_name);
		if (is_null($action_obj)) {
			return null;
		}

		return $action_obj['form_name'];
	}

	/**
	 *	指定されたアクションのクラス名を返す(オブジェクトの生成は行わない)
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクションの名称
	 *	@return	string	アクションのクラス名
	 */
	function getActionClassName($action_name)
	{
		$action_obj =& $this->_getAction($action_name);
		if ($action_obj == null) {
			return null;
		}

		return $action_obj['class_name'];
	}

	/**
	 *	指定された遷移名に対応するビュークラス名を返す(オブジェクトの生成は行わない)
	 *
	 *	@access	public
	 *	@param	string	$forward_name	遷移先の名称
	 *	@return	string	view classのクラス名
	 */
	function getViewClassName($forward_name)
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
			$this->logger->log(LOG_DEBUG, 'view class is not defined for [%s] -> use default [%s]', $forward_name, 'Ethna_ViewClass');
			return 'Ethna_ViewClass';
		}
	}

	/**
	 *	アクションに対応するフォームクラス名が省略された場合のデフォルトクラス名を返す
	 *
	 *	デフォルトでは[プロジェクトID]_Form_[アクション名]となるので好み応じてオーバライドする
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクション名
	 *	@param	bool	$fallback		クライアント種別によるfallback on/off
	 *	@return	string	アクションフォーム名
	 */
	function getDefaultFormClass($action_name, $fallback = true)
	{
		$postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($action_name));

		$r = null;
		if ($this->getClientType() == CLIENT_TYPE_SOAP) {
			$r = sprintf("%s_SOAPForm_%s", $this->getAppId(), $postfix);
		} else if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$tmp = sprintf("%s_MobileAUForm_%s", $this->getAppId(), $postfix);
			if ($fallback == false || class_exists($tmp)) {
				$r = $tmp;
			}
		}

		if ($r == null) {
			$r = sprintf("%s_Form_%s", $this->getAppId(), $postfix);
		}
		$this->logger->log(LOG_DEBUG, "default action class [%s]", $r);
		return $r;
	}

	/**
	 *	アクションに対応するフォームパス名が省略された場合のデフォルトパス名を返す
	 *
	 *	デフォルトでは_getDefaultActionPath()と同じ結果を返す(1ファイルに
	 *	アクションクラスとフォームクラスが記述される)ので、好みに応じて
	 *	オーバーライドする
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクション名
	 *	@param	bool	$fallback		クライアント種別によるfallback on/off
	 *	@return	string	form classが定義されるスクリプトのパス名
	 */
	function getDefaultFormPath($action_name, $fallback = true)
	{
		return $this->getDefaultActionPath($action_name, $fallback);
	}

	/**
	 *	アクションに対応するアクションクラス名が省略された場合のデフォルトクラス名を返す
	 *
	 *	デフォルトでは[プロジェクトID]_Action_[アクション名]となるので好み応じてオーバライドする
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクション名
	 *	@param	bool	$fallback		クライアント種別によるfallback on/off
	 *	@return	string	アクションクラス名
	 */
	function getDefaultActionClass($action_name, $fallback = true)
	{
		$postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($action_name));

		$r = null;
		if ($this->getClientType() == CLIENT_TYPE_SOAP) {
			$r = sprintf("%s_SOAPAction_%s", $this->getAppId(), $postfix);
		} else if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$tmp = sprintf("%s_MobileAUAction_%s", $this->getAppId(), $postfix);
			if ($fallback == false || class_exists($tmp)) {
				$r = $tmp;
			}
		}

		if ($r == null) {
			$r = sprintf("%s_Action_%s", $this->getAppId(), $postfix);
		}
		$this->logger->log(LOG_DEBUG, "default action class [%s]", $r);
		return $r;
	}

	/**
	 *	アクションに対応するアクションパス名が省略された場合のデフォルトパス名を返す
	 *
	 *	デフォルトでは"foo_bar" -> "/Foo/Bar.php"となるので好み応じてオーバーライドする
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクション名
	 *	@param	bool	$fallback		クライアント種別によるfallback on/off
	 *	@return	string	アクションクラスが定義されるスクリプトのパス名
	 */
	function getDefaultActionPath($action_name, $fallback = true)
	{
		$default_path = preg_replace('/_(.)/e', "'/' . strtoupper('\$1')", ucfirst($action_name)) . '.' . $this->getExt('php');
		$action_dir = $this->getActiondir();

		if ($this->getClientType() == CLIENT_TYPE_SOAP) {
			$r = 'SOAP/' . $r;
		} else if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$r = 'MobileAU/' . $r;
		} else {
			$r = $default_path;
		}

		if ($fallback && file_exists($action_dir . $r) == false && $r != $default_path) {
			$this->logger->log(LOG_DEBUG, 'client_type specific file not found [%s] -> try defualt', $r);
			$r = $default_path;
		}

		$this->logger->log(LOG_DEBUG, "default action path [%s]", $r);
		return $r;
	}

	/**
	 *	遷移名に対応するビュークラス名が省略された場合のデフォルトクラス名を返す
	 *
	 *	デフォルトでは[プロジェクトID]_View_[遷移名]となるので好み応じてオーバライドする
	 *
	 *	@access	public
	 *	@param	string	$forward_name	forward名
	 *	@param	bool	$fallback		クライアント種別によるfallback on/off
	 *	@return	string	view classクラス名
	 */
	function getDefaultViewClass($forward_name, $fallback = true)
	{
		$postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($forward_name));

		$r = null;
		if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$tmp = sprintf("%s_MobileAUView_%s", $this->getAppId(), $postfix);
			if ($fallback == false || class_exists($tmp)) {
				$r = $tmp;
			}
		}

		if ($r == null) {
			$r = sprintf("%s_View_%s", $this->getAppId(), $postfix);
		}
		$this->logger->log(LOG_DEBUG, "default view class [%s]", $r);
		return $r;
	}

	/**
	 *	遷移名に対応するビューパス名が省略された場合のデフォルトパス名を返す
	 *
	 *	デフォルトでは"foo_bar" -> "/Foo/Bar.php"となるので好み応じてオーバーライドする
	 *
	 *	@access	public
	 *	@param	string	$forward_name	forward名
	 *	@param	bool	$fallback		クライアント種別によるfallback on/off
	 *	@return	string	view classが定義されるスクリプトのパス名
	 */
	function getDefaultViewPath($forward_name, $fallback = true)
	{
		$default_path = preg_replace('/_(.)/e', "'/' . strtoupper('\$1')", ucfirst($forward_name)) . '.' . $this->getExt('php');
		$view_dir = $this->getViewdir();

		if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$r = 'MobileAU/' . $r;
		} else {
			$r = $default_path;
		}

		if ($fallback && file_exists($view_dir . $r) == false && $r != $default_path) {
			$this->logger->log(LOG_DEBUG, 'client_type specific file not found [%s] -> try defualt', $r);
			$r = $default_path;
		}

		$this->logger->log(LOG_DEBUG, "default view path [%s]", $r);
		return $r;
	}

	/**
	 *	実行するアクション名を返す
	 *
	 *	@access	private
	 *	@param	mixed	$default_action_name	指定のアクション名
	 *	@return	string	実行するアクション名
	 */
	function _getActionName($default_action_name, $fallback_action_name)
	{
		// フォームから要求されたアクション名を取得する
		$form_action_name = $this->_getActionName_Form();
		$form_action_name = preg_replace('/[^a-z0-9\-_]+/i', '', $form_action_name);
		$this->logger->log(LOG_DEBUG, 'form_action_name[%s]', $form_action_name);

		// フォームからの指定が無い場合はエントリポイントに指定されたデフォルト値を利用する
		if ($form_action_name == "" && count($default_action_name) > 0) {
			$tmp = is_array($default_action_name) ? $default_action_name[0] : $default_action_name;
			if ($tmp{strlen($tmp)-1} == '*') {
				$tmp = substr($tmp, 0, -1);
			}
			$this->logger->log(LOG_DEBUG, '-> default_action_name[%s]', $tmp);
			$action_name = $tmp;
		} else {
			$action_name = $form_action_name;
		}

		// エントリポイントに配列が指定されている場合は指定以外のアクション名は拒否する
		if (is_array($default_action_name)) {
			if ($this->_isAcceptableActionName($action_name, $default_action_name) == false) {
				// 指定以外のアクション名で合った場合は$fallback_action_name(or デフォルト)
				$tmp = $fallback_action_name != "" ? $fallback_action_name : $default_action_name[0];
				if ($tmp{strlen($tmp)-1} == '*') {
					$tmp = substr($tmp, 0, -1);
				}
				$this->logger->log(LOG_DEBUG, '-> fallback_action_name[%s]', $tmp);
				$action_name = $tmp;
			}
		}

		$this->logger->log(LOG_DEBUG, '<<< action_name[%s] >>>', $action_name);

		return $action_name;
	}

	/**
	 *	フォームにより要求されたアクション名を返す
	 *
	 *	アプリケーションの性質に応じてこのメソッドをオーバーライドして下さい。
	 *	デフォルトでは"action_"で始まるフォーム値の"action_"の部分を除いたもの
	 *	("action_sample"なら"sample")がアクション名として扱われます
	 *
	 *	@access	protected
	 *	@return	string	フォームにより要求されたactionの名称
	 */
	function _getActionName_Form()
	{
		if (isset($_SERVER['REQUEST_METHOD']) == false) {
			return $default_action_name;
		}

		if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
			$http_vars =& $_POST;
		} else {
			$http_vars =& $_GET;
		}

		// フォーム値からリクエストされたアクション名を取得する
		$action_name = $sub_action_name = null;
		foreach ($http_vars as $name => $value) {
			if ($value == "" || strncmp($name, 'action_', 7) != 0) {
				continue;
			}

			$tmp = substr($name, 7);

			// type="image"対応
			if (preg_match('/_x$/', $name) || preg_match('/_y$/', $name)) {
				$tmp = substr($tmp, 0, strlen($tmp)-2);
			}

			// value="dummy"となっているものは優先度を下げる
			if ($value == "dummy") {
				$sub_action_name = $tmp;
			} else {
				$action_name = $tmp;
			}
		}
		if ($action_name == null) {
			$action_name = $sub_action_name;
		}

		return $action_name;
	}

	/**
	 *	フォームにより要求されたアクション名に対応する定義を返す
	 *
	 *	@access	private
	 *	@param	string	$action_name	アクション名
	 *	@return	array	アクション定義
	 */
	function &_getAction($action_name)
	{
		if ($this->client_type == CLIENT_TYPE_SOAP) {
			$action =& $this->soap_action;
		} else {
			$action =& $this->action;
		}

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
		} else {
			// 明示指定されたフォームクラスが定義されていない場合は警告
			$this->logger->log(LOG_WARNING, 'stated form class is not defined [%s]', $action_obj['form_name']);
		}

		// 必要条件の確認
		if (class_exists($action_obj['class_name']) == false) {
			$this->logger->log(LOG_WARNING, 'action class is not defined [%s]', $action_obj['class_name']);
			return null;
		}
		if (class_exists($action_obj['form_name']) == false) {
			// フォームクラスは未定義でも良い
			$this->logger->log(LOG_DEBUG, 'form class is not defined [%s] -> falling back to default [%s]', $action_obj['form_name'], 'Ethna_ActionForm');
			$action_obj['form_name'] = 'Ethna_ActionForm';
		}

		$action_obj['inspect'] = true;
		$action[$action_name] = $action_obj;
		return $action[$action_name];
	}

	/**
	 *	アクション名とアクションクラスからの戻り値に基づいて遷移先を決定する
	 *
	 *	@access	protected
	 *	@param	string	$action_name	アクション名
	 *	@param	string	$retval			アクションクラスからの戻り値
	 *	@return	string	遷移先
	 */
	function _sortForward($action_name, $retval)
	{
		return $retval;
	}

	/**
	 *	遷移名からテンプレートファイルのパス名を取得する
	 *
	 *	@access	private
	 *	@param	string	$forward_name	forward名
	 *	@return	string	テンプレートファイルのパス名
	 */
	function _getForwardPath($forward_name)
	{
		$forward_obj = null;

		if (isset($this->forward[$forward_name]) == false) {
			// try default
			$this->forward[$forward_name] = array();
		}
		$forward_obj =& $this->forward[$forward_name];
		if (isset($forward_obj['forward_path']) == false) {
			// 省略値補正
			$forward_obj['forward_path'] = $this->_getDefaultForwardPath($forward_name);
		}

		return $forward_obj['forward_path'];
	}

	/**
	 *	使用言語を設定する
	 *
	 *	将来への拡張のためのみに存在しています。現在は特にオーバーライドの必要はありません。
	 *
	 *	@access	protected
	 *	@param	string	$language			言語定義(LANG_JA, LANG_EN...)
	 *	@param	string	$system_encoding	システムエンコーディング名
	 *	@param	string	$client_encoding	クライアントエンコーディング
	 */
	function _setLanguage($language, $system_encoding = null, $client_encoding = null)
	{
		$this->language = $language;
		$this->system_encoding = $system_encoding;
		$this->client_encoding = $client_encoding;

		$this->i18n->setLanguage($language, $system_encoding, $client_encoding);
	}

	/**
	 *	デフォルト状態での使用言語を取得する
	 *
	 *	@access	protected
	 *	@return	array	使用言語,システムエンコーディング名,クライアントエンコーディング名
	 */
	function _getDefaultLanguage()
	{
		return array(LANG_JA, null, null);
	}

	/**
	 *	デフォルト状態でのクライアントタイプを取得する
	 *
	 *	@access	protected
	 *	@return	int		クライアントタイプ定義(CLIENT_TYPE_WWW...)
	 */
	function _getDefaultClientType()
	{
		if (is_null($GLOBALS['_Ethna_client_type']) == false) {
			return $GLOBALS['_Ethna_client_type'];
		}
		return CLIENT_TYPE_WWW;
	}

	/**
	 *	アクションスクリプトをインクルードする
	 *
	 *	ただし、インクルードしたファイルにクラスが正しく定義されているかどうかは保証しない
	 *
	 *	@access	private
	 *	@param	array	$action_obj		アクション定義
	 *	@param	string	$action_name	アクション名
	 */
	function _includeActionScript($action_obj, $action_name)
	{
		$class_path = $form_path = null;

		$action_dir = $this->getActiondir();

		// class_path属性チェック
		if (isset($action_obj['class_path'])) {
			if (file_exists($action_dir . $action_obj['class_path']) == false) {
				$this->logger->log(LOG_WARNING, 'class_path file not found [%s] -> try default', $action_obj['class_path']);
			} else {
				include_once($action_dir . $action_obj['class_path']);
				$class_path = $action_obj['class_path'];
			}
		}

		// デフォルトチェック
		if (is_null($class_path)) {
			$class_path = $this->getDefaultActionPath($action_name);
			if (file_exists($action_dir . $class_path)) {
				include_once($action_dir . $class_path);
			} else {
				$this->logger->log(LOG_DEBUG, 'default action file not found [%s] -> try all files', $class_path);
				$class_path = null;
			}
		}
		
		// 全ファイルインクルード
		if (is_null($class_path)) {
			$this->_includeDirectory($this->getActiondir());
			return;
		}

		// form_path属性チェック
		if (isset($action_obj['form_path'])) {
			if ($action_obj['form_path'] == $class_path) {
				return;
			}
			if (file_exists($action_dir . $action_obj['form_path']) == false) {
				$this->logger->log(LOG_WARNING, 'form_path file not found [%s] -> try default', $action_obj['form_path']);
			} else {
				include_once($action_dir . $action_obj['form_path']);
				$form_path = $action_obj['form_path'];
			}
		}

		// デフォルトチェック
		if (is_null($form_path)) {
			$form_path = $this->getDefaultFormPath($action_name);
			if ($form_path == $class_path) {
				return;
			}
			if (file_exists($action_dir . $form_path)) {
				include_once($action_dir . $form_path);
			} else {
				$this->logger->log(LOG_DEBUG, 'default form file not found [%s] -> maybe falling back to default form class', $form_path);
			}
		}
	}

	/**
	 *	ビュースクリプトをインクルードする
	 *
	 *	ただし、インクルードしたファイルにクラスが正しく定義されているかどうかは保証しない
	 *
	 *	@access	private
	 *	@param	array	$forward_obj	遷移定義
	 *	@param	string	$forward_name	遷移名
	 */
	function _includeViewScript($forward_obj, $forward_name)
	{
		$view_dir = $this->getViewdir();

		// view_path属性チェック
		if (isset($action_obj['view_path'])) {
			if (file_exists($view_dir . $forward_obj['view_path']) == false) {
				$this->logger->log(LOG_WARNING, 'view_path file not found [%s] -> try default', $forward_obj['view_path']);
			} else {
				include_once($action_dir . $forward_obj['view_path']);
				return;
			}
		}

		// デフォルトチェック
		$view_path = $this->getDefaultViewPath($forward_name);
		if (file_exists($view_dir . $view_path)) {
			include_once($view_dir . $view_path);
			return;
		} else {
			$this->logger->log(LOG_DEBUG, 'default view file not found [%s]', $view_path);
			$view_path = null;
		}
	}

	/**
	 *	ディレクトリ以下の全てのスクリプトをインクルードする
	 *
	 *	@access	private
	 */
	function _includeDirectory($dir)
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
				include_once("$dir/$file");
			}
		}
		closedir($dh);
	}

	/**
	 *  テンプレートエンジンのデフォルト状態を設定する
	 *
	 *  @access protected
	 *  @param  object  Smarty  $smarty テンプレートエンジンオブジェクト
	 */
	function _setDefaultTemplateEngine(&$smarty)
	{
	}

	/**
	 *	Ethnaマネージャを設定する(不要な場合は空のメソッドとしてオーバーライドしてもよい)
	 *
	 *	@access	protected
	 */
	function _activateEthnaManager()
	{
		$base = dirname(dirname(__FILE__));

		if ($this->config->get('debug') == false) {
			return;
		}

		// action設定
		$this->action['__ethna_info__'] = array(
			'form_name' =>	'Ethna_Form_Info',
			'class_name' =>	'Ethna_Action_Info',
		);
		$this->action['__ethna_info_do__'] = array(
			'form_name' =>	'Ethna_Form_InfoDo',
			'class_name' =>	'Ethna_Action_InfoDo',
		);

		// forward設定
		$forward_obj = array();

		$forward_obj['forward_path'] = sprintf("%s/tpl/info.tpl", $base);
		$forward_obj['view_name'] = 'Ethna_Action_Info';
		$this->forward['__ethna_info__'] = $forward_obj;
	}

	/**
	 *	遷移名に対応するテンプレートパス名が省略された場合のデフォルトパス名を返す
	 *
	 *	デフォルトでは"foo_bar"というforward名が"foo/bar" + テンプレート拡張子となる
	 *	ので好み応じてオーバライドする
	 *
	 *	@access	protected
	 *	@param	string	$forward_name	forward名
	 *	@return	string	forwardパス名
	 */
	function _getDefaultForwardPath($forward_name)
	{
		return str_replace('_', '/', $forward_name) . '.' . $this->ext['tpl'];
	}

	/**
	 *	設定ファイルのDSN定義から使用するデータを再構築する(スレーブアクセス分岐等)
	 *
	 *	DSNの定義方法(デフォルト:設定ファイル)を変えたい場合はここをオーバーライドする
	 *
	 *	@access	protected
	 *	@return	array	DSN定義
	 */
	function _prepareDSN()
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
	 *	DSNのアクセス分岐を行う
	 *	
	 *	スレーブサーバへの振分け処理(デフォルト:ランダム)を変更したい場合はこのメソッドをオーバーライドする
	 *
	 *	@access	protected
	 *	@param	string	$type		DB種別
	 *	@param	array	$dsn_list	DSN一覧
	 *	@return	string	選択されたDSN
	 */
	function _selectDSN($type, $dsn_list)
	{
		// デフォルト:ランダム
		list($usec, $sec) = explode(' ', microtime());
		mt_srand($sec + ((float) $usec * 100000));
		$n = mt_rand(0, count($dsn_list)-1);
		
		return $dsn_list[$n];
	}

	/**
	 *	アクション名が実行許可されているものかどうかを返す
	 *
	 *	@access	private
	 *	@param	string	$action_name			リクエストされたアクション名
	 *	@param	array	$default_action_name	許可されているアクション名
	 *	@return	bool	true:許可 false:不許可
	 */
	function _isAcceptableActionName($action_name, $default_action_name)
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
	 *	フィルターチェインを生成する
	 *
	 *	@access	private
	 */
	function _createFilterChain()
	{
		$this->filter_chain = array();
		foreach ($this->filter as $filter) {
			$file = sprintf("%s/%s.%s", $this->getDirectory('filter'), $filter, $this->getExt('php'));
			if (file_exists($file)) {
				include_once($file);
			}
			if (class_exists($filter)) {
				$this->filter_chain[] =& new $filter($this);
			}
		}
	}
}
// }}}
?>
