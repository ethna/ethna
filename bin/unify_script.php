<?php
/**
 *	unify_script.php
 *
 *	usage: /path/to/php unify_script.php [root_dir] [filter(regexp)]
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// 引数妥当性チェック
if ($_SERVER['argc'] != 2 && $_SERVER['argc'] != 3) {
	ethna_unify_script_usage();
	exit(1);
}
$root_dir = $_SERVER['argv'][1];
$filter = null;
if ($_SERVER['argc'] > 2) {
	$filter = $_SERVER['argv'][2];
}

print "<?php\n";
ethna_unify_script($root_dir, $filter);
print "?>\n";

/**
 *	ディレクトリ中のスクリプトからコメント、空行を除いて標準出力へ出力する
 */
function ethna_unify_script($dir, $filter)
{
	$dir_list = array();
	$dh = opendir($dir);
	if ($dh == false) {
		return false;
	}
	while (($file = readdir($dh)) !== false) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (is_file("$dir/$file")) {
			ethna_unify_script_strip("$dir/$file", $filter);
		} else if (is_dir("$dir/$file")) {
			$dir_list[] = "$dir/$file";
		}
	}
	closedir($dh);
	foreach ($dir_list as $file) {
		ethna_unify_script($file, $filter);
	}
}

/**
 *	スクリプトからコメント、空行を除いて標準出力へ出力する
 */
function ethna_unify_script_strip($file, $filter)
{
	if ($filter && preg_match($filter, $file) == 0) {
		return;
	}

	$code = "";
	$fp = fopen($file, 'r');
	if ($fp == false) {
		return;
	}
	while (!feof($fp)) {
		$s = fgets($fp, 8192);
		
		// TODO: もう少しましな方法を...
		if (strstr($s, 'include') || strstr($s, 'require')) {
			if ($filter && preg_match($filter, $s)) {
				continue;
			}
		}
		$code .= $s;
	}
	fclose($fp);

	$token_list = token_get_all($code);
	foreach ($token_list as $token) {
		if (is_string($token)) {
			print $token;
		} else {
			switch ($token[0]) {
			case T_COMMENT:
			case T_ML_COMMENT:
			case T_OPEN_TAG:
			case T_CLOSE_TAG:
				break;
			case T_END_HEREDOC:
				print $token[1] . "\n";
				break;
			default:
				print $token[1];
				break;
			}
		}
	}
}

/**
 *	コマンドラインヘルプを表示する
 */
function ethna_unify_script_usage()
{
	printf("usage: /path/to/php unify_script.php [root_dir] [filter]\n\n");
}
?>
