<?php
// vim: foldmethod=marker
/**
 *  Ethna.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/** Ethna depends on PEAR */
include_once('PEAR.php');

if (!defined('PATH_SEPARATOR')) {
    if (OS_WINDOWS) {
        /** include_path separator(Windows) */
        define('PATH_SEPARATOR', ';');
    } else {
        /** include_path separator(Unix) */
        define('PATH_SEPARATOR', ':');
    }
}
if (!defined('DIRECTORY_SEPARATOR')) {
    if (OS_WINDOWS) {
        /** directory separator(Windows) */
        define('DIRECTORY_SEPARATOR', '\\');
    } else {
        /** separator(Unix) */
        define('DIRECTORY_SEPARATOR', '/');
    }
}

/** バージョン定義 */
define('ETHNA_VERSION', '2.3.0-dev');

/** Ethnaベースディレクトリ定義 */
define('ETHNA_BASE', dirname(__FILE__));

include_once(ETHNA_BASE . '/class/Ethna_ActionClass.php');
include_once(ETHNA_BASE . '/class/Ethna_ActionError.php');
include_once(ETHNA_BASE . '/class/Ethna_ActionForm.php');
include_once(ETHNA_BASE . '/class/Ethna_AppManager.php');
include_once(ETHNA_BASE . '/class/Ethna_AppObject.php');
include_once(ETHNA_BASE . '/class/Ethna_AppSQL.php');
include_once(ETHNA_BASE . '/class/Ethna_AppSearchObject.php');
include_once(ETHNA_BASE . '/class/Ethna_Backend.php');
include_once(ETHNA_BASE . '/class/Ethna_CacheManager.php');
include_once(ETHNA_BASE . '/class/Ethna_Config.php');
include_once(ETHNA_BASE . '/class/Ethna_Controller.php');
include_once(ETHNA_BASE . '/class/Ethna_ClassFactory.php');
include_once(ETHNA_BASE . '/class/Ethna_DB.php');
include_once(ETHNA_BASE . '/class/Ethna_Error.php');
include_once(ETHNA_BASE . '/class/Ethna_Filter.php');
include_once(ETHNA_BASE . '/class/Ethna_Handle.php');
include_once(ETHNA_BASE . '/class/Ethna_I18N.php');
include_once(ETHNA_BASE . '/class/Ethna_Logger.php');
include_once(ETHNA_BASE . '/class/Ethna_MailSender.php');
include_once(ETHNA_BASE . '/class/Ethna_Session.php');
include_once(ETHNA_BASE . '/class/Ethna_Generator.php');
include_once(ETHNA_BASE . '/class/Ethna_UrlHandler.php');
include_once(ETHNA_BASE . '/class/Ethna_Util.php');
include_once(ETHNA_BASE . '/class/Ethna_ViewClass.php');
include_once(ETHNA_BASE . '/class/View/Ethna_View_List.php');
include_once(ETHNA_BASE . '/class/Ethna_Plugin.php');
include_once(ETHNA_BASE . '/class/Ethna_Renderer.php');
include_once(ETHNA_BASE . '/class/CLI/Ethna_CLI_ActionClass.php');

if (extension_loaded('soap')) {
    include_once(ETHNA_BASE . '/class/SOAP/Ethna_SOAP_ActionForm.php');
    include_once(ETHNA_BASE . '/class/SOAP/Ethna_SOAP_Gateway.php');
    include_once(ETHNA_BASE . '/class/SOAP/Ethna_SOAP_GatewayGenerator.php');
    include_once(ETHNA_BASE . '/class/SOAP/Ethna_SOAP_Util.php');
    include_once(ETHNA_BASE . '/class/SOAP/Ethna_SOAP_WsdlGenerator.php');
}

/** クライアント言語定義: 英語 */
define('LANG_EN', 'en');

/** クライアント言語定義: 日本語 */
define('LANG_JA', 'ja');


/** ゲートウェイ: WWW */
define('GATEWAY_WWW', 1);

/** ゲートウェイ: CLI */
define('GATEWAY_CLI', 2);

/** ゲートウェイ: XMLRPC */
define('GATEWAY_XMLRPC', 3);

/** ゲートウェイ: SOAP */
define('GATEWAY_SOAP', 4);


/** DB種別定義: R/W */
define('DB_TYPE_RW', 1);

/** DB種別定義: R/O */
define('DB_TYPE_RO', 2);

/** DB種別定義: Misc  */
define('DB_TYPE_MISC', 3);


/** 要素型: 整数 */
define('VAR_TYPE_INT', 1);

/** 要素型: 浮動小数点数 */
define('VAR_TYPE_FLOAT', 2);

/** 要素型: 文字列 */
define('VAR_TYPE_STRING', 3);

/** 要素型: 日付 */
define('VAR_TYPE_DATETIME', 4);

/** 要素型: 真偽値 */
define('VAR_TYPE_BOOLEAN', 5);

/** 要素型: ファイル */
define('VAR_TYPE_FILE', 6);


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

/** フォーム型: file */
define('FORM_TYPE_FILE', 8);

/** フォーム型: button */
define('FORM_TYPE_BUTTON', 9);

/** フォーム型: hidden */
define('FORM_TYPE_HIDDEN', 10);


/** エラーコード: 一般エラー */
define('E_GENERAL', 1);

/** エラーコード: DB接続エラー */
define('E_DB_CONNECT', 2);

/** エラーコード: DB設定なし */
define('E_DB_NODSN', 3);

/** エラーコード: DBクエリエラー */
define('E_DB_QUERY', 4);

/** エラーコード: DBユニークキーエラー */
define('E_DB_DUPENT', 5);

/** エラーコード: DB種別エラー */
define('E_DB_INVALIDTYPE', 6);

/** エラーコード: セッションエラー(有効期限切れ) */
define('E_SESSION_EXPIRE', 16);

/** エラーコード: セッションエラー(IPアドレスチェックエラー) */
define('E_SESSION_IPCHECK', 17);

/** エラーコード: アクション未定義エラー */
define('E_APP_UNDEFINED_ACTION', 32);

/** エラーコード: アクションクラス未定義エラー */
define('E_APP_UNDEFINED_ACTIONCLASS', 33);

/** エラーコード: アプリケーションオブジェクトID重複エラー */
define('E_APP_DUPENT', 34);

/** エラーコード: アプリケーションメソッドが存在しない */
define('E_APP_NOMETHOD', 35);

/** エラーコード: ロックエラー */
define('E_APP_LOCK', 36);

/** エラーコード: 読み込みエラー */
define('E_APP_READ', 37);

/** エラーコード: 書き込みエラー */
define('E_APP_WRITE', 38);

/** エラーコード: CSV分割エラー(行継続) */
define('E_UTIL_CSV_CONTINUE', 64);

/** エラーコード: フォーム値型エラー(スカラー引数に配列指定) */
define('E_FORM_WRONGTYPE_SCALAR', 128);

/** エラーコード: フォーム値型エラー(配列引数にスカラー指定) */
define('E_FORM_WRONGTYPE_ARRAY', 129);

/** エラーコード: フォーム値型エラー(整数型) */
define('E_FORM_WRONGTYPE_INT', 130);

/** エラーコード: フォーム値型エラー(浮動小数点数型) */
define('E_FORM_WRONGTYPE_FLOAT', 131);

/** エラーコード: フォーム値型エラー(日付型) */
define('E_FORM_WRONGTYPE_DATETIME', 132);

/** エラーコード: フォーム値型エラー(BOOL型) */
define('E_FORM_WRONGTYPE_BOOLEAN', 133);

/** エラーコード: フォーム値型エラー(FILE型) */
define('E_FORM_WRONGTYPE_FILE', 134);

/** エラーコード: フォーム値必須エラー */
define('E_FORM_REQUIRED', 135);

/** エラーコード: フォーム値最小値エラー(整数型) */
define('E_FORM_MIN_INT', 136);

/** エラーコード: フォーム値最小値エラー(浮動小数点数型) */
define('E_FORM_MIN_FLOAT', 137);

/** エラーコード: フォーム値最小値エラー(文字列型) */
define('E_FORM_MIN_STRING', 138);

/** エラーコード: フォーム値最小値エラー(日付型) */
define('E_FORM_MIN_DATETIME', 139);

/** エラーコード: フォーム値最小値エラー(ファイル型) */
define('E_FORM_MIN_FILE', 140);

/** エラーコード: フォーム値最大値エラー(整数型) */
define('E_FORM_MAX_INT', 141);

/** エラーコード: フォーム値最大値エラー(浮動小数点数型) */
define('E_FORM_MAX_FLOAT', 142);

/** エラーコード: フォーム値最大値エラー(文字列型) */
define('E_FORM_MAX_STRING', 143);

/** エラーコード: フォーム値最大値エラー(日付型) */
define('E_FORM_MAX_DATETIME', 144);

/** エラーコード: フォーム値最大値エラー(ファイル型) */
define('E_FORM_MAX_FILE', 145);

/** エラーコード: フォーム値文字種(正規表現)エラー */
define('E_FORM_REGEXP', 146);

/** エラーコード: フォーム値数値(カスタムチェック)エラー */
define('E_FORM_INVALIDVALUE', 147);

/** エラーコード: フォーム値文字種(カスタムチェック)エラー */
define('E_FORM_INVALIDCHAR', 148);

/** エラーコード: 確認用エントリ入力エラー */
define('E_FORM_CONFIRM', 149);

/** エラーコード: キャッシュタイプ不正 */
define('E_CACHE_INVALID_TYPE', 192);

/** エラーコード: キャッシュ値なし */
define('E_CACHE_NO_VALUE', 193);

/** エラーコード: キャッシュ有効期限 */
define('E_CACHE_EXPIRED', 194);

/** エラーコード: キャッシュエラー(その他) */
define('E_CACHE_GENERAL', 195);

/** エラーコード: プラグインが見つからない */
define('E_PLUGIN_NOTFOUND', 196);

/** エラーコード: プラグインエラー(その他) */
define('E_PLUGIN_GENERAL', 197);

if (defined('E_STRICT') == false) {
    /** PHP 5との互換保持定義 */
    define('E_STRICT', 0);
}

/** Ethnaグローバル変数: エラーコールバック関数 */
$GLOBALS['_Ethna_error_callback_list'] = array();

/** Ethnaグローバル変数: エラーメッセージ */
$GLOBALS['_Ethna_error_message_list'] = array();


// {{{ Ethna
/**
 *  Ethnaフレームワーククラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna extends PEAR
{
    /**#@+
     *  @access private
     */

    /**#@-*/

    /**
     *  Ethna_Errorオブジェクトを生成する(エラーレベル:E_USER_ERROR)
     *
     *  @access public
     *  @param  string  $message            エラーメッセージ
     *  @param  int     $code               エラーコード
     *  @static
     */
    function &raiseError($message, $code = E_GENERAL)
    {
        $userinfo = null;
        if (func_num_args() > 2) {
            $userinfo = array_slice(func_get_args(), 2);
            if (count($userinfo) == 1 && is_array($userinfo[0])) {
                $userinfo = $userinfo[0];
            }
        }
        return PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_ERROR, $userinfo, 'Ethna_Error');
    }

    /**
     *  Ethna_Errorオブジェクトを生成する(エラーレベル:E_USER_WARNING)
     *
     *  @access public
     *  @param  string  $message            エラーメッセージ
     *  @param  int     $code               エラーコード
     *  @static
     */
    function &raiseWarning($message, $code = E_GENERAL)
    {
        $userinfo = null;
        if (func_num_args() > 2) {
            $userinfo = array_slice(func_get_args(), 2);
            if (count($userinfo) == 1 && is_array($userinfo[0])) {
                $userinfo = $userinfo[0];
            }
        }
        return PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_WARNING, $userinfo, 'Ethna_Error');
    }

    /**
     *  Ethna_Errorオブジェクトを生成する(エラーレベル:E_USER_NOTICE)
     *
     *  @access public
     *  @param  string  $message            エラーメッセージ
     *  @param  int     $code               エラーコード
     *  @static
     */
    function &raiseNotice($message, $code = E_GENERAL)
    {
        $userinfo = null;
        if (func_num_args() > 2) {
            $userinfo = array_slice(func_get_args(), 2);
            if (count($userinfo) == 1 && is_array($userinfo[0])) {
                $userinfo = $userinfo[0];
            }
        }
        return PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_NOTICE, $userinfo, 'Ethna_Error');
    }

    /**
     *  エラー発生時の(フレームワークとしての)コールバック関数を設定する
     *
     *  @access public
     *  @param  mixed   string:コールバック関数名 array:コールバッククラス(名|オブジェクト)+メソッド名
     *  @static
     */
    function setErrorCallback($callback)
    {
        $GLOBALS['_Ethna_error_callback_list'][] = $callback;
    }

    /**
     *  エラー発生時の(フレームワークとしての)コールバック関数をクリアする
     *
     *  @access public
     *  @static
     */
    function clearErrorCallback()
    {
        $GLOBALS['_Ethna_error_callback_list'] = array();
    }

    /**
     *  エラー発生時の処理を行う(コールバック関数/メソッドを呼び出す)
     *  
     *  @access public
     *  @param  object  Ethna_Error     Ethna_Errorオブジェクト
     *  @static
     */
    function handleError(&$error)
    {
        for ($i = 0; $i < count($GLOBALS['_Ethna_error_callback_list']); $i++) {
            $callback =& $GLOBALS['_Ethna_error_callback_list'][$i];
            if (is_array($callback) == false) {
                call_user_func($callback, $error);
            } else if (is_object($callback[0])) {
                $object =& $callback[0];
                $method = $callback[1];

                // perform some more checks?
                $object->$method($error);
            } else {
                call_user_func($callback, $error);
            }
        }
    }
}
// }}}
?>
