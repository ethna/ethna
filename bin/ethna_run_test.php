<?php
/**
 *  ethna_run_test.php
 *
 *  Ethna Test Runner
 *
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/** Ethnaインストールルートディレクトリ */
define('ETHNA_INSTALL_BASE', dirname(dirname(__FILE__)));

$symlink_filename = null;

/** シンボリックリンクをインストールディレクトリの親に張る */
/** symlink 関数は 5.3.0 以前では Windows 上で動作しない   */
/** が、Cygwinでテストするため問題はない。                 */
if (basename(ETHNA_INSTALL_BASE) != 'Ethna') {
    $symlink_filename = dirname(ETHNA_INSTALL_BASE) . "/Ethna";
    if (!file_exists($symlink_filename)) {
        symlink(ETHNA_INSTALL_BASE, $symlink_filename);
    } else {
        if (!is_link($symlink_filename)
            || realpath($symlink_filename) != ETHNA_INSTALL_BASE) {
            echo "Base dir 'Ethna' exists and it's not ETHNA_INSTALL_BASE.\n";
            exit(1);
        }
        else {
            // もとから存在した symlink は削除しない
            $symlink_filename = null;
        }
    }
}

/** テストケースがあるディレクトリ */
$test_dir = ETHNA_INSTALL_BASE . '/test';

/** include_pathの設定(このtest runnerがあるディレクトリを追加) */
//ini_set('include_path', realpath(ETHNA_INSTALL_BASE . '/class') . PATH_SEPARATOR . ini_get('include_path'));
ini_set('include_path', realpath(dirname(ETHNA_INSTALL_BASE)) . PATH_SEPARATOR . ini_get('include_path'));

/** Ethna関連クラスのインクルード */
require_once 'Ethna/Ethna.php';

// simpletest を使っているため、E_DEPRECATED, E_STRICT は解除
error_reporting(error_reporting() & ~E_DEPRECATED & ~E_STRICT);
if (extension_loaded('xdebug')) {
    ini_set('xdebug.scream', 0);
}

/** SimpleTestのインクルード */
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once $test_dir . '/TextSimpleReporter.php';
require_once $test_dir . '/TextDetailReporter.php';
require_once $test_dir . '/UnitTestBase.php';

$test = new TestSuite('Ethna All tests');

// テストケースのファイルリストを取得
require_once 'Ethna/class/Getopt.php';
$opt = new Ethna_Getopt();
$args = $opt->readPHPArgv();
array_shift($args);
$opt_ret = $opt->getopt($args, "", array('coverage', 'verbose'));
if (Ethna::isError($opt_ret)) {
    echo $opt_ret->getMessage(), PHP_EOL;
    exit(255);
}
list($args, $opts) = $opt_ret;

$coverage = false;
$verbose = false;
foreach ($args as $arg) {
    switch ($arg[0]) {
    case '--coverage':
        $coverage = true;
        break;

    case '--verbose':
        $verbose = true;
        break;
    }
}

if (count($opts) > 0) {
    $file_list = $opts;
} else {
    $file_list = getFileList($test_dir);
}

// テストケースを登録
foreach ($file_list as $file) {
    $test->addFile($file);
}

if ($coverage) {
    // カバレッジ計測開始
    require_once 'PHP/CodeCoverage.php';

    $base = dirname(dirname(__FILE__));

    $filter = PHP_CodeCoverage_Filter::getInstance();
    $filter->addDirectoryToBlacklist($base.'/test');
    $filter->addFileToBlacklist(__FILE__);

    $code_coverage = new PHP_CodeCoverage();
    $code_coverage->start('ethna');
}

// 結果をコマンドラインに出力
if ($verbose) {
    $test->run(new TextDetailReporter());
} else {
    $test->run(new TextSimpleReporter());
}

if ($symlink_filename !== null && is_link($symlink_filename)) {
    unlink($symlink_filename);
}

if ($coverage) {
    // カバレッジ計測終了
    $code_coverage->stop();

    require 'PHP/CodeCoverage/Report/HTML.php';
    $writer = new PHP_CodeCoverage_Report_HTML();
    $writer->process($code_coverage, getcwd().'/coverage');
}


//{{{ getFileList
/**
 * getFileList
 *
 * @param string $dir_path
 */
function getFileList($dir_path)
{
    $file_list = array();

    $dir = opendir($dir_path);

    if ($dir == false) {
        return false;
    }

    while($file_path = readdir($dir)) {

        $full_path = $dir_path . '/'. $file_path;

        if (is_file($full_path)){

            // テストケースのファイルのみ読み込む
            if (preg_match('/^(.*)(_Test.php)$/',$file_path,$matches)) {
                $file_list[] = $full_path;
            }

        // サブディレクトリがある場合は，再帰的に読み込む．
        // "."で始まるディレクトリは読み込まない.
        } else if (is_dir($full_path) && !preg_match('/^\./',$file_path,$matches)) {

            $file_list = array_merge($file_list,getFileList($full_path));
        }
    }

    closedir($dir);
    return $file_list;
}
//}}}

