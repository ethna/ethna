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
define('VAR_TYPE_INT', 0);

/**	要素型: 浮動小数点数 */
define('VAR_TYPE_FLOAT', 2);

/**	要素型: 文字列 */
define('VAR_TYPE_STRING', 1);

/**	要素型: 日付 */
define('VAR_TYPE_DATETIME', 3);

/**	要素型: 真偽値 */
define('VAR_TYPE_BOOLEAN', 4);

/**	要素型: ファイル */
define('VAR_TYPE_FILE', 5);


/**	エラーコード: DB接続失敗 */
define('E_DB_CONNECT', 1);

/**	エラーコード: DBクエリエラー */
define('E_DB_QUERY', 2);

/**	エラーコード: セッションエラー */
define('E_SESSION_INVALID', 16);

/**	エラーコード: オブジェクトID重複エラー */
define('E_APP_DUPOBJ', 32);

/**	エラーコード: フォーム値文字種エラー */
define('E_FORM_WRONGTYPE', 48);

/**	エラーコード: フォーム値必須エラー */
define('E_FORM_REQUIRED', 49);

/**	エラーコード: フォーム値最小値エラー */
define('E_FORM_MIN', 50);

/**	エラーコード: フォーム値最大値エラー */
define('E_FORM_MAX', 51);

/**	エラーコード: フォーム値不正文字エラー */
define('E_FORM_INVALIDCHAR', 52);

/**	エラーコード: フォーム値不正値エラー */
define('E_FORM_INVALIDVALUE', 53);

if (defined('E_STRICT') == false) {
	/**	PHP 5との互換保持定義 */
	define('E_STRICT', 0);
}
?>
