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

/** アプリケーションベースディレクトリ */
define('BASE', dirname(dirname(__FILE__)));

// include_pathの設定(アプリケーションディレクトリを追加)
$app = BASE . "/app";
$lib = BASE . "/lib";

/** Ethna関連クラスのインクルード */
include_once('Ethna/Ethna.php');

/** SimpleTestのインクルード */
include_once('simpletest/unit_tester.php');
include_once('simpletest/reporter.php');

/** テストケースがあるディレクトリ */
$test_dir = ETHNA_BASE . '/test';

$test = &new GroupTest('Ethna All tests');

// テストケースのファイルリストを取得
$file_list = getFileList($test_dir);

// テストケースを登録
foreach ($file_list as $file) {
	$test->addTestFile($file);
}

// 結果をコマンドラインに出力
$test->run(new TextReporter());

function getFileList($dir_path) {
	$file_list = array();
    if ($dir = opendir($dir_path)) {
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
    }
    return $file_list;
}
?>
