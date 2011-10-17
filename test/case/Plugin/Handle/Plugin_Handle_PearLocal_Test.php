<?php
// vim: foldmethod=marker
/**
 *  Plugin_Handle_PearLocal_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/test/MockProject.php';

//{{{  Ethna_Plugin_Handle_PearLocal_Test
/**
 *  Test Case For Ethna_Plugin_Handle_PearLocal_Test
 *
 *  @access public
 */
class Ethna_Plugin_Handle_PearLocal_Test extends Ethna_UnitTestBase
{
    var $proj;
    protected $er;

    function setUp()
    {
        // disable PEAR's deprecated error
        $this->er = error_reporting();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

        $this->proj = new Ethna_MockProject();
        $r = $this->proj->create();
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());
        }
    }

    function tearDown()
    {
        $this->proj->delete();
        error_reporting($this->er);
    }

    function test_cmd_option()
    {
        //    unrecognized option
        $r = $this->proj->runCmd('pear-local', array('-k'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option -k', $r->getMessage());

        //    pear list -a(get no error)
        //    @see http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=15760
        $r = $this->proj->runCmd('pear-local', array('list', '-a'));
        $this->assertFalse(Ethna::isError($r));

        //    channel requires an argument
        $r = $this->proj->runCmd('pear-local', array('-c'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option -c requires an argument', $r->getMessage());

        $r = $this->proj->runCmd('pear-local', array('--channel'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option --channel requires an argument', $r->getMessage());

        //    normal command exexute(offline only)
        $r = $this->proj->runCmd('pear-local', array('config-set', 'default_channel', 'pear.php.net'));
        $this->assertFalse(Ethna::isError($r));
    }
}
// }}}

