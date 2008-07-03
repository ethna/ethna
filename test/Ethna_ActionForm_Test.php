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
        $this->local_af = new Ethna_Test_ActionForm($this->ctl); 
        $this->local_af->clearFormVars();
        $this->ae->clear();
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
        $this->assertEqual(2, count($def));
        $this->assertEqual(3, count($def['test']));
        $this->assertEqual('test', $def['test']['name']);

        //   non-exist key.
        $this->assertNull($this->local_af->getDef('hoge'));

        $def = $this->local_af->getDef('test');
        $this->assertEqual(3, count($def));
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

}
// }}}

?>
