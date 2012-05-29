<?php
/**
 *  Plugin_Cachemanager_Localfile_Test.php
 */

/**
 *  Ethna_Plugin_Cachemanager_Localfileクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Cachemanager_Localfile_Test extends Ethna_UnitTestBase
{
    public $ctl;
    public $cm;
    public $cm_ref;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $this->ctl = $ctl;


        $config = $ctl->getConfig();

        $config->set('plugin',
            array('cachemanager' =>
                array('localfile' => array(
                    'test::int_key' => 'miyazakiaoi',
                )
            )
        ));

        $plugin = $ctl->getPlugin();
        $this->cm = $plugin->getPlugin('Cachemanager', 'Localfile');

    }

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

    function testCachemanagerLocalfileConfig()
    {
        // FIXME: mark as skip (if the tester support)
        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            return ;
        }

        $ref = new ReflectionMethod($this->cm, '_getCacheDir');
        $ref->setAccessible(true);

        $array = array_slice(explode('/', $ref->invoke($this->cm, 'test', 'int_key')), -4, 1);
        $this->assertEqual('miyazakiaoi', array_shift($array));

        $array = array_slice(explode('/', $ref->invoke($this->cm, '', 'string_key')), -4, 1);
        //$this->assertEqual('default', array_shift($array));
    }

    function testCachemanagerLocalfileNamespace()
    {
        $namespace = "miyazakiaoi";
        $this->cm->setNamespace($namespace);
        $this->assertEqual('miyazakiaoi', $this->cm->getNamespace());
        $this->cm->setNamespace("");
    }

    function testCachemanagerLocalfileInt()
    {
        // 整数のキャッシュ + namespace
        $int_key = 'int_key';
        $int_value = 777;
        $namespace = 'test';
        $this->cm->set($int_key, $int_value, mktime(0, 0, 0, 7, 1, 2000), $namespace);
        $cache_int = $this->cm->get($int_key, mktime(0, 0, 0, 7, 1, 2000), $namespace);
        $this->assertTrue($this->cm->isCached($int_key, mktime(0, 0, 0, 7, 1, 2000), $namespace));
        $this->assertTrue($int_value, $cache_int);
    }

    function testCachemanagerLocalfileString()
    {
        // 文字列のキャッシュ
        $string_key = 'string_key';
        $string_value = "cache\ncontent";
        $this->cm->set($string_key, $string_value, mktime(0, 0, 0, 7, 1, 2000));
        $cache_string = $this->cm->get($string_key);
        $this->assertTrue($this->cm->isCached($string_key));
        $this->assertEqual(mktime(0, 0, 0, 7, 1, 2000), $this->cm->getLastModified($string_key));
        $this->assertTrue($string_value, $cache_string);
    }

    function testCachemanagerLocalfileObject()
    {
        // FIXME: mark as skip (if the tester support)
        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            return ;
        }

        $string_key = 'string_key';
        $string_value = "cache\ncontent";

        // オブジェクトのキャッシュ
        $object_key = 'object_key';
        $object_value = $this->cm;

        $this->cm->set($object_key, $object_value);
        $this->assertTrue($this->cm->isCached($object_key));
        // キャッシュされたインスタンス
        $cache_object = $this->cm->get($object_key);
        $this->assertTrue($string_value, $cache_object->get($string_key));

        // キャッシュのクリアをテスト
        $this->cm->clear($object_key);
        $this->assertFalse($this->cm->isCached($object_key));

        // キャッシュされていないのに呼び出そうとした場合
        $nocache_key = 'nocache_key';
        $this->cm->clear($nocache_key);
        $pear_error = $this->cm->get($nocache_key);
        $this->assertEqual(E_CACHE_NO_VALUE, $pear_error->getCode());

        // ファイルに読み込み権限がない場合
        // PHP 4, PHP5 ともに、Windows上ではmodeをどのように設定しても
        // read権限が残るためskip.(PHP 4.4.8, 5.2.6 on Windows XP)
        if (!ETHNA_OS_WINDOWS) {
            $ref = new ReflectionMethod($this->cm, '_getCacheFile');
            $ref->setAccessible(true);
            Ethna_Util::chmod($ref->invoke($this->cm, $this->cm->getNamespace(), $string_key), 0222);
            $pear_error = $this->cm->get($string_key);
            $this->assertEqual(E_CACHE_NO_VALUE, $pear_error->getCode());
            Ethna_Util::chmod($ref->invoke($this->cm, $this->cm->getNamespace(), $string_key), 0666);
        }

        // lifetime切れの場合
        $pear_error = $this->cm->get($string_key, 1);
        $this->assertEqual(E_CACHE_EXPIRED, $pear_error->getCode());

        // ディレクトリ名と同じファイルがあってディレクトリが作成できない場合
        $ref = new ReflectionMethod($this->cm, '_getCacheDir');
        $ref->setAccessible(true);
        $tmp_key = 'tmpkey';
        $tmp_dirname = $ref->invoke($this->cm, $this->cm->getNamespace(), $tmp_key);
        Ethna_Util::mkdir(dirname($tmp_dirname), 0777);
        $tmp_file = fopen($tmp_dirname, 'w');
        fclose($tmp_file);
        $pear_error = $this->cm->set($tmp_key, $string_value);
        $this->assertEqual(E_USER_WARNING, $pear_error->getCode());
        $this->assertEqual("mkdir($tmp_dirname) failed", $pear_error->getMessage());

        $this->rm($this->getNonpublicProperty($this->cm, 'backend')->getTmpdir());

    }

}
