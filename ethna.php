<?php
/**
 *	ethna.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**	Ethna依存ライブラリ: PEAR::DB */
include_once('DB.php');

/** Ethna依存ライブラリ: Smarty */
include_once('Smarty/Smarty.class.php');


/** Ethnaコンポーネント: Action Class */
include_once('ethna/class/action_class.php');

/** Ethnaコンポーネント: Action Error */
include_once('ethna/class/action_error.php');

/** Ethnaコンポーネント: Action Form */
include_once('ethna/class/action_form.php');

/** Ethnaコンポーネント: Applicationオブジェクト */
include_once('ethna/class/app_object.php');

/** Ethnaコンポーネント: 設定情報表示オブジェクト */
include_once('ethna/class/app_info.php');

/** Ethnaコンポーネント: SQLオブジェクト */
include_once('ethna/class/app_sql.php');

/** Ethnaコンポーネント: Backend */
include_once('ethna/class/backend.php');

/** Ethnaコンポーネント: Config */
include_once('ethna/class/config.php');

/** Ethnaコンポーネント: Controller */
include_once('ethna/class/controller.php');

/** Ethnaコンポーネント: DB */
include_once('ethna/class/db.php');

/** Ethnaコンポーネント: i18n */
include_once('ethna/class/i18n.php');

/** Ethnaコンポーネント: Logger */
include_once('ethna/class/log.php');

/** Ethnaコンポーネント: MailSender */
include_once('ethna/class/mail.php');

/** Ethnaコンポーネント: Session */
include_once('ethna/class/session.php');

/** Ethnaコンポーネント: SkeltonGenerator */
include_once('ethna/class/skelton.php');

/** Ethnaコンポーネント: Smartyプラグイン関数 */
include_once('ethna/class/smarty_plugin.php');

/** Ethnaコンポーネント: ユーティリティ */
include_once('ethna/class/util.php');

if (extension_loaded('soap')) {
	/** Ethnaコンポーネント: SOAPゲートウェイ */
	include_once('ethna/class/soap_gateway.php');

	/** Ethnaコンポーネント: SOAPユーティリティ */
	include_once('ethna/class/soap_util.php');

	/** Ethnaコンポーネント: WSDL生成クラス */
	include_once('ethna/class/soap_wsdl.php');
}

/** バージョン定義 */
define('ETHNA_VERSION', '0.1.0');


/** クライアント言語定義: 英語 */
define('LANG_EN', 'en');

/**	クライアント言語定義: 日本語 */
define('LANG_JA', 'ja');


/**	クライアントタイプ: ウェブブラウザ(PC) */
define('CLIENT_TYPE_WWW', 1);

/**	クライアントタイプ: SOAPクライアント */
define('CLIENT_TYPE_SOAP', 2);

/**	クライアントタイプ: Flash Player (with Flash Remoting) */
define('CLIENT_TYPE_AMF', 3);


/**	要素型: 整数 */
define('VAR_TYPE_INT', 1);

/**	要素型: 浮動小数点数 */
define('VAR_TYPE_FLOAT', 1);

/**	要素型: 文字列 */
define('VAR_TYPE_STRING', 2);

/**	要素型: 日付 */
define('VAR_TYPE_DATETIME', 3);

/**	要素型: 真偽値 */
define('VAR_TYPE_BOOLEAN', 4);

/**	要素型: ファイル */
define('VAR_TYPE_FILE', 5);


/** フォーム型: text */
define('FORM_TYPE_TEXT', 1);

/** フォーム型: password */
define('FORM_TYPE_PASSWORD', 2);

/** フォーム型: textarea */
define('FORM_TYPE_TEXTAREA', 3);

/** フォーム型: select */
define('FORM_TYPE_SELECT', 4);

/** フォーム型: radio */
define('FORM_TYPE_RADIO', 5);

/** フォーム型: checkbox */
define('FORM_TYPE_CHECKBOX', 6);

/** フォーム型: button */
define('FORM_TYPE_SUBMIT', 7);

/** フォーム型: button */
define('FORM_TYPE_FILE', 8);


/**	エラーコード: 一般エラー */
define('E_GENERAL', 1);

/**	エラーコード: DB接続エラー */
define('E_DB_CONNECT', 2);

/**	エラーコード: DB設定なし */
define('E_DB_NODSN', 3);

/**	エラーコード: DBクエリエラー */
define('E_DB_QUERY', 4);

/**	エラーコード: DBユニークキーエラー */
define('E_DB_DUPENT', 5);

/**	エラーコード: セッションエラー(有効期限切れ) */
define('E_SESSION_EXPIRE', 16);

/**	エラーコード: セッションエラー(IPアドレスチェックエラー) */
define('E_SESSION_IPCHECK', 17);

/**	エラーコード: アクション未定義エラー */
define('E_APP_UNDEFINED_ACTION', 32);

/**	エラーコード: アクションクラス未定義エラー */
define('E_APP_UNDEFINED_ACTIONCLASS', 33);

/**	エラーコード: アプリケーションオブジェクトID重複エラー */
define('E_APP_DUPENT', 34);

/** エラーコード: アプリケーションメソッドが存在しない */
define('E_APP_NOMETHOD', 35);

/** エラーコード: ロックエラー */
define('E_APP_LOCK', 36);

/** エラーコード: CSV分割エラー(行継続) */
define('E_UTIL_CSV_CONTINUE', 64);

/**	エラーコード: フォーム値型エラー(スカラー引数に配列指定) */
define('E_FORM_WRONGTYPE_SCALAR', 128);

/**	エラーコード: フォーム値型エラー(配列引数にスカラー指定) */
define('E_FORM_WRONGTYPE_ARRAY', 129);

/**	エラーコード: フォーム値型エラー(整数型) */
define('E_FORM_WRONGTYPE_INT', 130);

/**	エラーコード: フォーム値型エラー(浮動小数点数型) */
define('E_FORM_WRONGTYPE_FLOAT', 131);

/**	エラーコード: フォーム値型エラー(日付型) */
define('E_FORM_WRONGTYPE_DATETIME', 132);

/**	エラーコード: フォーム値型エラー(BOOL型) */
define('E_FORM_WRONGTYPE_BOOLEAN', 133);

/**	エラーコード: フォーム値必須エラー */
define('E_FORM_REQUIRED', 134);

/**	エラーコード: フォーム値最小値エラー(整数型) */
define('E_FORM_MIN_INT', 135);

/**	エラーコード: フォーム値最小値エラー(浮動小数点数型) */
define('E_FORM_MIN_FLOAT', 136);

/**	エラーコード: フォーム値最小値エラー(文字列型) */
define('E_FORM_MIN_STRING', 137);

/**	エラーコード: フォーム値最小値エラー(日付型) */
define('E_FORM_MIN_DATETIME', 138);

/**	エラーコード: フォーム値最小値エラー(ファイル型) */
define('E_FORM_MIN_FILE', 139);

/**	エラーコード: フォーム値最大値エラー(整数型) */
define('E_FORM_MAX_INT', 140);

/**	エラーコード: フォーム値最大値エラー(浮動小数点数型) */
define('E_FORM_MAX_FLOAT', 141);

/**	エラーコード: フォーム値最大値エラー(文字列型) */
define('E_FORM_MAX_STRING', 142);

/**	エラーコード: フォーム値最大値エラー(日付型) */
define('E_FORM_MAX_DATETIME', 143);

/**	エラーコード: フォーム値最大値エラー(ファイル型) */
define('E_FORM_MAX_FILE', 144);

/**	エラーコード: フォーム値文字種(正規表現)エラー */
define('E_FORM_REGEXP', 145);

/**	エラーコード: フォーム値数値(カスタムチェック)エラー */
define('E_FORM_INVALIDVALUE', 146);

/**	エラーコード: フォーム値文字種(カスタムチェック)エラー */
define('E_FORM_INVALIDCHAR', 147);

/**	エラーコード: 確認用エントリ入力エラー */
define('E_FORM_CONFIRM', 148);


if (defined('E_STRICT') == false) {
	/**	PHP 5との互換保持定義 */
	define('E_STRICT', 0);
}


/**
 *	Ethnaフレームワーク基底クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna
{
	/**#@+
	 *	@access	private
	 */

	/**#@-*/

	/**
	 *	Ethna_Errorオブジェクトかどうかを判定する(または指定されたエラーコードの
	 *	エラーかどうかを判定する)
	 *
	 *	@access	public
	 *	@param	mixed	メソッド空の戻り値
	 *	@param	int		エラーコード
	 *	@return	bool	true:エラー false:正常終了
	 *	@static
	 */
	function isError($obj, $code = null)
	{
		if (strcasecmp(get_class($obj), 'Ethna_Error') == 0 ||
			is_subclass_of($obj, 'Ethna_Error')) {
			if (is_null($code)) {
				return true;
			} else {
				return $obj->getCode() == $code;
			}
		}
		return false;
	}

	/**
	 *	Ethna_Errorオブジェクトを生成する(エラーレベル:E_USER_ERROR)
	 *
	 *	@access	public
	 *	@param	int		$code				エラーコード
	 *	@param	string	$message			エラーメッセージ(+引数)
	 *	@static
	 */
	function &raiseError($code, $message = null)
	{
		$message_arg_list = array_slice(func_get_args(), 2);
		return new Ethna_Error(E_USER_ERROR, $code, $message, $message_arg_list);
	}

	/**
	 *	Ethna_Errorオブジェクトを生成する(エラーレベル:E_USER_WARNING)
	 *
	 *	@access	public
	 *	@param	int		$code				エラーコード
	 *	@param	string	$message			エラーメッセージ(+引数)
	 *	@static
	 */
	function &raiseWarning($code, $message = null)
	{
		$message_arg_list = array_slice(func_get_args(), 2);
		return new Ethna_Error(E_USER_WARNING, $code, $message, $message_arg_list);
	}

	/**
	 *	Ethna_Errorオブジェクトを生成する(エラーレベル:E_USER_NOTICE)
	 *
	 *	@access	public
	 *	@param	int		$code				エラーコード
	 *	@param	string	$message			エラーメッセージ(+引数)
	 *	@static
	 */
	function &raiseNotice($code, $message = null)
	{
		$message_arg_list = array_slice(func_get_args(), 2);
		return new Ethna_Error(E_USER_NOTICE, $code, $message, $message_arg_list);
	}
}
?>
