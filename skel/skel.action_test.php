<?php
/**
 *  {$action_path}
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  {$action_name} Form testcase.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$action_form}_TestCase extends Ethna_UnitTestCase
{
    /**
     *  @access private
     *  @var    string  Action name.
     */
    var $action_name = '{$action_name}';

    /**
     *  initialize test.
     *
     *  @access public
     */
    function setUp()
    {
        $this->createActionForm();  // create ActionForm.
    }

    /**
     *  clean up testcase.
     *
     *  @access public
     */
    function tearDown()
    {
    }

    /**
     *  {$action_name} ActionForm sample testcase.
     *
     *  @access public
     */
    function test_formSample()
    {
        /*
        // setting form input.
        $this->af->set('id', 1);

        // {$action_name} ActionForm input validation.
        $this->assertEqual($this->af->validate(), 0);
        */

        /**
         *  TODO: write test case! :)
         *  @see http://simpletest.org/en/first_test_tutorial.html
         *  @see http://simpletest.org/en/unit_test_documentation.html
         */
        $this->fail('No Test! write Test!');
    }
}

/**
 *  {$action_name} Action testcase.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$action_class}_TestCase extends Ethna_UnitTestCase
{
    /**
     *  @access private
     *  @var    string  Action name.
     */
    var $action_name = '{$action_name}';

    /**
     * initialize test.
     *
     * @access public
     */
    function setUp()
    {
        $this->createActionForm();  // create ActionForm.
        $this->createActionClass(); // create ActionClass.

        $this->session->start();    // start session.
    }

    /**
     *  clean up testcase.
     *
     *  @access public
     */
    function tearDown()
    {
        $this->session->destroy();   // destroy session.
    }

    /**
     *  {$action_name} ActionClass sample testcase.
     *
     *  @access public
     */
    function test_actionSample()
    {
        /*
        // setting form input.
        $this->af->set('id', 1);

        // Authentication before processing {$action_name} Action.
        $forward_name = $this->ac->authenticate();
        $this->assertNull($forward_name);

        // {$action_name} Action preprocess.
        $forward_name = $this->ac->prepare();
        $this->assertNull($forward_name);

        // {$action_name} Action implementation.
        $forward_name = $this->ac->perform();
        $this->assertEqual($forward_name, '{$action_name}');
        */

        /**
         *  TODO: write test case! :)
         *  @see http://simpletest.org/en/first_test_tutorial.html
         *  @see http://simpletest.org/en/unit_test_documentation.html
         */
        $this->fail('No Test! write Test!');
    }
}

