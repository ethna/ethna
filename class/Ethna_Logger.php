<?php
// vim: foldmethod=marker
/**
 *	Ethna_LogWriter_Syslog.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_LogWriter_Syslog
/**
 *	ログ出力クラス(Syslog)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_LogWriter_Syslog extends Ethna_LogWriter
{
	/**
	 *	ログ出力を開始する
	 *
	 *	@access	public
	 */
	function begin()
	{
		// syslog用オプションのみを指定
		$option = $this->option & (LOG_PID);

		openlog($this->ident, $option, $this->facility);
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
		$prefix = sprintf('%s: ', $this->_getLogLevelName($level));
		if ($this->option & LOG_FUNCTION) {
			$function = $this->_getFunctionName();
			if ($function) {
				$prefix .= sprintf('%s: ', $function);
			}
		}
		syslog($level, $prefix . $message);

		return $prefix . $message;
	}

	/**
	 *	ログ出力を終了する
	 *
	 *	@access	public
	 */
	function end()
	{
		closelog();
	}
}
// }}}
?>
