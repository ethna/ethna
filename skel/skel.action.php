<?php
/**
 *	{$action_path}
 *
 *	@author		{$author}
 *	@package	{$project_id}
 *	@version	$Id$
 */

/**
 *	{$action_name}フォームの実装
 *
 *	@author		{$author}
 *	@access		public
 *	@package	{$project_id}
 */
class {$action_form} extends Ethna_ActionForm
{
	/**
	 *	@access	private
	 *	@var	array	フォーム値定義
	 */
	var	$form = array(
		/*
		'sample' => array(
			'name'			=> 'サンプル',		// 表示名
			'required'      => true,			// 必須オプション(true/false)
			'min'           => null,			// 最小値
			'max'           => null,			// 最大値
			'regexp'        => null,			// 文字種指定(正規表現)
			'custom'        => null,			// メソッドによるチェック
			'filter'        => null,			// 入力値変換フィルタオプション
			'form_type'     => FORM_TYPE_TEXT,	// フォーム型
			'type'          => VAR_TYPE_INT,	// 入力値型
		),
		*/
	);
}

/**
 *	{$action_name}アクションの実装
 *
 *	@author		your name
 *	@access		public
 *	@package	{$project_id}
 */
class {$action_class} extends Ethna_ActionClass
{
	/**
	 *	{$action_name}アクションの前処理
	 *
	 *	@access	public
	 *	@return	string		遷移名(正常終了ならnull, 処理終了ならfalse)
	 */
	function prepare()
	{
		return null;
	}

	/**
	 *	{$action_name}アクションの実装
	 *
	 *	@access	public
	 *	@return	string	遷移名
	 */
	function perform()
	{
		return '{$action_name}';
	}
}
?>
