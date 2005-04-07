<?php
// vim: foldmethod=marker
/**
 *	Ethna_LogWriter_File.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_LogWriter_File
/**
 *	ログ出力クラス(File)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_LogWriter_File extends Ethna_LogWriter
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	int		ログファイルハンドル */
	var	$fp;

	/**#@-*/

	/**
	 *	Ethna_LogWriter_Fileクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	string	$log_ident		ログアイデンティティ文字列(プロセス名等)
	 *	@param	int		$log_facility	ログファシリティ
	 *	@param	string	$log_file		ログ出力先ファイル名(LOG_FILEオプションが指定されている場合のみ)
	 *	@param	int		$log_option		ログオプション(LOG_FILE,LOG_FUNCTION...)
	 */
	function Ethna_LogWriter_File($log_ident, $log_facility, $log_file, $log_option)
	{
		parent::Ethna_LogWriter($log_ident, $log_facility, $log_file, $log_option);
		$this->fp = null;
	}

	/**
	 *	ログ出力を開始する
	 *
	 *	@access	public
	 */
	function begin()
	{
		$this->fp = fopen($this->file, 'a');
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
		if ($this->fp == null) {
			return;
		}

		$prefix = strftime('%Y/%m/%d %H:%M:%S ') . $this->ident;
		if ($this->option & LOG_PID) {
			$prefix .= sprintf('[%d]', getmypid());
		}
		$prefix .= sprintf('(%s): ', $this->_getLogLevelName($level));
		if ($this->option & (LOG_FUNCTION | LOG_POS)) {
			$tmp = "";
			$bt = $this->_getBacktrace();
			if ($bt && ($this->option & LOG_FUNCTION) && $bt['function']) {
				$tmp .= $bt['function'];
			}
			if ($bt && ($this->option & LOG_POS) && $bt['pos']) {
				$tmp .= $tmp ? sprintf('(%s)', $bt['pos']) : $bt['pos'];
			}
			if ($tmp) {
				$prefix .= $tmp . ": ";
			}
		}
		fwrite($this->fp, $prefix . $message . "\n");

		return $prefix . $message;
	}

	/**
	 *	ログ出力を終了する
	 *
	 *	@access	public
	 */
	function end()
	{
		if ($this->fp) {
			fclose($this->fp);
			$this->fp = null;
		}
	}
}
// }}}
?>
