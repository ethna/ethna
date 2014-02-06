<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Cachemanager_Localfile_Test extends PHPUnit_Framework_TestCase
{
    public $ctl;
    public $cm;
    public $cm_ref;


    function setUp()
    {
        $ctl = new Ethna_Controller_Dummy();
        $plugin = $ctl->getPlugin();
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
        $this->assertEquals('miyazakiaoi', array_shift($array));

        $array = array_slice(explode('/', $ref->invoke($this->cm, '', 'string_key')), -4, 1);
        //$this->assertEquals('default', array_shift($array));
    }

    function testCachemanagerLocalfileNamespace()
    {
        $namespace = "miyazakiaoi";
        $this->cm->setNamespace($namespace);
        $this->assertEquals('miyazakiaoi', $this->cm->getNamespace());
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
        $this->assertTrue((bool)$int_value);
        $this->assertTrue((bool)$cache_int);
    }

    function testCachemanagerLocalfileString()
    {
        // 文字列のキャッシュ
        $string_key = 'string_key';
        $string_value = "cache\ncontent";
        $this->cm->set($string_key, $string_value, mktime(0, 0, 0, 7, 1, 2000));
        $cache_string = $this->cm->get($string_key);
        $this->assertTrue($this->cm->isCached($string_key));
        $this->assertEquals(mktime(0, 0, 0, 7, 1, 2000), $this->cm->getLastModified($string_key));
        $this->assertTrue((bool)$string_value);
        $this->assertTrue((bool)$cache_string);
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
        $this->assertTrue((bool)$string_value);
        $this->assertTrue((bool)$cache_object->get($string_key));

        // キャッシュのクリアをテスト
        $this->cm->clear($object_key);
        $this->assertFalse($this->cm->isCached($object_key));

        // キャッシュされていないのに呼び出そうとした場合
        $nocache_key = 'nocache_key';
        $this->cm->clear($nocache_key);
        $pear_error = $this->cm->get($nocache_key);
        $this->assertEquals(E_CACHE_NO_VALUE, $pear_error->getCode());

        // ファイルに読み込み権限がない場合
        // PHP 4, PHP5 ともに、Windows上ではmodeをどのように設定しても
        // read権限が残るためskip.(PHP 4.4.8, 5.2.6 on Windows XP)
        if (!ETHNA_OS_WINDOWS) {
            $ref = new ReflectionMethod($this->cm, '_getCacheFile');
            $ref->setAccessible(true);
            Ethna_Util::chmod($ref->invoke($this->cm, $this->cm->getNamespace(), $string_key), 0222);
            $pear_error = $this->cm->get($string_key);
            $this->assertEquals(E_CACHE_NO_VALUE, $pear_error->getCode());
            Ethna_Util::chmod($ref->invoke($this->cm, $this->cm->getNamespace(), $string_key), 0666);
        }

        // lifetime切れの場合
        $pear_error = $this->cm->get($string_key, 1);
        $this->assertEquals(E_CACHE_EXPIRED, $pear_error->getCode());

        // ディレクトリ名と同じファイルがあってディレクトリが作成できない場合
        $ref = new ReflectionMethod($this->cm, '_getCacheDir');
        $ref->setAccessible(true);
        $tmp_key = 'tmpkey';
        $tmp_dirname = $ref->invoke($this->cm, $this->cm->getNamespace(), $tmp_key);
        Ethna_Util::mkdir(dirname($tmp_dirname), 0777);
        $tmp_file = fopen($tmp_dirname, 'w');
        fclose($tmp_file);
        $pear_error = $this->cm->set($tmp_key, $string_value);
        $this->assertEquals(E_USER_WARNING, $pear_error->getCode());
        $this->assertEquals("mkdir($tmp_dirname) failed", $pear_error->getMessage());

        $this->rm($this->getNonpublicProperty($this->cm, 'backend')->getTmpdir());

    }

    function getNonpublicProperty($object, $property_name)
    {
        // NOTE(chobie): really bad idea.
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $ref = new ReflectionProperty(get_class($object), $property_name);
            $ref->setAccessible(true);
            return $ref->getValue($object);
        } else {
            $arr = (array)$object;
            $key = $property_name;

            $ref = new ReflectionProperty(get_class($object), $property_name);
            if ($ref->isProtected()) {
                $key = "\0*\0".$key;
            } elseif ($ref->isPrivate()) {
                $key = "\0".get_class($object)."\0".$key;
            }

            return $arr[$key];
        }
    }

}
