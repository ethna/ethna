<?php
// vim: foldmethod=marker
/**
 *	Ethna_Logger.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	拡張ログプロパティ:	ファイル出力
 */
define('LOG_FILE', 1 << 16);

/**
 *	拡張ログプロパティ:	標準出力
 */
define('LOG_ECHO', 1 << 17);

/**
 *	拡張ログプロパティ:	関数名表示
 */
define('LOG_FUNCTION', 1 << 18);

/**
 *	拡張ログプロパティ:	ファイル名+行番号表示
 */
define('LOG_POS', 1 << 19);


// {{{ ethna_error_handler
/**
 *	エラーコールバック関数
 *
 *	@param	int		$errno		エラーレベル
 *	@param	string	$errstr		エラーメッセージ
 *	@param	string	$errfile	エラー発生箇所のファイル名
 *	@param	string	$errline	エラー発生箇所の行番号
 */
function ethna_error_handler($errno, $errstr, $errfile, $errline)
{
	if ($errno == E_STRICT) {
		// E_STRICTは表示しない
		return E_STRICT;
	}

	list($level, $name) = Ethna_Logger::errorLevelToLogLevel($errno);
	switch ($errno) {
	case E_ERROR:
	case E_CORE_ERROR:
	case E_COMPILE_ERROR:
	case E_USER_ERROR:
		$php_errno = 'Fatal error'; break;
	case E_WARNING:
	case E_CORE_WARNING:
	case E_COMPILE_WARNING:
	case E_USER_WARNING:
		$php_errno = 'Warning'; break;
	case E_PARSE:
		$php_errno = 'Parse error'; break;
	case E_NOTICE:
	case E_USER_NOTICE:
		$php_errno = 'Notice'; break;
	default:
		$php_errno = 'Unknown error'; break;
	}

	$php_errstr = sprintf('PHP %s: %s in %s on line %d', $php_errno, $errstr, $errfile, $errline);
	if (ini_get('log_errors') && (error_reporting() & $errno)) {
		$locale = setlocale(LC_TIME, 0);
		setlocale(LC_TIME, 'C');
		error_log($php_errstr, 0);
		setlocale(LC_TIME, $locale);
	}

	$c =& Ethna_Controller::getInstance();
	$logger =& $c->getLogger();
	$logger->log($level, sprintf("[PHP] %s: %s in %s on line %d", $name, $errstr, $errfile, $errline));

	$facility = $logger->getLogFacility();
	if (($facility != LOG_ECHO && is_null($facility) == false) && ini_get('display_errors') && (error_reporting() & $errno)) {
		if ($c->getCLI()) {
			$format = "%s: %s in %s on line %d\n";
		} else {
			$format = "<b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br />\n";
		}
		printf($format, $php_errno, $errstr, $errfile, $errline);
	}
}
// }}}

// {{{ Ethna_Logger
/**
 *	ログ管理クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Logger extends Ethna_AppManager
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	array	ログファシリティ一覧 */
	var $log_facility_list = array(
		'auth' => array('name' => 'LOG_AUTH'),
		'authpriv' => array('name' => 'LOG_AUTHPRIV'),
		'cron' => array('name' => 'LOG_CRON'),
		'daemon' => array('name' => 'LOG_DAEMON'),
		'kern' => array('name' => 'LOG_KERN'),
		'local0' => array('name' => 'LOG_LOCAL0'),
		'local1' => array('name' => 'LOG_LOCAL1'),
		'local2' => array('name' => 'LOG_LOCAL2'),
		'local3' => array('name' => 'LOG_LOCAL3'),
		'local4' => array('name' => 'LOG_LOCAL4'),
		'local5' => array('name' => 'LOG_LOCAL5'),
		'local6' => array('name' => 'LOG_LOCAL6'),
		'local7' => array('name' => 'LOG_LOCAL7'),
		'lpr' => array('name' => 'LOG_LPR'),
		'mail' => array('name' => 'LOG_MAIL'),
		'news' => array('name' => 'LOG_NEWS'),
		'syslog' => array('name' => 'LOG_SYSLOG'),
		'user' => array('name' => 'LOG_USER'),
		'uucp' => array('name' => 'LOG_UUCP'),
		'file' => array('name' => 'LOG_FILE'),
		'echo' => array('name' => 'LOG_ECHO'),
	);

	/**	@var	array	ログレベル一覧 */
	var $log_level_list = array(
		'emerg' => array('name' => 'LOG_EMERG'),
		'alert' => array('name' => 'LOG_ALERT'),
		'crit' => array('name' => 'LOG_CRIT'),
		'err' => array('name' => 'LOG_ERR'),
		'warning' => array('name' => 'LOG_WARNING'),
		'notice' => array('name' => 'LOG_NOTICE'),
		'info' => array('name' => 'LOG_INFO'),
		'debug' => array('name' => 'LOG_DEBUG'),
	);

	/**	@var	array	ログオプション一覧 */
	var $log_option_list = array(
		'pid' => array('name' => 'PID表示', 'value' => LOG_PID),
		'function' => array('name' => '関数名表示', 'value' => LOG_FUNCTION),
		'pos' => array('name' => 'ファイル名表示', 'value' => LOG_POS),
	);

	/**	@var	array	ログレベルテーブル */
	var $level_table = array(
		LOG_EMERG	=> 7,
		LOG_ALERT	=> 6,
		LOG_CRIT	=> 5,
		LOG_ERR		=> 4,
		LOG_WARNING	=> 3,
		LOG_NOTICE	=> 2,
		LOG_INFO	=> 1,
		LOG_DEBUG	=> 0,
	);

	/**	@var	object	Ethna_Controller	controllerオブジェクト */
	var	$controller;

	/**	@var	int		ログレベル */
	var $level;

	/**	@var	int		ログファシリティ */
	var $facility;

	/**	@var	int		アラートレベル */
	var $alert_level;

	/**	@var	string	アラートメールアドレス */
	var $alert_mailaddress;

	/**	@var	string	メッセージフィルタ(出力) */
	var $message_filter_do;

	/**	@var	string	メッセージフィルタ(無視) */
	var $message_filter_ignore;

	/**	@var	object	Ethna_LogWriter	ログ出力オブジェクト */
	var	$writer;

	/**#@-*/
	
	/**
	 *	Ethna_Loggerクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	$controller	controllerオブジェクト
	 */
	function Ethna_Logger(&$controller)
	{
		$this->controller =& $controller;
		$config =& $controller->getConfig();
		
		// ログ設定の取得
		$this->level = $this->_parseLogLevel($config->get('log_level'));
		if (is_null($this->level)) {
			// 未設定ならLOG_WARNING
			$this->level = LOG_WARNING;
		}
		$this->facility = $this->_parseLogFacility($config->get('log_facility'));
		$option = $this->_parseLogOption($config->get('log_option'));
		$this->alert_level = $this->_parseLogLevel($config->get('log_alert_level'));
		$this->alert_mailaddress = preg_split('/\s*,\s*/', $config->get('log_alert_mailaddress'));
		$this->message_filter_do = $config->get('log_filter_do');
		$this->message_filter_ignore = $config->get('log_filter_ignore');

		// LogWriterクラスの生成
		$file = $this->_getLogFile();
		$this->writer =& $this->_getLogWriter($file, $option);

		set_error_handler("ethna_error_handler");
	}

	/**
	 *	ログファシリティを取得する
	 *
	 *	@access	public
	 *	@return	int		ログファシリティ
	 */
	function getLogFacility()
	{
		return $this->facility;
	}

	/**
	 *	PHPエラーレベルをログレベルに変換する
	 *
	 *	@access	public
	 *	@param	int		$errno	PHPエラーレベル
	 *	@return	array	ログレベル(LOG_NOTICE,...), エラーレベル表示名("E_NOTICE"...)
	 *	@static
	 */
	function errorLevelToLogLevel($errno)
	{
		switch ($errno) {
		case E_ERROR:			$code = "E_ERROR"; $level = LOG_ERR; break;
		case E_WARNING:			$code = "E_WARNING"; $level = LOG_WARNING; break;
		case E_PARSE:			$code = "E_PARSE"; $level = LOG_CRIT; break;
		case E_NOTICE:			$code = "E_NOTICE"; $level = LOG_NOTICE; break;
		case E_USER_ERROR:		$code = "E_USER_ERROR"; $level = LOG_ERR; break;
		case E_USER_WARNING:	$code = "E_USER_WARNING"; $level = LOG_WARNING; break;
		case E_USER_NOTICE:		$code = "E_USER_NOTICE"; $level = LOG_NOTICE; break;
		case E_STRICT:			$code = "E_STRING"; $level = LOG_NOTICE; return;
		default:				$code = "E_UNKNOWN"; $level = LOG_DEBUG; break;
		}
		return array($level, $code);
	}

	/**
	 *	ログ出力を開始する
	 *
	 *	@access	public
	 */
	function begin()
	{
		$this->writer->begin();
	}

	/**
	 *	ログを出力する
	 *
	 *	@access	public
	 *	@param	int		$level		ログレベル(LOG_DEBUG, LOG_NOTICE...)
	 *	@param	string	$message	ログメッセージ(+引数)
	 */
	function log($level, $message)
	{
		// ログメッセージフィルタ(レベルフィルタに優先する)
		$r = $this->_evalMessageMask($message);
		if ($r === false) {
			return;
		}

		// ログレベルフィルタ
		if ($r !== true && $this->_evalLevelMask($this->level, $level)) {
			return;
		}

		// ログ出力
		$args = func_get_args();
		if (count($args) > 2) {
			array_splice($args, 0, 2);
			$message = vsprintf($message, $args);
		}
		$output = $this->writer->log($level, $message);

		// アラート処理
		if ($this->_evalLevelMask($this->alert_level, $level) == false) {
			if (count($this->alert_mailaddress) > 0) {
				$this->_alert($output);
			}
		}
	}

	/**
	 *	ログ出力を終了する
	 *
	 *	@access	public
	 */
	function end()
	{
		$this->writer->end();
	}

	/**
	 *	ログファイルの書き出し先を取得する(ログファシリティに
	 *	LOG_FILEが指定されている場合のみ有効)
	 *
	 *	ログファイルの書き出し先を変更したい場合はこのメソッドを
	 *	オーバーライドします
	 *
	 *	@access	protected
	 *	@return	string	ログファイルの書き出し先
	 */
	function _getLogFile()
	{
		return sprintf('%s/%s.log',
			$this->controller->getDirectory('log'),
			strtolower($this->controller->getAppid())
		);
	}

	/**
	 *	LogWriterオブジェクトを取得する
	 *
	 *	アプリケーション固有のLogWriterを利用したい場合はこのメソッドを
	 *	オーバーライドします
	 *
	 *	@access	protected
	 *	@param	string	$file		ログファイル
	 *	@param	array	$option		ログオプション
	 *	@return	object	LogWriter	LogWriterオブジェクト
	 */
	function &_getLogWriter($file, $option)
	{
		if ($this->facility == LOG_FILE) {
			$writer_class = "Ethna_LogWriter_File";
		} else if ($this->facility == LOG_ECHO || is_null($this->facility)) {
			$writer_class = "Ethna_LogWriter";
		} else {
			$writer_class = "Ethna_LogWriter_Syslog";
		}
		return new $writer_class($this->controller->getAppId(), $this->facility, $file, $option);
	}

	/**
	 *	ログオプション(設定ファイル値)を解析する
	 *
	 *	@access	private
	 *	@param	string	$string	ログオプション(設定ファイル値)
	 *	@return	array	解析された設定ファイル値(アラート通知メールアドレス, アラート対象ログレベル, ログオプション)
	 */
	function _parseLogOption($string)
	{
		$option = null;
		$elts = explode(',', $string);
		foreach ($elts as $elt) {
			if ($elt == 'pid') {
				$option |= LOG_PID;
			} else if ($elt == 'function') {
				$option |= LOG_FUNCTION;
			} else if ($elt == 'pos') {
				$option |= LOG_POS;
			}
		}

		return $option;
	}

	/**
	 *	アラートメールを送信する
	 *
	 *	@access	protected
	 *	@param	string	$message	ログメッセージ
	 *	@return	int		0:正常終了
	 */
	function _alert($message)
	{
		restore_error_handler();

		// ヘッダ
		$header = "Mime-Version: 1.0\n";
		$header .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
		$header .= "X-Alert: " . $this->writer->getIdent();
		$subject = sprintf("[%s] alert (%s%s)\n", $this->writer->getIdent(), substr($message, 0, 12), strlen($message) > 12 ? "..." : "");
		
		// 本文
		$mail = sprintf("--- [log message] ---\n%s\n\n", $message);
		if (function_exists("debug_backtrace")) {
			$bt = debug_backtrace();
			$mail .= sprintf("--- [backtrace] ---\n%s\n", Ethna_Util::FormatBacktrace($bt));
		}

		foreach ($this->alert_mailaddress as $mailaddress) {
			mail($mailaddress, $subject, mb_convert_encoding($mail, "ISO-2022-JP"), $header);
		}

		set_error_handler("ethna_error_handler");

		return 0;
	}

	/**
	 *	ログメッセージのマスクチェックを行う
	 *
	 *	@access	private
	 *	@param	string	$message	ログメッセージ
	 *	@return	mixed	true:強制出力 false:強制無視 null:スキップ
	 */
	function _evalMessageMask($message)
	{
		$regexp_do = sprintf("/%s/", $this->message_filter_do);
		$regexp_ignore = sprintf("/%s/", $this->message_filter_ignore);

		if ($this->message_filter_do && preg_match($regexp_do, $message)) {
			return true;
		}
		if ($this->message_filter_ignore && preg_match($regexp_ignore, $message)) {
			return false;
		}
		return null;
	}

	/**
	 *	ログレベルのマスクチェックを行う
	 *
	 *	@access	private
	 *	@param	int		$src	ログレベルマスク
	 *	@param	int		$dst	ログレベル
	 *	@return	bool	true:閾値以下 false:閾値以上
	 */
	function _evalLevelMask($src, $dst)
	{
		// 知らないレベルなら出力しない
		if (isset($this->level_table[$src]) == false || isset($this->level_table[$dst]) == false) {
			return true;
		}

		if ($this->level_table[$dst] >= $this->level_table[$src]) {
			return false;
		}

		return true;
	}

	/**
	 *	ログファシリティ(設定ファイル値)を解析する
	 *
	 *	@access	private
	 *	@param	string	$facility	ログファシリティ(設定ファイル値)
	 *	@return	int		ログファシリティ(LOG_LOCAL0, LOG_FILE...)
	 */
	function _parseLogFacility($facility)
	{
		$facility_map_table = array(
			'auth'		=> LOG_AUTH,
			'authpriv'	=> LOG_AUTHPRIV,
			'cron'		=> LOG_CRON,
			'daemon'	=> LOG_DAEMON,
			'kern'		=> LOG_KERN,
			'local0'	=> LOG_LOCAL0,
			'local1'	=> LOG_LOCAL1,
			'local2'	=> LOG_LOCAL2,
			'local3'	=> LOG_LOCAL3,
			'local4'	=> LOG_LOCAL4,
			'local5'	=> LOG_LOCAL5,
			'local6'	=> LOG_LOCAL6,
			'local7'	=> LOG_LOCAL7,
			'lpr'		=> LOG_LPR,
			'mail'		=> LOG_MAIL,
			'news'		=> LOG_NEWS,
			'syslog'	=> LOG_SYSLOG,
			'user'		=> LOG_USER,
			'uucp'		=> LOG_UUCP,
			'file'		=> LOG_FILE,
		);
		if (isset($facility_map_table[strtolower($facility)]) == false) {
			return null;
		}
		return $facility_map_table[strtolower($facility)];
	}

	/**
	 *	ログレベル(設定ファイル値)を解析する
	 *
	 *	@access	private
	 *	@param	string	$level	ログレベル(設定ファイル値)
	 *	@return	int		ログレベル(LOG_DEBUG, LOG_NOTICE...)
	 */
	function _parseLogLevel($level)
	{
		$level_map_table = array(
			'emerg'		=> LOG_EMERG,
			'alert'		=> LOG_ALERT,
			'crit'		=> LOG_CRIT,
			'err'		=> LOG_ERR,
			'warning'	=> LOG_WARNING,
			'notice'	=> LOG_NOTICE,
			'info'		=> LOG_INFO,
			'debug'		=> LOG_DEBUG,
		);
		if (isset($level_map_table[strtolower($level)]) == false) {
			return null;
		}
		return $level_map_table[strtolower($level)];
	}
}
// }}}
?>
