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

/** テストケースがあるディレクトリ */
$test_dir = ETHNA_INSTALL_BASE . '/test';

/** include_pathの設定(このtest runnerがあるディレクトリを追加) */
ini_set('include_path', realpath(ETHNA_INSTALL_BASE . '/class') . PATH_SEPARATOR . ini_get('include_path'));

/** Ethna関連クラスのインクルード */
require_once ETHNA_INSTALL_BASE . '/Ethna.php';

/** SimpleTestのインクルード */
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once $test_dir . '/TextDetailReporter.php';
require_once $test_dir . '/Ethna_UnitTestBase.php';

$test = &new GroupTest('Ethna All tests');

// テストケースのファイルリストを取得
require_once ETHNA_INSTALL_BASE . '/class/Ethna_Getopt.php';
$opt = new Ethna_Getopt();
$args = $opt->readPHPArgv();
list($args, $opts) = $opt->getopt($args, '', array());
array_shift($opts);
if (count($opts) > 0) {
    $file_list = $opts;
} else {
    $file_list = getFileList($test_dir);
}

// テストケースを登録
foreach ($file_list as $file) {
    $test->addTestFile($file);
}

// 結果をコマンドラインに出力
$test->run(new TextDetailReporter());

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
            if (preg_match('/^(Ethna_)(.*)(_Test.php)$/',$file_path,$matches)) {
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

?>
