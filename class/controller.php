<?php
// vim: foldmethod=marker
/**
 *	controller.php
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
	var $appid = 'PHPSTRUTS';

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
		'locale'		=> 'locale',
		'log'			=> 'log',
		'template'		=> 'template',
		'template_c'	=> 'tmp',
		'tmp'			=> 'tmp',
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
		'logger'		=> 'Ethna_Logger',
		'sql'			=> 'Ethna_AppSQL',
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
	var $backend;

	/**
	 *	@var	object	Ethna_I18N		i18nオブジェクト
	 */
	var $i18n;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var $action_error;

	/**
	 *	@var	object	Ethna_ActionForm	action formオブジェクト
	 */
	var $action_form;

	/**
	 *	@var	object	Ethna_Session		セッションオブジェクト
	 */
	var $session;

	/**
	 *	@var	object	Ethna_Config		設定オブジェクト
	 */
	var	$config;

	/**
	 *	@var	object	Ethna_Logger		ログオブジェクト
	 */
	var	$logger;

	/**
	 *	@var	object	Ethna_AppSQL		SQLオブジェクト
	 */
	var	$sql;

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

		foreach ($this->directory as $key => $value) {
			if ($value[0] != '/') {
				$this->directory[$key] = $this->base . (empty($this->base) ? '' : '/') . $value;
			}
		}
		$this->i18n =& new Ethna_I18N($this->getDirectory('locale'), $this->getAppId());
		$this->action_form = null;
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
	 *	action errorオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionError	action errorオブジェクト
	 */
	function &getActionError()
	{
		return $this->action_error;
	}

	/**
	 *	action formオブジェクトのアクセサ
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionForm	action formオブジェクト
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
	 *	実行中のaction名を返す
	 *
	 *	@access	public
	 *	@return	string	実行中のaction名
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

		return $smarty;
	}

	/**
	 *	アプリケーションのエントリポイント
	 *
	 *	@access	public
	 *	@param	string	$class_name		アプリケーションコントローラのクラス名
	 *	@param	mixed	$action_name	指定のアクション名(省略可)
	 *	@static
	 */
	function main($class_name, $action_name = "", $client_type = null)
	{
		$c =& new $class_name;
		if ($client_type != null) {
			$c->setClientType($client_type);
		}
		$c->trigger($action_name);
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
		$c->setClientType(CLIENT_TYPE_WWW);
		$c->trigger($action_name);
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
		$c->trigger_SOAP();
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
		$c->trigger_AMF();
	}

	/**
	 *	フレームワークの処理を開始する
	 *
	 *	引数$default_action_nameに配列が指定された場合、その配列で指定された
	 *	action以外は受け付けない(それ以外のactionが指定された場合、配列の先頭
	 *	で指定されたアクションが実行される)
	 *
	 *	@access	public
	 *	@param	mixed	$default_action_name	指定のアクション名
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function trigger($default_action_name = "")
	{
		// actionの決定
		$action_name = $this->_getActionName($default_action_name);
		$this->action_name = $action_name;
		$action_obj =& $this->_getAction($action_name);
		if (is_null($action_obj)) {
			// try default action
			if ($default_action_name != "") {
				$action_obj =& $this->_getAction($default_action_name);
			}
			if ($action_obj == null) {
				return Ethna::raiseError(E_APP_DEFINED_ACTION, "未定義のアクション[%s]", $action_name);
			} else {
				$action_name = $default_action_name;
			}
		}

		// action定義をinclude
		$this->_includeActionScript();

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
		if (Ethna::isError($forward_name)) {
			// (アプリケーションではなく)フレームワーク上でのエラー
			return $forward_name;
		}

		// forward前処理実行
		$backend->preforward($forward_name);

		if ($forward_name != null) {
			if ($this->_forward($forward_name) != 0) {
				return -1;
			}
		}

		return 0;
	}

	/**
	 *  SOAPフレームワークの処理を開始する
 	 *
	 *  @access public
	 */
	function trigger_SOAP()
	{
		// action定義をinclude
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
	 *	AMF(Flash Remoting)フレームワークの処理を開始する
	 *
	 *	@access	public
	 */
	function trigger_AMF()
	{
		include_once('ethna/contrib/amfphp/app/Gateway.php');

		$this->action_error =& new Ethna_ActionError();

		// Credentialヘッダでセッションを処理するのでここではnullに設定
		$this->session = null;

		$this->_setLanguage($this->language, $this->system_encoding, $this->client_encoding);

		// backendオブジェクト
		$backend =& new Ethna_Backend($this);
		$this->backend =& $backend;

		// action定義をinclude
		$this->_includeActionScript();

		// amfphpに処理を委譲
		$gateway =& new Gateway();
		$gateway->setBaseClassPath('');
		$gateway->service();
	}

	/**
	 *	致命的エラー発生時の画面を表示する
	 *
	 *	注意：メソッド呼び出し後全ての処理は中断される(このメソッドでexit()する)
	 *
	 *	@access	public
	 */
	function fatal()
	{
		exit(0);
	}

	/**
	 *	指定されたactionのフォームクラス名を返す(オブジェクトの生成は行わない)
	 *
	 *	@access	public
	 *	@param	string	$action_name	action名
	 *	@return	string	action formのクラス名
	 */
	function getActionFormName($action_name)
	{
		$action_obj =& $this->_getAction($action_name);
		if (is_null($action_obj)) {
			return null;
		}

		// 省略値補完
		if (isset($action_obj['form_name']) == false) {
			$action_obj['form_name'] = $this->_getDefaultFormClass($action_name);
		}

		if (class_exists($action_obj['form_name'])) {
			return $action_obj['form_name'];
		} else {
			// fall back to default
			return 'Ethna_ActionForm';
		}
	}

	/**
	 *	指定されたactionのクラス名を返す(オブジェクトの生成は行わない)
	 *
	 *	@access	public
	 *	@param	string	$action_name	actionの名称
	 *	@return	string	action classのクラス名
	 */
	function getActionClassName($action_name)
	{
		$action_obj =& $this->_getAction($action_name);
		if ($action_obj == null) {
			return null;
		}

		// 省略値補完
		if (isset($action_obj['class_name']) == false) {
			$action_obj['class_name'] = $this->_getDefaultActionClass($action_name);
		}

		return $action_obj['class_name'];
	}

	/**
	 *	指定されたforwardに対応するビュークラス名を返す(オブジェクトの生成は行わない)
	 *
	 *	ビュークラスはActionClassにpreforwardを実装したもの、あるいはViewClassを継承したものいずれかどちらでもよい(ViewClass推奨)
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

		$class_name = null;
		if (isset($this->forward[$forward_name]) && isset($this->forward[$forward_name]['preforward_name'])) {
			$class_name = $this->forward[$forward_name]['preforward_name'];
			if (class_exists($class_name) == false) {
				$this->backend->log(LOG_WARNING, 'specified preforward class [%s] is not defined -> falling back to default', $class_name);
			} else {
				return $class_name;
			}
		}

		$postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($forward_name));

		if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$tmp = sprintf("%s_MobileAUView_%s", $this->getAppId(), $postfix);
			if (class_exists($tmp)) {
				return $tmp;
			}
		}

		$class_name = sprintf("%s_View_%s", $this->getAppId(), $postfix);
		if (class_exists($class_name)) {
			return $class_name;
		}

		// ビュークラスは必須ではない
		return null;
	}

	/**
	 *	フォームにより要求されたaction名を返す
	 *
	 *	アプリケーションの性質に応じてこのメソッドをオーバーライドして下さい。
	 *	デフォルトでは"action_"で始まるフォーム値の"action_"の部分を除いたもの
	 *	("action_sample"なら"sample")がaction名として扱われます
	 *
	 *	@access	protected
	 *	@param	mixed	$default_action_name	指定のアクション名
	 *	@return	string	要求されたactionの名称
	 */
	function _getActionName($default_action_name)
	{
		if (isset($_SERVER['REQUEST_METHOD']) == false) {
			return $default_action_name;
		}

		if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
			$http_vars =& $_POST;
		} else {
			$http_vars =& $_GET;
		}

		$action_name = null;
		$fallback_action_name = null;
		foreach ($http_vars as $name => $value) {
			if ($value == "") {
				continue;
			}
			if (strncmp($name, 'action_', 7) == 0) {
				$tmp = substr($name, 7);
				if (preg_match('/_x$/', $name) || preg_match('/_y$/', $name)) {
					$tmp = substr($tmp, 0, strlen($tmp)-2);
				}
				if ($value != "" && $value != "dummy") {
					$action_name = $tmp;
				} else {
					$fallback_action_name = $tmp;
				}
			}
		}

		if ($action_name == null) {
			if ($fallback_action_name == null) {
				$action_name = is_array($default_action_name) ? $default_action_name[0] : $default_action_name;
			} else {
				$action_name = $fallback_action_name;
			}
		}
		if (is_array($default_action_name)) {
			if (in_array($action_name, $default_action_name) == false) {
				return $default_action_name[0];
			}
		}

		$this->logger->log(LOG_DEBUG, '<<<<<<<< action_name[%s] >>>>>>>>', $action_name);

		return $action_name;
	}

	/**
	 *	フォームにより要求されたactionに対応する定義を返す
	 *
	 *	@access	private
	 *	@param	string	$action_name	actionの名称
	 *	@return	array	action定義
	 */
	function &_getAction($action_name)
	{
		if ($this->client_type == CLIENT_TYPE_SOAP) {
			$action =& $this->soap_action;
		} else {
			$action =& $this->action;
		}

		if (isset($action[$action_name]) == false) {
			return null;
		}

		return $action[$action_name];
	}

	/**
	 *	指定されたforward名に対応する画面を出力する
	 *
	 *	@access	private
	 *	@param	string	$forward_name	Forward名
	 *	@return	bool	0:正常終了 -1:エラー
	 */
	function _forward($forward_name)
	{
		$forward_path = $this->_getForwardPath($forward_name);
		$smarty =& $this->getTemplateEngine();

		$form_array =& $this->action_form->getArray();
		$app_array =& $this->action_form->getAppArray();
		$app_ne_array =& $this->action_form->getAppNEArray();
		$smarty->assign_by_ref('form', $form_array);
		$smarty->assign_by_ref('app', $app_array);
		$smarty->assign_by_ref('app_ne', $app_ne_array);
		$smarty->assign_by_ref('errors', Ethna_Util::escapeHtml($this->action_error->getMessageList()));
		if (isset($_SESSION)) {
			$smarty->assign_by_ref('session', Ethna_Util::escapeHtml($_SESSION));
		}
		$smarty->assign('script', basename($_SERVER['PHP_SELF']));
		$smarty->assign('request_uri', htmlspecialchars($_SERVER['REQUEST_URI']));

		// デフォルトマクロの設定
		$this->_setDefaultMacro($smarty);

		$smarty->display($forward_path);

		return 0;
	}

	/**
	 *	forward名からテンプレートファイルのパス名を取得する
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
		return CLIENT_TYPE_WWW;
	}

	/**
	 *	action定義ファイルをincludeする
	 *
	 *	@access	private
	 */
	function _includeActionScript()
	{
		$ext = "." . $this->ext['php'];
		$ext_len = strlen($ext);
		$action_dir = (empty($this->directory['action']) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory['action'] . "/"));

		$dh = opendir($action_dir);
		if ($dh) {
			while (($file = readdir($dh)) !== false) {
				if (substr($file, -$ext_len, $ext_len) != $ext) {
					continue;
				}
				include_once("$action_dir/$file");
			}
		}
		closedir($dh);
	}

	/**
	 *	遷移時のデフォルトマクロを設定する
	 *
	 *	@access	protected
	 *	@param	object	Smarty	$smarty	テンプレートエンジンオブジェクト
	 */
	function _setDefaultMacro(&$smarty)
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
		$forward_obj['preforward_name'] = 'Ethna_Action_Info';
		$this->forward['__ethna_info__'] = $forward_obj;
	}

	/**
	 *	actionに対応するフォームクラス名が省略された場合のデフォルトクラス名を返す
	 *
	 *	デフォルトでは[プロジェクトID]_Form_[アクション名]となるので好み応じてオーバライドする
	 *
	 *	@access	protected
	 *	@param	string	$action_name	action名
	 *	@return	string	action formクラス名
	 */
	function _getDefaultFormClass($action_name)
	{
		$postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($action_name));

		if ($this->getClientType() == CLIENT_TYPE_SOAP) {
			return sprintf("%s_SOAPForm_%s", $this->getAppId(), $postfix);
		} else if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$tmp = sprintf("%s_MobileAUForm_%s", $this->getAppId(), $postfix);
			if (class_exists($tmp)) {
				return $tmp;
			}
		}

		return sprintf("%s_Form_%s", $this->getAppId(), $postfix);
	}

	/**
	 *	actionに対応するアクションクラス名が省略された場合のデフォルトクラス名を返す
	 *
	 *	デフォルトでは[プロジェクトID]_Action_[アクション名]となるので好み応じてオーバライドする
	 *
	 *	@access	protected
	 *	@param	string	$action_name	action名
	 *	@return	string	action classクラス名
	 */
	function _getDefaultActionClass($action_name)
	{
		$postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($action_name));

		if ($this->getClientType() == CLIENT_TYPE_SOAP) {
			return sprintf("%s_SOAPAction_%s", $this->getAppId(), $postfix);
		} else if ($this->getClientType() == CLIENT_TYPE_MOBILE_AU) {
			$tmp = sprintf("%s_MobileAUAction_%s", $this->getAppId(), $postfix);
			if (class_exists($tmp)) {
				return $tmp;
			}
		}

		return sprintf("%s_Action_%s", $this->getAppId(), $postfix);
	}

	/**
	 *	forwardに対応するテンプレートパス名が省略された場合のデフォルトパス名を返す
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
}
// }}}
?>
