<?php
/**
 *  Ethna_Plugin_Cachemanager_Localfile_Test.php
 */

/**
 *  Ethna_Plugin_Cachemanager_Localfileクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Cachemanager_Localfile_Test extends Ethna_UnitTestBase
{
    function rm($path){
        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $this->rm("$path/$file");
                    }
                }
                closedir($handle);
            }
            if (!rmdir($path)) {
                printf("fail rmdir[$path]\n");
            }
        } else {
            if (!unlink($path)) {
                printf("fail unlink[$path]\n");
            }
        }
    }

    function testCachemanagerLocalfile()
    {
        $ctl =& Ethna_Controller::getInstance();
        $plugin =& $ctl->getPlugin();
        $cm = $plugin->getPlugin('Cachemanager', 'Localfile');

        // 文字列のキャッシュ
        $string_key = 'string_key';
        $string_value = "cache\ncontent";
        $cm->set($string_key, $string_value, mktime(0, 0, 0, 7, 1, 2000));
        $cache_string = $cm->get($string_key);
        $this->assertTrue($cm->isCached($string_key));
        $this->assertEqual(mktime(0, 0, 0, 7, 1, 2000), $cm->getLastModified($string_key));
        $this->assertTrue($string_value, $cache_string);

        // 整数のキャッシュ + namespace
        $int_key = 'int_key';
        $int_value = 777;
        $namespace = 'test';
        $cm->set($int_key, $int_value, mktime(0, 0, 0, 7, 1, 2000), $namespace);
        $cache_int = $cm->get($int_key, mktime(0, 0, 0, 7, 1, 2000), $namespace);
        $this->assertTrue($cm->isCached($int_key, mktime(0, 0, 0, 7, 1, 2000), $namespace));
        $this->assertTrue($int_value, $cache_int);

        // オブジェクトのキャッシュ
        $object_key = 'object_key';
        $object_value =& $cm;
        $cm->set($object_key, $object_value);
        $this->assertTrue($cm->isCached($object_key));
        // キャッシュされたインスタンス
        $cache_object = $cm->get($object_key);
        $this->assertTrue($string_value, $cache_object->get($string_key));

        // キャッシュのクリアをテスト
        $cm->clear($object_key);
        $this->assertFalse($cm->isCached($object_key));

        // キャッシュされていないのに呼び出そうとした場合
        $nocache_key = 'nocache_key';
        $cm->clear($nocache_key);
        $pear_error = $cm->get($nocache_key);
        $this->assertEqual(E_CACHE_NO_VALUE, $pear_error->getCode());
        $this->assertEqual('fopen failed', $pear_error->getMessage());

        // ファイルに読み込み権限がない場合
        // PHP 4, PHP5 ともに、Windows上ではmodeをどのように設定しても
        // read権限が残るためskip.(PHP 4.4.8, 5.2.6 on Windows XP)
        if (!OS_WINDOWS) {
            Ethna_Util::chmod($cm->_getCacheFile(null, $string_key), 0222);
            $pear_error = $cm->get($string_key);
            $this->assertEqual(E_CACHE_NO_VALUE, $pear_error->getCode());
            $this->assertEqual('fopen failed', $pear_error->getMessage());
            Ethna_Util::chmod($cm->_getCacheFile(null, $string_key), 0666);
        }

        // lifetime切れの場合
        $pear_error = $cm->get($string_key, 1);
        $this->assertEqual(E_CACHE_EXPIRED, $pear_error->getCode());
        $this->assertEqual('fopen failed', $pear_error->getMessage());

        // ディレクトリ名と同じファイルがあってディレクトリが作成できない場合
        $tmp_key = 'tmpkey';
        $tmp_dirname = $cm->_getCacheDir(null, $tmp_key);
        Ethna_Util::mkdir(dirname($tmp_dirname), 0777);
        $tmp_file = fopen($tmp_dirname, 'w');
        fclose($tmp_file);
        $pear_error = $cm->set($tmp_key, $string_value);
        $this->assertEqual(E_USER_WARNING, $pear_error->getCode());
        $this->assertEqual("mkdir($tmp_dirname) failed", $pear_error->getMessage());

        $this->rm($cm->backend->getTmpdir());

    }
}
?>
