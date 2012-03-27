<?php
// vim: foldmethod=marker
/**
 *  Util.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ to_array
/**
 *  グローバルユーティリティ関数: スカラー値を要素数1の配列として返す
 *
 *  @param  mixed   $v  配列として扱う値
 *  @return array   配列に変換された値
 */
function to_array($v)
{
    if (is_array($v)) {
        return $v;
    } else {
        return array($v);
    }
}
// }}}

// {{{ is_error
/**
 *  グローバルユーティリティ関数: 指定されたフォーム項目にエラーがあるかどうかを返す
 *
 *  @param  string  $name   フォーム項目名
 *  @return bool    true:エラー有り false:エラー無し
 */
function is_error($name = null)
{
    $c = Ethna_Controller::getInstance();
    $action_error = $c->getActionError();
    if ($name !== null) {
        return $action_error->isError($name);
    } else {
        return $action_error->count() > 0;
    }
}
// }}}

// {{{ file_exists_ex
/**
 *  グローバルユーティリティ関数: include_pathを検索しつつfile_exists()する
 *
 *  @param  string  $path               ファイル名
 *  @param  bool    $use_include_path   include_pathをチェックするかどうか
 *  @return bool    true:有り false:無し
 */
function file_exists_ex($path, $use_include_path = true)
{
    if ($use_include_path == false) {
        return file_exists($path);
    }

    // check if absolute
    if (is_absolute_path($path)) {
        return file_exists($path);
    }

    $include_path_list = explode(PATH_SEPARATOR, get_include_path());
    if (is_array($include_path_list) == false) {
        return file_exists($path);
    }

    foreach ($include_path_list as $include_path) {
        if (file_exists($include_path . DIRECTORY_SEPARATOR . $path)) {
            return true;
        }
    }
    return false;
}
// }}}

// {{{ is_absolute_path
/**
 *  グローバルユーティリティ関数: 絶対パスかどうかを返す
 *
 *  @param  string  $path               ファイル名
 *  @return bool    true:絶対 false:相対
 */
function is_absolute_path($path)
{
    if (ETHNA_OS_WINDOWS) {
        if (preg_match('/^[a-z]:/i', $path) && $path{2} == DIRECTORY_SEPARATOR) {
            return true;
        }
    } else {
        if ($path{0} == DIRECTORY_SEPARATOR) {
            return true;
        }
    }
    return false;
}
// }}}

// {{{ Ethna_Util
/**
 *  ユーティリティクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Util
{
    // {{{ isDuplicatePost
    /**
     *  POSTのユニークチェックを行う
     *
     *  @access public
     *  @return bool    true:2回目以降のPOST false:1回目のPOST
     */
    public static function isDuplicatePost()
    {
        $c = Ethna_Controller::getInstance();

        // use raw post data
        if (isset($_POST['uniqid'])) {
            $uniqid = $_POST['uniqid'];
        } else if (isset($_GET['uniqid'])) {
            $uniqid = $_GET['uniqid'];
        } else {
            return false;
        }

        // purge old files
        Ethna_Util::purgeTmp("uniqid_", 60*60*1);

        $filename = sprintf("%s/uniqid_%s_%s",
                            $c->getDirectory('tmp'),
                            $_SERVER['REMOTE_ADDR'],
                            $uniqid);
        if (file_exists($filename) == false) {
            touch($filename);
            return false;
        }

        $st = stat($filename);
        if ($st[9] + 60*60*1 < time()) {
            // too old
            return false;
        }

        return true;
    }
    // }}}

    // {{{ clearDuplicatePost
    /**
     *  POSTのユニークチェックフラグをクリアする
     *
     *  @acccess public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    public static function clearDuplicatePost()
    {
        $c = Ethna_Controller::getInstance();

        // use raw post data
        if (isset($_POST['uniqid'])) {
            $uniqid = $_POST['uniqid'];
        } else {
            return 0;
        }

        $filename = sprintf("%s/uniqid_%s_%s",
                            $c->getDirectory('tmp'),
                            $_SERVER['REMOTE_ADDR'],
                            $uniqid);
        if (file_exists($filename)) {
            if (unlink($filename) == false) {
                return Ethna::raiseWarning("File Write Error [%s]", E_APP_WRITE, $filename);
            }
        }

        return 0;
    }
    // }}}

    // {{{ isCsrfSafeValid
    /**
     *  CSRFをチェックする
     *
     *  @access public
     *  @return bool    true:正常なPOST false:不正なPOST
     */
    public static function isCsrfSafe()
    {
        $c = Ethna_Controller::getInstance();
        $name = $c->config->get('csrf');

        if (is_null($name)) {
            $name = 'Session';
        }

        $plugin = $c->getPlugin('Csrf', $name);
        $csrf = $plugin->getPlugin('Csrf', $name);
        return $csrf->isValid();
    }
    // }}}

    // {{{ setCsrfID
    /**
     *  CSRFをチェックする
     *
     *  @access public
     *  @return bool    true:成功
     */
    public static function setCsrfID()
    {
        $c = Ethna_Controller::getInstance();
        $name = $c->config->get('csrf');
        
        if (is_null($name)) {
            $name = 'Session';
        }
        
        $plugin = $c->getPlugin('Csrf', $name);
        $csrf = $plugin->getPlugin('Csrf', $name);
        return $csrf->set();
    }
    // }}}

    // {{{ checkMailAddress
    /**
     *  メールアドレスが正しいかどうかをチェックする
     *
     *  @access public
     *  @param  string  $mailaddress    チェックするメールアドレス
     *  @return bool    true: 正しいメールアドレス false: 不正な形式
     */
    public static function checkMailAddress($mailaddress)
    {
        if (preg_match('#^([a-z0-9_]|\-|\.|\+|\/|\?)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$#i',
                       $mailaddress)) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ explodeCSV
    /**
     *  CSV形式の文字列を配列に分割する
     *
     *  @access public
     *  @param  string  $csv        CSV形式の文字列(1行分)
     *  @param  string  $delimiter  フィールドの区切り文字
     *  @return mixed   (array):分割結果 Ethna_Error:エラー(行継続)
     */
    public static function explodeCSV($csv, $delimiter = ",")
    {
        $space_list = '';
        foreach (array(" ", "\t", "\r", "\n") as $c) {
            if ($c != $delimiter) {
                $space_list .= $c;
            }
        }

        $line_end = "";
        if (preg_match("/([$space_list]+)\$/sS", $csv, $match)) {
            $line_end = $match[1];
        }
        $csv = substr($csv, 0, strlen($csv)-strlen($line_end));
        $csv .= ' ';

        $field = '';
        $retval = array();

        $index = 0;
        $csv_len = strlen($csv);
        do {
            // 1. skip leading spaces
            if (preg_match("/^([$space_list]+)/sS", substr($csv, $index), $match)) {
                $index += strlen($match[1]);
            }
            if ($index >= $csv_len) {
                break;
            }

            // 2. read field
            if ($csv{$index} == '"') {
                // 2A. handle quote delimited field
                $index++;
                while ($index < $csv_len) {
                    if ($csv{$index} == '"') {
                        // handle double quote
                        if ($csv{$index+1} == '"') {
                            $field .= $csv{$index};
                            $index += 2;
                        } else {
                            // must be end of string
                            while ($csv{$index} != $delimiter && $index < $csv_len) {
                                $index++;
                            }
                            if ($csv{$index} == $delimiter) {
                                $index++;
                            }
                            break;
                        }
                    } else {
                        // normal character
                        if (preg_match("/^([^\"]*)/S", substr($csv, $index), $match)) {
                            $field .= $match[1];
                            $index += strlen($match[1]);
                        }

                        if ($index == $csv_len) {
                            $field = substr($field, 0, strlen($field)-1);
                            $field .= $line_end;

                            // request one more line
                            return Ethna::raiseNotice('CSV Split Error (line continue)', E_UTIL_CSV_CONTINUE);
                        }
                    }
                }
            } else {
                // 2B. handle non-quoted field
                if (preg_match("/^([^$delimiter]*)/S", substr($csv, $index), $match)) {
                    $field .= $match[1];
                    $index += strlen($match[1]);
                }

                // remove trailing spaces
                $field = preg_replace("/[$space_list]+\$/S", '', $field);
                if ($csv{$index} == $delimiter) {
                    $index++;
                }
            }
            $retval[] = $field;
            $field = '';
        } while ($index < $csv_len);

        return $retval;
    }
    // }}}

    // {{{ escapeCSV
    /**
     *  CSVエスケープ処理を行う
     *
     *  @access public
     *  @param  string  $csv        エスケープ対象の文字列(CSVの各要素)
     *  @param  bool    $escape_nl  改行文字(\r/\n)のエスケープフラグ
     *  @return string  CSVエスケープされた文字列
     */
    public static function escapeCSV($csv, $escape_nl = false)
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
    // }}}

    // {{{ escapeHtml
    /**
     *  配列の要素を全てHTMLエスケープして返す
     *
     *  @access public
     *  @param  array   $target     HTMLエスケープ対象となる配列
     *  @return array   エスケープされた配列
     */
    public static function escapeHtml($target)
    {
        $r = array();
        Ethna_Util::_escapeHtml($target, $r);
        return $r;
    }

    /**
     *  配列の要素を全てHTMLエスケープして返す
     *
     *  @access private
     *  @param  mixed   $vars   HTMLエスケープ対象となる配列
     *  @param  mixed   $retval HTMLエスケープ対象となる子要素
     */
    private static function _escapeHtml(&$vars, &$retval)
    {
        foreach (array_keys($vars) as $name) {
            if (is_array($vars[$name])) {
                $retval[$name] = array();
                Ethna_Util::_escapeHtml($vars[$name], $retval[$name]);
            } else if (!is_object($vars[$name])) {
                $retval[$name] = htmlspecialchars($vars[$name], ENT_QUOTES);
            }
        }
    }
    // }}}

    // {{{ encode_MIME
    /**
     *  文字列をMIMEエンコードする
     *
     *  @access public
     *  @param  string  $string     MIMEエンコードする文字列
     *  @return エンコード済みの文字列
     */
    public static function encode_MIME($string)
    {
        $pos = 0;
        $split = 36;
        $_string = "";
        while ($pos < mb_strlen($string))
        {
            $tmp = mb_strimwidth($string, $pos, $split, "");
            $pos += mb_strlen($tmp);
            $_string .= (($_string)? ' ' : '') . mb_encode_mimeheader($tmp, 'ISO-2022-JP');
        }
        return $_string;
    }
    // }}}

    // {{{ getDirectLinkList
    /**
     *  Google風リンクリストを返す
     *
     *  @access public
     *  @param  int     $total      検索総件数
     *  @param  int     $offset     表示オフセット
     *  @param  int     $count      表示件数
     *  @return array   リンク情報を格納した配列
     */
    public static function getDirectLinkList($total, $offset, $count)
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
    // }}}

    // {{{ getEra
    /**
     *  元号制での年を返す
     *
     *  @access public
     *  @param  int     $t      unix time
     *  @return string  元号(不明な場合はnull)
     */
    public static function getEra($t)
    {
        $tm = localtime($t, true);
        $year = $tm['tm_year'] + 1900;

        if ($year >= 1989) {
            $heisei_str = _et('Heisei');
            return array($heisei_str, $year - 1988);
        } else if ($year >= 1926) {
            $showa_str = _et('Showa');
            return array($showa_str, $year - 1925);
        }

        return null;
    }
    // }}}

    // {{{ getImageExtName
    /**
     *  getimagesize()の返すイメージタイプに対応する拡張子を返す
     *
     *  @access public
     *  @param  int     $type   getimagesize()関数の返すイメージタイプ
     *  @return string  $typeに対応する拡張子
     */
    public static function getImageExtName($type)
    {
        $ext_list = array(
            1   => 'gif',
            2   => 'jpg',
            3   => 'png',
            4   => 'swf',
            5   => 'psd',
            6   => 'bmp',
            7   => 'tiff',
            8   => 'tiff',
            9   => 'jpc',
            10  => 'jp2',
            11  => 'jpx',
            12  => 'jb2',
            13  => 'swc',
            14  => 'iff',
            15  => 'wbmp',
            16  => 'xbm',
        );

        return @$ext_list[$type];
    }
    // }}}

    // {{{ getRandom
    /**
     *  ランダムなハッシュ値を生成する
     *
     *  決して高速ではないので乱用は避けること
     *
     *  @access public
     *  @param  int     $length ハッシュ値の長さ(〜64)
     *  @return string  ハッシュ値
     */
    public static function getRandom($length = 64)
    {
        static $srand = false;

        if ($srand == false) {
            list($usec, $sec) = explode(' ', microtime());
            mt_srand((float) $sec + ((float) $usec * 100000) + getmypid());
            $srand = true;
        }

        // open_basedir がオンで、かつ /proc が許可されているか？
        // open_basedir が空なら許可されていると看做す
        $devfile = '/proc/net/dev';
        $open_basedir_conf = ini_get('open_basedir');
        $devfile_enabled = (empty($open_basedir_conf) 
                        || (preg_match('#:/proc#', $open_basedir_conf) > 0
                        ||  preg_match('#^/proc#', $open_basedir_conf) > 0));

        $value = "";
        for ($i = 0; $i < 2; $i++) {
            // for Linux
            if ($devfile_enabled && file_exists($devfile)) {
                $rx = $tx = 0;
                $fp = fopen($devfile, 'r');
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
                $platform_value = $rx . $tx . mt_rand() . getmypid();
            } else {
                $platform_value = mt_rand() . getmypid();
            }
            $now = strftime('%Y%m%d %T');
            $time = gettimeofday();
            $v = $now . $time['usec'] . $platform_value . mt_rand(0, time());
            $value .= md5($v);
        }

        if ($length < 64) {
            $value = substr($value, 0, $length);
        }
        return $value;
    }
    // }}}

    // {{{ get2dArray
    /**
     *  1次元配列をm x nに再構成する
     *
     *  @access public
     *  @param  array   $array  処理対象の1次元配列
     *  @param  int     $m      軸の要素数
     *  @param  int     $order  $mをX軸と見做すかY軸と見做すか(0:X軸 1:Y軸)
     *  @return array   m x nに再構成された配列
     */
    public static function get2dArray($array, $m, $order)
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
                    // 横並び(横：$m列 縦：無制限)
                    $key = $i*$m+$j;
                } else {
                    // 縦並び(横：無制限 縦：$m行)
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
    // }}}

    // {{{ isAbsolute
    /**
     *  パス名が絶対パスかどうかを返す
     *
     *  port from File in PEAR (for BC)
     *
     *  @access public
     *  @param  string  $path
     *  @return bool    true:絶対パス false:相対パス
     */
    public static function isAbsolute($path)
    {
        if (preg_match("/\.\./", $path)) {
            return false;
        }

        if (DIRECTORY_SEPARATOR == '/'
            && (substr($path, 0, 1) == '/' || substr($path, 0, 1) == '~')) {
            return true;
        } else if (DIRECTORY_SEPARATOR == '\\' && preg_match('/^[a-z]:\\\/i', $path)) {
            return true;
        }

        return false;
    }
    // }}}

    // {{{ isRootDir
    /**
     *  パス名がルートディレクトリかどうかを返す
     *
     *  @access public
     *  @param  string  $path
     *  @static
     */
    public static function isRootDir($path)
    {
        if ($path === DIRECTORY_SEPARATOR) {
            // avoid stat().
            return true;
        }
        if (is_dir($path) === false) {
            return false;
        }
        return $path === basename($path) . DIRECTORY_SEPARATOR;
    }
    // }}}

    // {{{ mkdir
    /**
     *  mkdir -p
     *
     *  @access public
     *  @param  string  $dir    作成するディレクトリ
     *  @param  int     $mode   パーミッション
     *  @return bool    true:成功 false:失敗
     *  @static
     */
    public static function mkdir($dir, $mode)
    {
        if (file_exists($dir)) {
            return is_dir($dir);
        }

        $parent = dirname($dir);
        if ($dir === $parent) {
            return true;
        }

        if (is_dir($parent) === false) {
            if (Ethna_Util::mkdir($parent, $mode) === false) {
                return false;
            }
        }

        return mkdir($dir, $mode) && Ethna_Util::chmod($dir, $mode);
    }
    // }}}

    // {{{ chmod
    /**
     *  ファイルのパーミッションを変更する
     */
    public static function chmod($file, $mode)
    {
        $st = stat($file);
        if (($st[2] & 0777) == $mode) {
            return true;
        }
        return chmod($file, $mode);
    }
    // }}}

    // {{{ purgeDir
    /**
     *  ディレクトリを再帰的に削除する
     *  (途中で失敗しても中断せず、削除できるものはすべて消す)
     *
     *  @access public
     *  @param  string  $file   削除するファイルまたはディレクトリ
     *  @return bool    true:成功 false:失敗
     *  @static
     */
    public static function purgeDir($dir)
    {
        if (file_exists($dir) === false) {
            return false;
        }
        if (is_dir($dir) === false) {
            return unlink($dir);
        }

        $dh = opendir($dir);
        if ($dh === false) {
            return false;
        }
        $ret = true;
        while (($entry = readdir($dh)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $ret = $ret && Ethna_Util::purgeDir("{$dir}/{$entry}");
        }
        closedir($dh);
        if ($ret) {
            return rmdir($dir);
        } else {
            return false;
        }
    }
    // }}}

    // {{{ purgeTmp
    /**
     *  テンポラリディレクトリのファイルを削除する
     *
     *  @access public
     *  @param  string  $prefix     ファイルのプレフィクス
     *  @param  int     $timeout    削除対象閾値(秒−60*60*1なら1時間)
     */
    public static function purgeTmp($prefix, $timeout)
    {
        $c = Ethna_Controller::getInstance();

        $dh = opendir($c->getDirectory('tmp'));
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                } else if (is_dir($c->getDirectory('tmp') . '/' . $file)) {
                    continue;
                } else if (strncmp($file, $prefix, strlen($prefix)) == 0) {
                    $f = $c->getDirectory('tmp') . "/" . $file;
                    $st = stat($f);
                    if ($st[9] + $timeout < time()) {
                        unlink($f);
                    }
                }
            }
            closedir($dh);
        }
    }
    // }}}

    // {{{ lockFile
    /**
     *  ファイルをロックする
     *
     *  @access public
     *  @param  string  $file       ロックするファイル名
     *  @param  int     $mode       ロックモード('r', 'rw')
     *  @param  int     $timeout    ロック待ちタイムアウト(秒−0なら無限)
     *  @return int     ロックハンドル(falseならエラー)
     */
    public static function lockFile($file, $mode, $timeout = 0)
    {
        if (file_exists($file) === false) {
            touch($file);
        }
        $lh = fopen($file, $mode);
        if ($lh == null) {
            return Ethna::raiseError("File Read Error [%s]", E_APP_READ, $file);
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
            return Ethna::raiseError("File lock get error [%s]", E_APP_LOCK, $file);
        }

        return $lh;
    }
    // }}}

    // {{{ unlockFile
    /**
     *  ファイルのロックを解除する
     *
     *  @access public
     *  @param  int     $lh     ロックハンドル
     */
    public static function unlockFile($lh)
    {
        fclose($lh);
    }
    // }}}

    // {{{ formatBacktrace
    /**
     *  バックトレースをフォーマットして返す
     *
     *  @access public
     *  @param  array   $bt     debug_backtrace()関数で取得したバックトレース
     *  @return string  文字列にフォーマットされたバックトレース
     */
    public static function formatBacktrace($bt) 
    {
        $r = "";
        $i = 0;
        foreach ($bt as $elt) {
            $r .= sprintf("[%02d] %s:%d:%s.%s\n", $i,
                          isset($elt['file']) ? $elt['file'] : 'unknown file',
                          isset($elt['line']) ? $elt['line'] : 'unknown line',
                          isset($elt['class']) ? $elt['class'] : 'global',
                          $elt['function']);
            $i++;

            if (isset($elt['args']) == false || is_array($elt['args']) == false) {
                continue;
            }

            // 引数のダンプ
            foreach ($elt['args'] as $arg) {
                $r .= Ethna_Util::_formatBacktrace($arg);
            }
        }

        return $r;
    }

    /**
     *  バックトレース引数をフォーマットして返す
     *
     *  @access private
     *  @param  string  $arg    バックトレースの引数
     *  @param  int     $level  バックトレースのネストレベル
     *  @param  int     $wrap   改行フラグ
     *  @return string  文字列にフォーマットされたバックトレース
     */
    private static function _formatBacktrace($arg, $level = 0, $wrap = true)
    {
        $pad = str_repeat("  ", $level);
        if (is_array($arg)) {
            $r = sprintf("     %s[array] => (\n", $pad);
            if ($level+1 > 4) {
                $r .= sprintf("     %s  *too deep*\n", $pad);
            } else {
                foreach ($arg as $key => $elt) {
                    $r .= Ethna_Util::_formatBacktrace($key, $level, false);
                    $r .= " => \n";
                    $r .= Ethna_Util::_formatBacktrace($elt, $level+1);
                }
            }
            $r .= sprintf("     %s)\n", $pad);
        } else if (is_object($arg)) {
            $r = sprintf("     %s[object]%s%s", $pad, get_class($arg), $wrap ? "\n" : "");
        } else {
            $r = sprintf("     %s[%s]%s%s", $pad, gettype($arg), $arg, $wrap ? "\n" : "");
        }

        return $r;
    }
    // }}}

    /**
     *  Site url from request uri (instead of a config)
     */
    public static function getUrlFromRequestUri()
    {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
          || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
          $protocol = 'https://';
        }
        else {
          $protocol = 'http://';
        }

        $url = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
        return $url;
    }
}
// }}}
