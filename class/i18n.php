<?php
/**
 *	i18n.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	i18n関連の処理を行うクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_I18N
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	bool	gettextフラグ
	 */
	var $have_gettext;

	/**
	 *	@var	string	ロケールディレクトリ
	 */
	var $locale_dir;

	/**
	 *	@var	string	アプリケーションID
	 */
	var $appid;

	/**
	 *	@var	string	システム側エンコーディング
	 */
	var $systemencoding;

	/**
	 *	@var	string	クライアント側エンコーディング
	 */
	var	$clientencoding;

	/**#@-*/

	/**
	 *	Ethna_I18Nクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	string	$locale_dir	ロケールディレクトリ
	 *	@param	string	$appid		アプリケーションID
	 */
	function Ethna_I18N($locale_dir, $appid)
	{
		$this->locale_dir = $locale_dir;
		$this->appid = strtoupper($appid);
		$this->have_gettext = extension_loaded("gettext") ? true : false;

		$this->setLanguage(LANG_JA);
	}

	/**
	 *	ロケールを設定する
	 *
	 *	@access	public
	 *	@param	string	$language		言語定義
	 *	@param	string	$systemencoding	システムエンコーディング名
	 *	@param	string	$clientencoding	クライアントエンコーディング名
	 *	@return	string	言語に対応して設定されたロケール名
	 */
	function setLanguage($language, $systemencoding = null, $clientencoding = null)
	{
		switch ($language) {
		case LANG_EN:
			$locale = "en_US";
			break;
		case LANG_JA:
			$locale = "ja_JP";
			break;
		default:
			$locale = "ja_JP";
			break;
		}
		setlocale(LC_ALL, $locale);
		if ($this->have_gettext) {
			bindtextdomain($this->appid, $this->locale_dir);
			textdomain($this->appid);
		}

		$this->systemencoding = $systemencoding;
		$this->clientencoding = $clientencoding;

		return $locale;
	}

	/**
	 *	メッセージカタログからロケールに適合するメッセージを取得する
	 *
	 *	@access	public
	 *	@param	string	$message	メッセージ
	 *	@return	string	ロケールに適合するメッセージ
	 */
	function get($message)
	{
		if ($this->have_gettext) {
			return gettext($message);
		} else {
			return $message;
		}
	}
}
?>
