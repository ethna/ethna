<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ActionFormTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $af;

    public function setup()
    {
        //   REQUEST_METHOD を設定しないと
        //   フォームテンプレートが初期化されない
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller = new Ethna_Controller_Dummy();
        $this->af = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->af);
        $this->af->clearFormVars();
    }

    public function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = NULL;
        $this->af = NULL;
        $this->controller = NULL;
        $_POST = array();
    }

    /**
     * @test
     */
    public function getActionFormValue()
    {
        $this->af->set('test', 'test');
        $this->assertEquals('test', $this->af->get('test'));
    }

    public function testGetDef()
    {
        //   null param.
        $def = $this->af->getDef();
        $this->assertEquals(3, count($def));
        $this->assertEquals(10, count($def['test']));
        $this->assertEquals('test', $def['test']['name']);

        //   non-exist key.
        $this->assertNull($this->af->getDef('hoge'));

        $def = $this->af->getDef('test');
        $this->assertEquals(10, count($def));
        $this->assertEquals('test', $def['name']);
    }

    public function testGetName()
    {
        $this->assertNull($this->af->getName('hoge'));
        $this->assertEquals('test', $this->af->getName('test'));

        //   もしフォームのname属性がないと、keyそのものが返ってくる
        $this->assertEquals('no_name', $this->af->getName('no_name'));
    }

    public function test_clearFormVars()
    {
        $this->af->set('test', 'hoge');
        $this->af->set('no_name', 'fuga');

        $this->af->clearFormVars();

        $this->assertNull($this->af->get('test'));
        $this->assertNull($this->af->get('no_name'));
    }

    public function test_set()
    {
        $this->af->set('test', 'test');
        $this->assertEquals('test', $this->af->get('test'));
    }

    public function test_getHiddenVars()
    {
        //    フォーム定義が配列で、submit された値が非配列の場合
        //    かつ、フォーム定義が配列なので、結局出力するhiddden
        //    タグも配列用のものとなる. 警告も勿論でない
        $this->af->set('test_array', 1);

        $hidden = $this->af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test_array[0]\" value=\"1\" />\n";
        $this->assertEquals($hidden, $expected);
        $this->af->clearFormVars();

        //    配列出力のテスト
        $this->af->set('test_array', array(1, 2));
        $hidden = $this->af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test_array[0]\" value=\"1\" />\n"
            . "<input type=\"hidden\" name=\"test_array[1]\" value=\"2\" />\n";
        $this->assertEquals($hidden, $expected);
        $this->af->clearFormVars();

        //    スカラーのテスト
        $this->af->set('test', 1);
        $hidden = $this->af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEquals($hidden, $expected);
        $this->af->clearFormVars();

        //    フォーム定義がスカラーで、submitされた値が配列の場合
        //    この場合は明らかに使い方が間違っている上、２重に値が
        //    出力されても意味がないので、警告(E_NOTICE)扱いにする
        //    この場合、hiddenタグは出力されない
        $this->af->set('test', array(1,2));
        $hidden = $this->af->getHiddenVars();
        $this->assertEquals($hidden, '');  //  値が入っていない扱いなので空文字が返る
        $this->af->clearFormVars();

        //    include_list テスト
        $this->af->set('test', 1);
        $this->af->set('no_name', 'name');
        $this->af->set('test_array', array(1,2));
        $include_list = array('test');
        $hidden = $this->af->getHiddenVars($include_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEquals($hidden, $expected);

        //    exclude_list テスト
        $exclude_list = array('test_array', 'no_name');
        $hidden = $this->af->getHiddenVars(NULL, $exclude_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEquals($hidden, $expected);

        //    include_list, exclude_list の組み合わせ
        $include_list = array('test', 'no_name');
        $exclude_list = array('no_name');
        $hidden = $this->af->getHiddenVars($include_list, $exclude_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEquals($hidden, $expected);
    }

    public function test_setDef()
    {
        $orig = $this->af->getDef();

        $def = array(
            'name' => 'hoge test',
            'type' => VAR_TYPE_STRING,
            'form_type'=> FORM_TYPE_TEXT,
            'required' => true,
        );
        $this->af->setDef('hoge', $def);

        $this->assertEquals($def, $this->af->getDef('hoge'));

        $this->af->setDef(null, $orig);
        $this->assertEquals($this->af->getFormValue('test'), $this->af->getDef('test'));
        $this->assertEquals($orig['test'], $this->af->getDef('test'));
        $this->assertEquals($orig, $this->af->getDef());
    }
}