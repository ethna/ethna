<?php
/**
 *	{$project_prefix}.php
 *
 *	@package	{$project_id}
 *
 *	$Id$
 */

/**
 *	indexフォームの実装
 *
 *	@author		yourname
 *	@access		public
 *	@package	{$project_id}
 */
class {$project_id}_Form_Index extends Ethna_ActionClass
{
	/**
	 *	@access	private
	 *	@var	array	フォーム値定義
	 */
	var	$form = array(
	);
}

/**
 *	indexアクションの実装
 *
 *	@author		yourname
 *	@access		public
 *	@package	{$project_id}
 */
class {$project_id}_Action_Index extends Ethna_ActionClass
{
	/**
	 *	indexアクションの前処理
	 *
	 *	@access	public
	 *	@return	string		Forward先(正常終了ならnull)
	 */
	function prepare()
	{
		return null;
	}

	/**
	 *	indexアクションの実装
	 *
	 *	@access	public
	 *	@return	string	遷移名
	 */
	function perform()
	{
		return 'index';
	}

	/**
	 *	遷移前処理
	 *
	 *	@access	public
	 */
	function preforward()
	{
	}
}
?>
