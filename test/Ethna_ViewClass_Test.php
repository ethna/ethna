<?php
/**
 *  Ethna_ViewClass_Test.php
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 */

/**
 *  Ethna_ViewClassクラスのテストケース
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 *  @access public
 */
class Ethna_ViewClass_Test extends Ethna_UnitTestBase
{
    /**
     * Ethna_ViewClass
     * @var     Ethna_ViewClass
     * @access  protected
     */
    var $viewclass;

    /**
     * setUp
     *
     * make Ethna_ViewClass class
     *
     * @access public
     * @param void
     */
    function setUp()
    {
        $this->viewclass =& $this->ctl->getView();
    }

    /**
     * test_getFormInput_Html
     *
     * @access public
     * @param void
     */
    function test_getFormInput_Html()
    {
        $actual = '<input type="text" name="test" value="&lt;&amp;&gt;" />';

        $test_attr = array(
            'type' => 'text',
            'name' => 'test',
            'value' => '<&>',
        );

        $result = $this->viewclass->_getFormInput_Html('input', $test_attr);

        $this->assertEqual($result, $actual);
    }

    function test_getFormInput_Text()
    {
        $name = "test_text";
        $def = array(
            'max' => 20,
        );
        $params = array();

        $test_form = array(
            'test_text' => array(
                'name' => 'TestTestText',
                'form_type' => FORM_TYPE_TEXT,
                'type' => VAR_TYPE_STRING,
            ),                    
        );

        $this->viewclass->af->setDef(null, $test_form);

        $result = $this->viewclass->_getFormInput_Text($name, $def, $params);
    }

    function test_getFormInput_Textarea()
    {
        $name = "content";
        $params = array();

        $test_form = array(
            $name => array(
                'name' => 'TestTestText',
                'form_type' => FORM_TYPE_TEXTAREA,
                'type' => VAR_TYPE_STRING,
                'required' => true,
            ),                    
        );

        $this->viewclass->af->setDef(null, $test_form);

        $result = $this->viewclass->getFormInput($name, null, $params);

        $this->assertTrue(strpos($result, '</textarea>'), "can't find textarea endtag [{$result}]");
    }

    function test_getFormInput_Select()
    {
        $name = "select";
        $params = array();

        $test_form = array(
            'select' => array(
                'name' => 'TestTestText',
                'form_type' => FORM_TYPE_SELECT,
                'type' => VAR_TYPE_STRING,
                'option' => array('a', 'b', 'c'),
            ),
        );

        $this->viewclass->af->setDef($name, $test_form);

        $result = $this->viewclass->getFormInput($name, null, $params);

        $this->assertTrue(!empty($result), "selectbox make error");
    }

    function test_getFormInput_Checkbox()
    {
        $this->assertTrue(defined('FORM_TYPE_CHECKBOX'), 'undefined FORM_TYPE_CHECKBOX');

        $name = "check";
        $params = array();

        $test_form = array(
            $name => array(
                'name' => 'TestTestText',
                'form_type' => FORM_TYPE_CHECKBOX,
                'type' => array(VAR_TYPE_INT),
                'option' => array('a', 'b', 'c'),
            ),
        );

        $this->viewclass->af->setDef($name, $test_form);

        $result = $this->viewclass->getFormInput($name, null, $params);

        $this->assertTrue(!empty($result), "checkbox make error");
    }

    function test_getFormInput_Submit()
    {
        $this->assertTrue(defined('FORM_TYPE_SUBMIT'), 'undefined FORM_TYPE_SUBMIT');

        $name = "post";
        $params = array();

        $test_form = array(
            $name => array(
                'name' => 'Preview',
                'form_type' => FORM_TYPE_SUBMIT,
                'type' => VAR_TYPE_STRING,
            ),
        );

        $this->viewclass->af->setDef(null, $test_form);

        $result = $this->viewclass->getFormInput($name, null, $params);

        $this->assertTrue(!empty($result), "submit make error");
        $this->assertFalse(strpos($result, 'default '), "invalid attribute");
    }

    function testGetFormName()
    {
        $test_word = "TestTestTest";

        $test_form = array(
            'test_text' => array(
                'name' => 'TestTestTest',
                'form_type' => FORM_TYPE_TEXT,
                'type' => VAR_TYPE_STRING
            ),
        );
        $params = array();

        $this->viewclass->af->form = $test_form;

        $result = $this->viewclass->getFormName('test_text', null, $params);
        $this->assertEqual($result, $test_word);
    }

    function test_getFormInput_Button()
    {
        $name = 'btn';
        $def = array(
            'form_type' => FORM_TYPE_BUTTON,
            );
        $params = array();

        // valueなし
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('btn', null, $params);
        $this->assertTrue(strpos($result, 'value') === false);

        // defaultは指定しても無意味
        $params['default'] = 'hoge';
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('btn', null, $params);
        $this->assertTrue(strpos($result, 'value') === false);

        // valueを指定
        $params['value'] = 'fuga';
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('btn', null, $params);
        $this->assertTrue(strpos($result, 'value="fuga"'));
    }

    function test_getFormInput_Checkbox2()
    {
        $name = 'chkbx';
        $def = array(
            'form_type' => FORM_TYPE_CHECKBOX,
            'type' => array(VAR_TYPE_STRING),
            'option' => array(1=>1, 2=>2),
            'default' => 2,
            );
        $params = array('separator' => "\n");

        $expected =<<<EOS
<label for="chkbx_1"><input type="checkbox" name="chkbx[]" value="1" id="chkbx_1" />1</label>
<label for="chkbx_2"><input type="checkbox" name="chkbx[]" value="2" id="chkbx_2" checked="checked" />2</label>
EOS;

        // def の default 指定で int(2) に check
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('chkbx', null, $params);
        $this->assertEqual($result, $expected);

        // params の default 指定で int(2) に check
        $def['default'] = 1;
        $params['default'] = 2; // paramsが優先
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('chkbx', null, $params);
        $this->assertEqual($result, $expected);

        // params の default 指定で string(1) "2" に check
        $params['default'] = '2';
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('chkbx', null, $params);
        $this->assertEqual($result, $expected);
    }

    function test_default_value()
    {
        $name = 'testform';
        $def = array();
        $params = array();

        // defaultもvalueもないとき
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('testform', null, $params);
        $this->assertTrue(strpos($result, 'value=""'));

        // defaultがあるとき
        $params['default'] = 'hoge';
        unset($params['value']);
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('testform', null, $params);
        $this->assertTrue(strpos($result, 'value="hoge"'));

        // valueがあるとき
        unset($params['default']);
        $params['value'] = 'fuga';
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('testform', null, $params);
        $this->assertTrue(strpos($result, 'value="fuga"'));

        // default, value両方があるとき: valueが優先
        $params['default'] = 'hogefuga';
        $params['value'] = 'foobar';
        $this->viewclass->af->setDef($name, $def);
        $result = $this->viewclass->getFormInput('testform', null, $params);
        $this->assertTrue(strpos($result, 'value="foobar"'));
    }
}
?>
