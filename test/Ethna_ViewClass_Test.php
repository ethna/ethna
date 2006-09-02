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
        $this->ctl =& new Ethna_Controller();
        $this->ctl->action_form = new Ethna_ActionForm($this->ctl);

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
        $actual = '<input type="text" name="test" value="1" />';

        $test_attr = array(
            'type' => 'text',
            'name' => 'test',
            'value' => '1',
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
        //var_dump($result);
    }

    function testGetFormName()
    {
        $test_word = "TestTestTest";

        $test_form = array(
            'test_text' => array(
                'name' => $test_word,
                'form_type' => FORM_TYPE_TEXT,
                'type' => VAR_TYPE_STRING,
            ),                    
        );
        $params = array();

        $this->viewclass->af->form = $test_form;

        $result = $this->viewclass->getFormName('test_text', $params);
        $this->assertEqual($result, $test_word);
     }

}
?>
