<?php
/**
 *	util.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	グローバルユーティリティ関数: スカラー値を要素数1の配列として返す
 *
 *	@param	mixed	$v	配列として扱う値
 *	@return	array	配列に変換された値
 */
function to_array($v)
{
	if (is_array($v)) {
		return $v;
	} else {
		return array($v);
	}
}

/**
 *	グローバルユーティリティ関数: 指定されたフォーム項目にエラーがあるかどうかを返す
 *
 *	@param	string	$name	フォーム項目名
 *	@return	bool	true:エラー有り false:エラー無し
 */
function is_error($name)
{
	$c =& $GLOBALS['controller'];

	$action_error =& $c->getActionError();

	return $action_error->isError($name);
}


/**
 *	ユーティリティクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Util
{
	/**
	 *	POSTのユニークチェックを行う
	 *
	 *	@access	public
	 *	@return	bool	true:2回目以降のPOST false:1回目のPOST
	 */
	function isDuplicatePost()
	{
		$c =& $GLOBALS['controller'];

		// use raw post data
		if (isset($_POST['uniqid'])) {
			$uniqid = $_POST['uniqid'];
		} else if (isset($_GET['uniqid'])) {
			$uniqid = $_GET['uniqid'];
		} else {
			return false;
		}

		// purge old files
		Etuna_Util::_purgeTmp("uniqid_", 60*60*1);

		$filename = sprintf("%s/uniqid_%s_%s", $c->getDirectory('tmp'), $_SERVER['REMOTE_ADDR'], $uniqid);
		$st = @stat($filename);
		if ($st == false) {
			touch($filename);
			return false;
		}
		if ($st[9] + 60*60*1 < time()) {
			// too old
			return false;
		}

		return true;
	}

	/**
	 *	POSTのユニークチェックフラグをクリアする
	 *
	 *	@access	public
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function clearDuplicatePost()
	{
		$c =& $GLOBALS['controller'];

		// use raw post data
		if (isset($_POST['uniqid'])) {
			$uniqid = $_POST['uniqid'];
		} else {
			return false;
		}

		$filename = sprintf("%s/uniqid_%s_%s", $c->getDirectory('tmp'), $_SERVER['REMOTE_ADDR'], $uniqid);
		unlink($filename);

		return true;
	}

	/**
	 *	メールアドレスが正しいかどうかをチェックする
	 *
	 *	@access	public
	 *	@param	string	$mailaddress	チェックするメールアドレス
	 *	@return	bool	true: 正しいメールアドレス false: 不正な形式
	 */
	function isValidMailAddress($mailaddress)
	{
		if (preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,4}$/i', $mailaddress)) {
			return true;
		}
		return false;
	}

	/**
	 *	CSVエスケープ処理を行う
	 *
	 *	@access	public
	 *	@param	string	$csv		エスケープ対象の文字列(CSVの各要素)
	 *	@param	bool	$escape_nl	改行文字(\r/\n)のエスケープフラグ
	 *	@return	string	CSVエスケープされた文字列
	 */
	function escapeCSV($csv, $escape_nl = false)
	{
		if (preg_match('/[,"\r\n]/', $csv)) {
			if ($escape_nl) {
				$csv = preg_replace('/\r/', "\\r", $csv);
				$csv = preg_replace('/\n/', "\\n", $csv);
			}
			$csv = preg_replace('/"/', "\"\"", $csv);
			$csv = "\"$csv\"";
		}

		return $csv;
	}

	/**
	 *	配列の要素を全てHTMLエスケープして返す
	 *
	 *	@access	public
	 *	@param	array	$target		HTMLエスケープ対象となる配列
	 *	@return	array	エスケープされた配列
	 */
	function escapeHtml($target)
	{
		$r = array();
		Ethna_Util::_escapeHtml($target, $r);
		return $r;
	}

	/**
	 *	配列の要素を全てHTMLエスケープして返す
	 *
	 *	@access	public
	 *	@param	mixed	$vars	HTMLエスケープ対象となる配列
	 *	@param	mixed	$retval	HTMLエスケープ対象となる子要素
	 */
	function _escapeHtml(&$vars, &$retval)
	{
		foreach (array_keys($vars) as $name) {
			if (is_array($vars[$name])) {
				$retval[$name] = array();
				Util::_escapeHtml($vars[$name], $retval[$name]);
			} else {
				$retval[$name] = htmlspecialchars($vars[$name]);
			}
		}
	}

	/**
	 *	Google風リンクリストを返す
	 *
	 *	@access	public
	 */
	function getDirectLinkList($total, $offset, $count)
	{
		$direct_link_list = array();

		if ($total == 0) {
			return array();
		}

		// backwards
		$current = $offset - $count;
		while ($current > 0) {
			array_unshift($direct_link_list, $current);
			$current -= $count;
		}
		if ($offset != 0 && $current <= 0) {
			array_unshift($direct_link_list, 0);
		}

		// current
		$backward_count = count($direct_link_list);
		array_push($direct_link_list, $offset);

		// forwards
		$current = $offset + $count;
		for ($i = 0; $i < 10; $i++) {
			if ($current >= $total) {
				break;
			}
			array_push($direct_link_list, $current);
			$current += $count;
		}
		$forward_count = count($direct_link_list) - $backward_count - 1;

		$backward_count -= 4;
		if ($forward_count < 5) {
			$backward_count -= 5 - $forward_count;
		}
		if ($backward_count < 0) {
			$backward_count = 0;
		}

		// add index
		$n = 1;
		$r = array();
		foreach ($direct_link_list as $direct_link) {
			$v = array('offset' => $direct_link, 'index' => $n);
			$r[] = $v;
			$n++;
		}

		return array_splice($r, $backward_count, 10);
	}

	/**
	 *	元号制での年を返す
	 *
	 *	@access	public
	 *	@param	int		$t		unix time
	 *	@return	string	元号(不明な場合はnull)
	 */
	function getEra($t)
	{
		$tm = localtime($t, true);
		$year = $tm['tm_year'] + 1900;

		if ($year >= 1989) {
			return array('平成', $year - 1988);
		} else if ($year >= 1926) {
			return array('昭和', $year - 1925);
		}

		return null;
	}

	/**
	 *	getimagesize()の返すイメージタイプに対応する拡張子を返す
	 *
	 *	@access	public
	 *	@param	int		$type	getimagesize()関数の返すイメージタイプ
	 *	@return	string	$typeに対応する拡張子
	 */
	function getImageExtName($type)
	{
		$ext_list = array(
			1	=> 'gif',
			2	=> 'jpg',
			3	=> 'png',
			4	=> 'swf',
			5	=> 'psd',
			6	=> 'bmp',
			7	=> 'tiff',
			8	=> 'tiff',
			9	=> 'jpc',
			10	=> 'jp2',
			11	=> 'jpx',
			12	=> 'jb2',
			13	=> 'swc',
			14	=> 'iff',
			15	=> 'wbmp',
			16	=> 'xbm',
		);

		return @$ext_list[$type];
	}

	/**
	 *	ランダムなハッシュ値を生成する
	 *
	 *	@access	public
	 *	@param	int		$length	ハッシュ値の長さ(〜64)
	 *	@return	string	ハッシュ値
	 *	@todo	Linux以外の環境対応
	 */
	function getRandom($length = 64)
	{
		$value = "";
		for ($i = 0; $i < 2; $i++) {
			$rx = $tx = 0;
			$fp = fopen('/proc/net/dev', 'r');
			if ($fp != null) {
				$header = true;
				while (feof($fp) === false) {
					$s = fgets($fp, 4096);
					if ($header) {
						$header = false;
						continue;
					}
					$v = preg_split('/[:\s]+/', $s);
					if (is_array($v) && count($v) > 10) {
						$rx += $v[2];
						$tx += $v[10];
					}
				}
			}
			$now = strftime('%Y%m%d %T');
			$time = gettimeofday();
			$v = $now . $time['usec'] . $rx . $tx . rand(0, time());
			$value .= md5($v);
		}

		if ($length < 64) {
			$value = substr($value, 0, $length);
		}
		return $value;
	}

	/**
	 *	1次元配列をm x nに再構成する
	 *
	 *	@access	public
	 *	@param	array	$array	処理対象の1次元配列
	 *	@param	int		$m		軸の要素数
	 *	@param	int		$order	$mをX軸と見做すかY軸と見做すか(0:X軸 1:Y軸)
	 *	@return	array	m x nに再構成された配列
	 */
	function get2dArray($array, $m, $order)
	{
		$r = array();
		
		$n = intval(count($array) / $m);
		if ((count($array) % $m) > 0) {
			$n++;
		}
		for ($i = 0; $i < $n; $i++) {
			$elts = array();
			for ($j = 0; $j < $m; $j++) {
				if ($order == 0) {
					/* 横並び */
					$key = $i*$m+$j;
				} else {
					/* 縦並び */
					$key = $i+$n*$j;
				}
				if (array_key_exists($key, $array) == false) {
					$array[$key] = null;
				}
				$elts[] = $array[$key];
			}
			$r[] = $elts;
		}

		return $r;
	}

	/**
	 *	テンポラリディレクトリのファイルを削除する
	 *
	 *	@access	public
	 *	@param	string	$prefix		ファイルのプレフィクス
	 *	@param	int		$timeout	削除対象閾値(秒−60*60*1なら1時間)
	 */
	function purgeTmp($prefix, $timeout)
	{
		$c =& $GLOBALS['controller'];

		$dh = opendir($c->getDirectory('tmp'));
		if ($dh) {
			while (($file = readdir($dh)) !== false) {
				if (strncmp($file, $prefix, strlen($prefix)) == 0) {
					$f = $c->getDirectory('tmp') . "/" . $file;
					$st = @stat($f);
					if ($st[9] + $timeout < time()) {
						unlink($f);
					}
				}
			}
			closedir($dh);
		}
	}

	/**
	 *	ファイルをロックする
	 *
	 *	@access	public
	 *	@param	string	$file		ロックするファイル名
	 *	@param	int		$mode		ロックモード(LOCK_SH, LOCK_EX)
	 *	@param	int		$timeout	ロック待ちタイムアウト(秒−0なら無限)
	 *	@return	int		ロックハンドル(falseならエラー)
	 */
	function lockFile($file, $mode, $timeout = 0)
	{
		$lh = @fopen($file, 'r');
		if ($lh == null) {
			return false;
		}

		$lock_mode = $mode == 'r' ? LOCK_SH : LOCK_EX;

		for ($i = 0; $i < $timeout || $timeout == 0; $i++) {
			$r = flock($lh, $lock_mode | LOCK_NB);
			if ($r == true) {
				break;
			}
			sleep(1);
		}
		if ($timeout > 0 && $i == $timeout) {
			// timed out
			return false;
		}
		@unlink($lock);

		return $lh;
	}

	/**
	 *	ファイルのロックを解除する
	 *
	 *	@access	public
	 *	@param	int		$lh		ロックハンドル
	 */
	function unlockFile($lh)
	{
		fclose($lh);
	}

	/**
	 *	バックトレースをフォーマットして返す
	 *
	 *	@access	public
	 *	@param	array	$bt		debug_backtrace()関数で取得したバックトレース
	 *	@return	string	文字列にフォーマットされたバックトレース
	 */
	function formatBacktrace($bt) 
	{
		$r = "";
		$i = 0;
		foreach ($bt as $elt) {
			$r .= sprintf("[%02d] %s:%d:%s.%s\n", $i, $elt['file'], $elt['line'], isset($elt['class']) ? $elt['class'] : 'global', $elt['function']);
			$i++;

			if (isset($elt['args']) == false || is_array($elt['args']) == false) {
				continue;
			}

			/* 引数のダンプ */
			foreach ($elt['args'] as $arg) {
				$r .= Ethna_Util::_formatBacktrace($arg);
			}
		}

		return $r;
	}

	/**
	 *	バックトレース引数をフォーマットして返す
	 *
	 *	@access	private
	 *	@param	string	$arg	バックトレースの引数
	 *	@param	int		$level	バックトレースのネストレベル
	 *	@return	string	文字列にフォーマットされたバックトレース
	 */
	function _formatBacktrace($arg, $level = 0)
	{
		$pad = str_repeat("  ", $level);
		if (is_array($arg)) {
			$r = sprintf("     %s[array] => (\n", $pad);
			if ($level+1 > 4) {
				$r .= sprintf("     %s  *too deep*\n", $pad);
			} else {
				foreach ($arg as $elt) {
					$r .= Ethna_Util::_formatBacktrace($elt, $level+1);
				}
			}
			$r .= sprintf("     %s)\n", $pad);
		} else if (is_object($arg)) {
			$r = sprintf("     %s[object]%s\n", $pad, get_class($arg));
		} else {
			$r = sprintf("     %s[%s]%s\n", $pad, gettype($arg), $arg);
		}

		return $r;
	}
}
?>
