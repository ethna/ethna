<?php
// vim: foldmethod=marker
/**
 *	Ethna_LogWriter_Echo.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_LogWriter_Echo
/**
 *	ログ出力基底クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_LogWriter_Echo extends Ethna_LogWriter
{
	/**#@+
	 *	@access	private
	 */

	/**#@-*/

	/**
	 *	ログを出力する
	 *
	 *	@access	public
	 *	@param	int		$level		ログレベル(LOG_DEBUG, LOG_NOTICE...)
	 *	@param	string	$message	ログメッセージ(+引数)
	 */
	function log($level, $message)
	{
		$c =& Ethna_Controller::getInstance();

		$prefix = $this->ident;
		if ($this->option & LOG_PID) {
			$prefix .= sprintf('[%d]', getmypid());
		}
		$prefix .= sprintf($c->getCLI() ? '(%s): ' : '(<b>%s</b>): ',
			$this->_getLogLevelName($level)
		);
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

		printf($prefix . $message . "%s\n", $c->getCLI() ? "" : "<br />");

		return $prefix . $message;
	}
}
// }}}
?>
