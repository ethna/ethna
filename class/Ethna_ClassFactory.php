<?php
// vim: foldmethod=marker
/**
 *	Ethna_ClassFactory.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_ClassFactory
/**
 *	Ethnaフレームワークのオブジェクト生成ゲートウェイ
 *
 *	DIコンテナか、ということも考えましたがEthnaではこの程度の単純なものに
 *	留めておきます。アプリケーションレベルDIしたい場合はフィルタチェインを
 *	使って実現することも出来ます。
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_ClassFactory
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	object	Ethna_Controller	controllerオブジェクト */
	var	$controller;

	/**	@var	object	Ethna_Controller	controllerオブジェクト(省略形) */
	var	$ctl;
	
	/**	@var	array	クラス定義 */
	var	$class = array();

	/**	@var	array	生成済みオブジェクトキャッシュ */
	var	$object = array();

	/**#@-*/


	/**
	 *	Ethna_ClassFactoryクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	controllerオブジェクト
	 *	@param	array						$class			クラス定義
	 */
	function Ethna_ClassFactory(&$controller, $class)
	{
		$this->controller =& $controller;
		$this->ctl =& $controller;
		$this->class = $class;
	}

	/**
	 *	クラスキーに対応するオブジェクトを返す
	 *
	 *	@access	public
	 *	@param	string	$key	クラスキー
	 *	@param	bool	$weak	オブジェクトが未生成の場合の強制生成フラグ(default: false)
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &getObject($key, $weak = false)
	{
		if (isset($this->class[$key]) == false) {
			return null;
		}
		$class_name = $this->class[$key];
		if (isset($this->object[$key]) && is_object($this->object[$key])) {
			return $this->object[$key];
		}

		$method = sprintf('_getObject_%s', ucfirst($key));
		if (method_exists($this, $method)) {
			$obj =& $this->$method($class_name);
		} else {
			$obj =& new $class_name();
		}
		$this->object[$key] =& $obj;

		return $obj;
	}

	/**
	 *	クラスキーに対応するクラス名を返す
	 *
	 *	@access	public
	 *	@param	string	$key	クラスキー
	 *	@return	string	クラス名
	 */
	function getObjectName($key)
	{
		if (isset($this->class[$key]) == false) {
			return null;
		}

		return $this->class[$key];
	}

	/**
	 *	オブジェクト生成メソッド(backend)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_Backend($class_name)
	{
		$_ret_object =& new $class_name($this->ctl);
		return $_ret_object;
	}

	/**
	 *	オブジェクト生成メソッド(config)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_Config($class_name)
	{
		$_ret_object =& new $class_name($this->ctl);
		return $_ret_object;
	}

	/**
	 *	オブジェクト生成メソッド(i18n)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_I18n($class_name)
	{
		$_ret_object =& new $class_name($this->ctl->getDirectory('locale'), $this->ctl->getAppId());
		return $_ret_object;
	}

	/**
	 *	オブジェクト生成メソッド(logger)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_Logger($class_name)
	{
		$_ret_object =& new $class_name($this->ctl);
		return $_ret_object;
	}

	/**
	 *	オブジェクト生成メソッド(plugin)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_Plugin($class_name)
	{
		$_ret_object =& new $class_name($this->ctl);
		return $_ret_object;
	}

	/**
	 *	オブジェクト生成メソッド(session)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_Session($class_name)
	{
		$_ret_object =& new $class_name($this->ctl->getAppId(), $this->ctl->getDirectory('tmp'), $this->ctl->getLogger());
		return $_ret_object;
	}

	/**
	 *	オブジェクト生成メソッド(sql)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		クラス名
	 *	@return	object	生成されたオブジェクト(エラーならnull)
	 */
	function &_getObject_Sql($class_name)
	{
		$_ret_object =& new $class_name($this->ctl);
		return $_ret_object;
	}
}
// }}}
?>
