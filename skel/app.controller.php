<?php
/**
 *	{$project_id}_Controller.php
 *
 *	@package	{$project_id}
 *
 *	$Id$
 */

/** アプリケーションベースディレクトリ */
define('BASE', dirname(dirname(__FILE__)));

// include_pathの設定(アプリケーションディレクトリを追加)
$app = BASE . "/app";
$lib = BASE . "/lib";
ini_set('include_path', ini_get('include_path') . ":$app:$lib");


/** アプリケーションライブラリのインクルード */
include_once('Ethna/Ethna.php');
include_once('{$project_id}_Error.php');

/**
 *	{$project_id}アプリケーションのコントローラ定義
 *
 *	@author		your name
 *	@access		public
 *	@package	{$project_id}
 */
class {$project_id}_Controller extends Ethna_Controller
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	string	アプリケーションID
	 */
	var	$appid = '{$application_id}';

	/**
	 *	@var	array	forward定義
	 */
	var $forward = array(
		/*
		 *	TODO: ここにforward先を記述してください
		 *
		 *	記述例：
		 *
		 *	'index'			=> array(
		 *		'preforward_name'	=> 'IndexClass',
		 *	),
		 */
	);

	/**
	 *	@var	array	action定義
	 */
	var $action = array(
		/*
		 *	TODO: ここにaction定義を記述してください
		 */
		'index'				=> array(),
	);

	/**
	 *	@var	array	soap action定義
	 */
	var $soap_action = array(
		/*
		 *	TODO: ここにSOAPアプリケーション用のaction定義を
		 *	記述してください
		 *	記述例：
		 *
		 *	'sample'			=> array(),
		 */
	);

	/**
	 *	@var	array	クラス定義
	 */
	var $class = array(
		/*
		 *	TODO: 設定クラス、ログクラス、SQLクラスをオーバーライド
		 *	した場合は下記のクラス名を忘れずに変更してください
		 */
		'config'        => 'Ethna_Config',
		'logger'        => 'Ethna_Logger',
		'sql'           => 'Ethna_AppSQL',
	);

	/**
	 *	@var	array	マネージャ一覧
	 */
	var $manager = array(
		/*
		 *	TODO: ここにアプリケーションのマネージャオブジェクト一覧を
		 *	記述してください
		 *
		 *	記述例：
		 *
		 *	'um'	=> 'User',
		 */
	);

	/**
	 *	@var	array	smarty modifier定義
	 */
	var $smarty_modifier_plugin = array(
		/*
		 *	TODO: ここにユーザ定義のsmarty modifier一覧を記述してください
		 *
		 *	記述例：
		 *
		 *	'smarty_modifier_foo_bar',
		 */
	);

	/**
	 *	@var	array	smarty function定義
	 */
	var $smarty_function_plugin = array(
		/*
		 *	TODO: ここにユーザ定義のsmarty function一覧を記述してください
		 *
		 *	記述例：
		 *
		 *	'smarty_function_foo_bar',
		 */
	);

	/**#@-*/

	/**
	 *	遷移時のデフォルトマクロを設定する
	 *
	 *	@access	protected
	 *	@param	object	Smarty	$smarty	テンプレートエンジンオブジェクト
	 */
	function _setDefaultMacro(&$smarty)
	{
		$smarty->assign_by_ref('session_name', session_name());
		$smarty->assign_by_ref('session_id', session_id());

		/* ログインフラグ(true/false) */
		if ($this->session->isStart()) {
			$smarty->assign_by_ref('login', $this->session->isStart());
		}
	}
}
?>
