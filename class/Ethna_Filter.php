<?php
// vim: foldmethod=marker
/**
 *	Ethna_Filter.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Filter
/**
 *	フレームワークのフィルタ基底クラス
 *
 *	Mojaviの真似です（きっぱり）。アクション実行前に各種処理を行うことが
 *	出来ます。
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Filter
{
	/**#@+
	 *	@access	private
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
	 *	@var	object	Ethna_Logger		ログオブジェクト
	 */
	var $logger;

	/**#@-*/


	/**
	 *	Ethna_Filterのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	コントローラオブジェクト
	 */
	function Ethna_Filter(&$controller)
	{
		// オブジェクトの設定
		$this->controller =& $controller;
		$this->ctl =& $this->controller;

		$this->config =& $controller->getConfig();
		$this->logger =& $this->controller->getLogger();
	}

	/**
	 *	実行前フィルタ
	 *
	 *	@access	public
	 */
	function prefilter()
	{
	}

	/**
	 *	実行後フィルタ
	 *
	 *	@access	public
	 */
	function postfilter()
	{
	}
}
// }}}
?>
