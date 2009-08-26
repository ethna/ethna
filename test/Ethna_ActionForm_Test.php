<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_Test_ActionForm
/**
 *  Test ActionForm 
 *
 *  @access public
 */
class Ethna_Test_ActionForm extends Ethna_ActionForm
{
    var $form = array(
        'test' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test',
        ),

        'no_name' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
        ),

        'test_array' => array(
            'type' => array(VAR_TYPE_STRING),
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test array',
        ),

    );
}
// }}}

// {{{    Ethna_ActionForm_Test
/**
 *  Test Case For Ethna_ActionForm(Mainly Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Test extends Ethna_UnitTestBase
{
    var $local_af;

    function setUp()
    {
        //   REQUEST_METHOD を設定しないと
        //   フォームテンプレートが初期化されない
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->local_af = new Ethna_Test_ActionForm($this->ctl); 
        $this->local_af->clearFormVars();
        $this->ae->clear();
    }

    function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = NULL;
        $this->local_af = NULL;
        $_POST = array();
    }

    // {{{ get
    function test_get()
    {
        $this->local_af->set('test', 'test');
        $this->assertEqual('test', $this->local_af->get('test'));
    }
    // }}}

    // {{{ getDef
    function test_getDef()
    {
        //   null param.
        $def = $this->local_af->getDef();
        $this->assertEqual(3, count($def));
        $this->assertEqual(10, count($def['test']));
        $this->assertEqual('test', $def['test']['name']);

        //   non-exist key.
        $this->assertNull($this->local_af->getDef('hoge'));

        $def = $this->local_af->getDef('test');
        $this->assertEqual(10, count($def));
        $this->assertEqual('test', $def['name']);
    }
    // }}}

    // {{{ getName
    function test_getName()
    {
        $this->assertNull($this->local_af->getName('hoge'));
        $this->assertEqual('test', $this->local_af->getName('test'));
        
        //   もしフォームのname属性がないと、keyそのものが返ってくる
        $this->assertEqual('no_name', $this->local_af->getName('no_name'));
    }
    // }}}

    // {{{ clearFormVars
    function test_clearFormVars()
    {
        $this->local_af->set('test', 'hoge');
        $this->local_af->set('no_name', 'fuga');

        $this->local_af->clearFormVars();

        $this->assertNull($this->local_af->get('test'));
        $this->assertNull($this->local_af->get('no_name'));
    }
    // }}}

    // {{{ set 
    function test_set()
    {
        $this->local_af->set('test', 'test');
        $this->assertEqual('test', $this->local_af->get('test'));
    }
    // }}}

    // {{{ getHiddenVars 
    function test_getHiddenVars()
    {
        //    フォーム定義が配列で、submit された値が非配列の場合
        //    かつ、フォーム定義が配列なので、結局出力するhiddden
        //    タグも配列用のものとなる. 警告も勿論でない
        $this->local_af->set('test_array', 1);

        $hidden = $this->local_af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test_array[0]\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
        $this->local_af->clearFormVars();

        //    配列出力のテスト
        $this->local_af->set('test_array', array(1, 2));
        $hidden = $this->local_af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test_array[0]\" value=\"1\" />\n"
                  . "<input type=\"hidden\" name=\"test_array[1]\" value=\"2\" />\n";
        $this->assertEqual($hidden, $expected);
        $this->local_af->clearFormVars();

        //    スカラーのテスト
        $this->local_af->set('test', 1);
        $hidden = $this->local_af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
        $this->local_af->clearFormVars();

        //    フォーム定義がスカラーで、submitされた値が配列の場合
        //    この場合は明らかに使い方が間違っている上、２重に値が
        //    出力されても意味がないので、警告(E_NOTICE)扱いにする
        //    この場合、hiddenタグは出力されない
        $this->local_af->set('test', array(1,2));
        $hidden = $this->local_af->getHiddenVars();
        $this->assertEqual($hidden, '');  //  値が入っていない扱いなので空文字が返る
        $this->local_af->clearFormVars();

        //    include_list テスト
        $this->local_af->set('test', 1);
        $this->local_af->set('no_name', 'name');
        $this->local_af->set('test_array', array(1,2));
        $include_list = array('test');
        $hidden = $this->local_af->getHiddenVars($include_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
       
        //    exclude_list テスト
        $exclude_list = array('test_array', 'no_name');
        $hidden = $this->local_af->getHiddenVars(NULL, $exclude_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);

        //    include_list, exclude_list の組み合わせ
        $include_list = array('test', 'no_name');
        $exclude_list = array('no_name');
        $hidden = $this->local_af->getHiddenVars($include_list, $exclude_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
    }
    // }}}

}
// }}}

