<?php
// vim: foldmethod=marker
/**
 *	Ethna_LogWriter.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_LogWriter
/**
 *	ログ出力基底クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_LogWriter
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	string	ログアイデンティティ文字列 */
	var	$ident;

	/**	@var	int		ログファシリティ */
	var	$facility;

	/**	@var	int		ログオプション */
	var	$option;

	/**	@var	string	ログファイル */
	var	$file;

	/**	@var	bool	バックトレースが取得可能かどうか */
	var	$have_backtrace;

	/**	@var	array	ログレベル名テーブル */
	var	$level_name_table = array(
		LOG_EMERG	=> 'EMERG',
		LOG_ALERT	=> 'ALERT',
		LOG_CRIT	=> 'CRIT',
		LOG_ERR		=> 'ERR',
		LOG_WARNING	=> 'WARNING',
		LOG_NOTICE	=> 'NOTICE',
		LOG_INFO	=> 'INFO',
		LOG_DEBUG	=> 'DEBUG',
	);

	/**#@-*/

	/**
	 *	Ethna_LogWriterクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	string	$log_ident		ログアイデンティティ文字列(プロセス名等)
	 *	@param	int		$log_facility	ログファシリティ
	 *	@param	string	$log_file		ログ出力先ファイル名(LOG_FILEオプションが指定されている場合のみ)
	 *	@param	int		$log_option		ログオプション(LOG_FILE,LOG_FUNCTION...)
	 */
	function Ethna_LogWriter($log_ident, $log_facility, $log_file, $log_option)
	{
		$this->ident = $log_ident;
		$this->facility = $log_facility;
		$this->option = $log_option;
		$this->file = $log_file;
		$this->have_backtrace = function_exists('debug_backtrace');
	}

	/**
	 *	ログ出力を開始する
	 *
	 *	@access	public
	 */
	function begin()
	{
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
		$prefix = strftime('%Y/%m/%d %H:%M:%S ') . $this->ident;
		if ($this->option & LOG_PID) {
			$prefix .= sprintf('[%d]', getmypid());
		}
		$prefix .= sprintf('(%s): ', $this->_getLogLevelName($level));
		if ($this->option & LOG_FUNCTION) {
			$function = $this->_getFunctionName();
			if ($function) {
				$prefix .= sprintf('%s: ', $function);
			}
		}
		printf($prefix . $message . "\n");

		return $prefix . $message;
	}

	/**
	 *	ログ出力を終了する
	 *
	 *	@access	public
	 */
	function end()
	{
	}

	/**
	 *	ログアイデンティティ文字列を取得する
	 *
	 *	@access	public
	 *	@return	string	ログアイデンティティ文字列
	 */
	function getIdent()
	{
		return $this->ident;
	}

	/**
	 *	ログレベルを表示文字列に変換する
	 *
	 *	@access	private
	 *	@param	int		$level	ログレベル(LOG_DEBUG,LOG_NOTICE...)
	 *	@return	string	ログレベル表示文字列(LOG_DEBUG→"DEBUG")
	 */
	function _getLogLevelName($level)
	{
		if (isset($this->level_name_table[$level]) == false) {
			return null;
		}
		return $this->level_name_table[$level];
	}

	/**
	 *	ログ出力元の関数を取得する
	 *
	 *	@access	private
	 *	@return	string	ログ出力元クラス/メソッド名("class.method")
	 */
	function _getFunctionName()
	{
		$skip_method_list = array(
			array('ethna', 'raise*'),
			array('ethna_logger', null),
			array('ethna_logwriter_*', null),
			array('ethna_error', null),
			array('ethna_apperror', null),
			array('ethna_actionerror', null),
			array('ethna_backend', 'log'),
			array(null, 'ethna_error_handler'),
			array(null, 'trigger_error'),
		);

		if ($this->have_backtrace == false) {
			return null;
		}

		$bt = debug_backtrace();
		$i = 0;
		while ($i < count($bt)) {
			if (isset($bt[$i]['class']) == false) {
				$bt[$i]['class'] = null;
			}
			$skip = false;

			// メソッドスキップ処理
			foreach ($skip_method_list as $method) {
				$class = $function = true;
				if ($method[0] != null) {
					if (preg_match('/\*$/', $method[0])) {
						$n = strncasecmp($bt[$i]['class'], $method[0], strlen($method[0])-1);
					} else {
						$n = strcasecmp($bt[$i]['class'], $method[0]);
					}
					$class = $n == 0 ? true : false;
				}
				if ($method[1] != null) {
					if (preg_match('/\*$/', $method[1])) {
						$n = strncasecmp($bt[$i]['function'], $method[1], strlen($method[1])-1);
					} else {
						$n = strcasecmp($bt[$i]['function'], $method[1]);
					}
					$function = $n == 0 ? true : false;
				}
				if ($class && $function) {
					$skip = true;
					break;
				}
			}

			if ($skip) {
				$i++;
			} else {
				break;
			}
		}

		return sprintf("%s.%s", isset($bt[$i]['class']) ? $bt[$i]['class'] : 'global', $bt[$i]['function']);
	}
}
// }}}
?>
