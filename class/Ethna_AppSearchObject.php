<?php
// vim: foldmethod=marker
/**
 *	Ethna_AppSearchObject.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_AppSearchObject
/**
 *	アプリケーションオブジェクト検索条件クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_AppSearchObject
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	string	検索値 */
	var $value;

	/**	@var	int		検索条件 */
	var $condition;

	/**#@-*/


	/**
	 *	Ethna_AppSearchObjectのコンストラクタ
	 *
	 *	@access	public
	 *	@param	string	$value		検索値
	 *	@param	int		$condition	検索条件(OBJECT_CONDITION_NE,...)
	 */
	function Ethna_AppSearchObject($value, $condition)
	{
		$this->value = $value;
		$this->condition = $condition;
	}
}
// }}}
?>
