<?php
/**
 *  {$view_path}
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  {$forward_name} view implementation.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$view_class}_TestCase extends Ethna_UnitTestCase
{
    /**
     *  @access private
     *  @var    string  view name.
     */
    var $forward_name = '{$forward_name}';

    /**
     * initialize test.
     *
     * @access public
     */
    function setUp()
    {
        $this->createPlainActionForm(); // create ActionForm
        $this->createViewClass();       // create View.
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
     *  {$forward_name} preprocess sample testcase.
     *
     *  @access public
     */
    function test_viewSample()
    {
        /*
        // setting form input. 
        $this->af->set('id', 1);

        // {$forward_name} preprocess.
        $this->vc->preforward();
        $this->assertNull($this->af->get('data'));
        */

        /**
         *  TODO: write test case! :)
         *  @see http://simpletest.org/en/first_test_tutorial.html
         *  @see http://simpletest.org/en/unit_test_documentation.html
         */
        $this->fail('No Test! write Test!');
    }
}
?>
