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
class {$project_id}_Form_Index extends Ethna_ActionForm
{
	/**
	 *	@access	private
	 *	@var	array	フォーム値定義
	 */
	var	$form = array(
		/*
		 *	TODO: このアクションが使用するフォーム値定義を記述してください
		 *
		 *	記述例(typeを除く全ての要素は省略可能)：
		 *
		 *	'sample' => array(
		 *		'name'			=> 'サンプル',		// 表示名
		 *		'required'      => true,			// 必須オプション(true/false)
		 *		'min'           => null,			// 最小値
		 *		'max'           => null,			// 最大値
		 *		'regexp'        => null,			// 文字種指定(正規表現)
		 *		'custom'        => null,			// メソッドによるチェック
		 *		'convert'       => null,			// 入力値自動変換オプション
		 *		'form_type'		=> FORM_TYPE_TEXT	// フォーム型
		 *		'type'          => VAR_TYPE_INT,	// 入力値型
		 *	),
		 */
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
