<?php
/**
 *	backend.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	バックエンド処理クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Backend
{
	/**#@+
	 *	@access		private
	 */

	/**
	 *	@var	object	Ethna_Controller	controllerオブジェクト
	 */
	var	$controller;

	/**
	 *	@var	object	Ethna_Controller	controllerオブジェクト($controllerの省略形)
	 */
	var	$ctl;

	/**
	 *	@var	object	Ethna_Config		設定オブジェクト
	 */
	var	$config;

	/**
	 *	@var	object	Ethna_I18N			i18nオブジェクト
	 */
	var $i18n;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var $action_error;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト($action_errorの省略形)
	 */
	var $ae;

	/**
	 *	@var	object	Ethna_ActionForm	action formオブジェクト
	 */
	var $action_form;

	/**
	 *	@var	object	Ethna_ActionForm	action formオブジェクト($action_formの省略形)
	 */
	var $af;

	/**
	 *	@var	object	Ethna_Session		セッションオブジェクト
	 */
	var $session;

	/**
	 *	@var	object	Ethna_DB			DBオブジェクト
	 */
	var $db;

	/**
	 *	@var	object	Ethna_Logger		ログオブジェクト
	 */
	var $logger;

	/**
	 *	@var	array	マネージャオブジェクトキャッシュ
	 */
	var $manager = array();

	/**#@-*/


	/**
	 *	Ethna_Backendクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	コントローラオブジェクト
	 */
	function Ethna_Backend(&$controller)
	{
		// オブジェクトの設定
		$this->controller =& $controller;
		$this->ctl =& $this->controller;

		$this->config =& $controller->getConfig();
		$this->i18n =& $controller->getI18N();

		$this->action_error =& $controller->getActionError();
		$this->ae =& $this->action_error;
		$this->action_form =& $controller->getActionForm();
		$this->af =& $this->action_form;

		$this->session =& $controller->getSession();
		$this->db = null;
		$this->logger =& $this->controller->getLogger();

		// マネージャオブジェクトの生成
		$manager_list = $controller->getManagerList();
		foreach ($manager_list as $key => $value) {
			$class_name = $controller->getAppId() . "_" . ucfirst(strtolower($value)) . 'Manager';
			$this->manager[$value] = new $class_name($this);
		}

		foreach ($manager_list as $key => $value) {
			foreach ($manager_list as $k => $v) {
				if ($v == $value) {
					/* skip myself */
					continue;
				}
				$this->manager[$value]->$k =& $this->manager[$v];
			}
		}
	}

	/**
	 *	controllerオブジェクトへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Controller	controllerオブジェクト
	 */
	function &getController()
	{
		return $this->controller;
	}

	/**
	 *	設定オブジェクトへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Config		設定オブジェクト
	 */
	function &getConfig()
	{
		return $this->config;
	}

	/**
	 *	I18Nオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_I18N	i18nオブジェクト
	 */
	function &getI18N()
	{
		return $this->i18n;
	}

	/**
	 *	action errorオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionError	action errorオブジェクト
	 */
	function &getActionError()
	{
		return $this->action_error;
	}

	/**
	 *	action formオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionForm	action formオブジェクト
	 */
	function &getActionForm()
	{
		return $this->action_form;
	}

	/**
	 *	セッションオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Session	セッションオブジェクト
	 */
	function &getSession()
	{
		return $this->session;
	}

	/**
	 *	マネージャオブジェクトへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_AppManager	マネージャオブジェクト
	 */
	function &getManager($type)
	{
		if (isset($this->manager[$type])) {
			return $this->manager[$type];
		}
		return null;
	}

	/**
	 *	アプリケーションのベースディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	ベースディレクトリのパス名
	 */
	function getBasedir()
	{
		return $this->controller->getBasedir();
	}

	/**
	 *	アプリケーションのテンプレートディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	テンプレートディレクトリのパス名
	 */
	function getTemplatedir()
	{
		return $this->controller->getTemplatedir();
	}

	/**
	 *	アプリケーションのテンポラリディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	テンポラリディレクトリのパス名
	 */
	function getTmpdir()
	{
		return $this->controller->getDirectory('tmp');
	}

	/**
	 *	ログを出力する
	 *
	 *	@access	public
	 *	@param	int		$level		ログレベル(LOG_DEBUG, LOG_NOTICE...)
	 *	@param	string	$message	ログメッセージ(printf形式)
	 */
	function log($level, $message)
	{
		$args = func_get_args();
		if (count($args) > 2) {
			array_splice($args, 0, 2);
			$message = vsprintf($message, $args);
		}
		$this->logger->log($level, $message);
	}

	/**
	 *	バックエンド処理を実行する
	 *
	 *	@access	public
	 *	@param	string	$action_name	実行するアクションの名称
	 *	@return	string	Forward名(nullならforwardしない)
	 */
	function perform($action_name)
	{
		$forward_name = null;

		$action_class_name = $this->controller->getActionClassName($action_name);
		if ($action_class_name == null) {
			return null;
		}
		$action_class = new $action_class_name($this);

		// アクションの実行
		$forward_name = $action_class->prepare();
		if ($forward_name != null) {
			return $forward_name;
		}

		$forward_name = $action_class->perform();

		return $forward_name;
	}

	/**
	 *	画面表示前処理を行う
	 *
	 *	@access	public
	 *	@param	string	$action_class_name	forward名に関連付けられたaction class名
	 */
	function preforward($action_class_name)
	{
		$action_class = new $action_class_name($this);
		$action_class->preforward();
	}

	/**
	 *	DBオブジェクトを返す
	 *
	 *	@access	public
	 *	@return	mixed	Ethna_DB:DBオブジェクト null:DSN設定なし Ethna_Error:エラー
	 */
	function &getDB()
	{
		if ($this->db != null) {
			return $this->db;
		}

		$dsn = $this->controller->getDSN();
		if ($dsn == "") {
			// DB接続不要
			return null;
		}

		$this->db =& new Ethna_DB($this->controller->getDSN(), false, $this->controller);
		$r = $this->db->connect();
		if (Ethna::isError($r)) {
			return $r;
		}

		register_shutdown_function(array($this, 'shutdownDB'));

		return $this->db;
	}

	/**
	 *	DBコネクションを切断する
	 *
	 *	@access	public
	 */
	function shutdownDB()
	{
		if ($this->db != null && $this->db->isValid()) {
			$this->db->disconnect();
			$this->db = null;
		}
	}
}
?>
