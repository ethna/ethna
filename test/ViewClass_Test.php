<?php
/**
 *  ViewClass_Test.php
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 *  @author Yoshinari Takaoka <takaoka@beatcraft.com>
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

/**
 *  Ethna_ViewClass のテストケース
 *  但しフォームヘルパまわりのテストを除く
 *  フォームヘルパのテストについては、Ethna_View_FormHelper_Test.php を参照
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 *  @author Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access public
 */
class Ethna_ViewClass_Test extends Ethna_UnitTestBase
{
    var $view;

    function setUp()
    {
        $this->view = $this->ctl->getView();
    }

    function test_forward()
    {
        // todo add tests.
    }

    function test_header_integer()
    {
        if (version_compare(PHP_VERSION, '5.0.0', '>')) {

            $expected_values = array(
                '100' => 'HTTP/1.1: 100 Continue',
                '101' => 'HTTP/1.1: 101 Switching Protocols',
                '200' => 'HTTP/1.1: 200 OK',
                '201' => 'HTTP/1.1: 201 Created',
                '202' => 'HTTP/1.1: 202 Accepted',
                '203' => 'HTTP/1.1: 203 Non-Authoritative Information',
                '204' => 'HTTP/1.1: 204 No Content',
                '205' => 'HTTP/1.1: 205 Reset Content',
                '206' => 'HTTP/1.1: 206 Partial Content',
                '300' => 'HTTP/1.1: 300 Multiple Choices',
                '301' => 'HTTP/1.1: 301 Moved Permanently',
                '302' => 'HTTP/1.1: 302 Found',
                '303' => 'HTTP/1.1: 303 See Other',
                '304' => 'HTTP/1.1: 304 Not Modified',
                '305' => 'HTTP/1.1: 305 Use Proxy',
                '307' => 'HTTP/1.1: 307 Temporary Redirect',
                '400' => 'HTTP/1.1: 400 Bad Request',
                '401' => 'HTTP/1.1: Unauthorized',
                '402' => 'HTTP/1.1: Payment Required',
                '403' => 'HTTP/1.1: Forbidden',
                '404' => 'HTTP/1.1: Not Found',
                '405' => 'HTTP/1.1: Method Not Allowed',
                '406' => 'HTTP/1.1: Not Acceptable',
                '407' => 'HTTP/1.1: Proxy Authentication Required',
                '408' => 'HTTP/1.1: Request Time-out',
                '409' => 'HTTP/1.1: Conflict',
                '410' => 'HTTP/1.1: Gone',
                '411' => 'HTTP/1.1: Length Required',
                '412' => 'HTTP/1.1: Precondition Failed',
                '413' => 'HTTP/1.1: Request Entity Too Large',
                '414' => 'HTTP/1.1: Request-URI Too Large',
                '415' => 'HTTP/1.1: Unsupported Media Type',
                '416' => 'HTTP/1.1: Requested range not satisfiable',
                '417' => 'HTTP/1.1: Expectation Failed',
                '500' => 'HTTP/1.1: Internal Server Error',
                '501' => 'HTTP/1.1: Not Implemented',
                '502' => 'HTTP/1.1: Bad Gateway',
                '503' => 'HTTP/1.1: Service Unavailable',
                '504' => 'HTTP/1.1: Gateway Time-out'
            );
            foreach ($expected_values as $status => $raw_header) {
                @$this->view->header($status);
                $headers_sent = headers_list();
                $this->assertNotA(array_search($raw_header, $headers_sent), false);
            }
        }
    }

    function test_header_array()
    {
        $test_status = array('X-PHP-Framework' => 'Ethna 3000',);
        @$this->view->header($test_status);
        $headers_sent = headers_list();
        $expected = 'X-PHP-Framework: Ethna 3000';
        $this->assertNotA(array_search($expected, $headers_sent), false);
        
    }

    function test_header_string()
    {
        //  valid header
        $expected = 'X-PHP-Framework: Ethna 3001';
        @$this->view->header($expected);
        $headers_sent = headers_list();
        $this->assertNotA(array_search($expected, $headers_sent), false);

        //  invalid header
        $invalid_header = 'invalid_header@foo';
        @$this->view->header($invalid_header);
        $headers_sent = headers_list();
        $this->assertFalse(array_search($invalid_header, $headers_sent));
    }

    function test_redirect()
    {
        @$this->view->redirect('http://ethna.jp');
        $headers_sent = headers_list();
        $expected = 'Location: http://ethna.jp';
        $this->assertNotA(array_search($expected, $headers_sent), false);
        $expected = 'HTTP/1.1: 302 Found';
        $this->assertNotA(array_search($expected, $headers_sent), false);
    }

    function test_redirect_statuscode()
    {
        @$this->view->redirect('http://ethna.jp', 301);
        $headers_sent = headers_list();
        $expected = 'Location: http://ethna.jp';
        $this->assertNotA(array_search($expected, $headers_sent), false);
        $expected = 'HTTP/1.1: 301 Moved Permanently';
        $this->assertNotA(array_search($expected, $headers_sent), false);
    }

    function test_setLayout()
    {
        $project = new Ethna_MockProject();
        $project->create();
        $ctl = $project->getController()->getBackend();
        $view = new MockProject_ViewClass($ctl, 'dummy', 'dummy.tpl');

        //  invalid file
        $return = $view->setLayout('fake');
        $this->assertTrue(Ethna::isError($return));

        //  valid layout file test. 
        $return = $view->setLayout('index');
        $this->assertFalse(Ethna::isError($return));
    }

    function test_getLayout()
    {
        $layout = $this->view->getLayout();
        $this->assertEqual($layout, 'layout.tpl');
    }

    function test_templateExists()
    {
        $project = new Ethna_MockProject();
        $project->create();
        $ctl = $project->getController()->getBackend();
        $view = new MockProject_ViewClass($ctl, 'dummy', 'dummy.tpl');
        
        $this->assertTrue($view->templateExists('index.tpl'));
        $this->assertFalse($view->templateExists('dummy.tpl'));

        $project->delete();
    }

    function test_error()
    {
        // todo: add test page output
    }
}

