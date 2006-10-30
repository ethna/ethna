<?php
/**
 *  Ethna_ViewClass_Test.php
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 */

//error_reporting(E_ALL);

/**
 *  Ethna_ViewClassクラスのテストケース
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 *  @access public
 */
class Ethna_ViewClass_Test extends UnitTestCase
{
    /**
     * Ethna_Controller
     *
     * @var     Ethna_Controller
     * @access  protected
     */
    var $ctl;

    /**
     * Ethna_Backend
     *
     * @var     Ethna_Backend
     * @access  protected
     */
    var $backend;

    /**
     * Ethna_ViewClass
     * @var     Ethna_ViewClass
     * @access  protected
     */
    var $viewclass;

    function Ethna_ViewClass_Test()
    {
        $this->ctl =& Ethna_Controller::getInstance();
        if (is_null($ctl)) {
            $this->ctl =& new Ethna_Controller();
            $this->ctl->action_form = new Ethna_ActionForm($this->ctl);
        }

        $this->backend =& $this->ctl->getBackend();
        $this->viewclass =& new Ethna_ViewClass($this->backend, '', '');
    }

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

        $user_attr = array();

        $result = $this->viewclass->_getFormInput_Html('input', $test_attr, $user_attr);

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
        $this->assertTrue(defined('FORM_TYPE_CHECKBOX'), 'undefined FORM_TYPE_SUBMIT');

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

}
?>
