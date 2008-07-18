<?php
// vim: foldmethod=marker
/**
 *  Ethna_Getopt_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Ethna_Getopt.php';

/**
 *  Test Case For Ethna_Getopt
 *
 *  @access public
 */
class Ethna_Getopt_Test extends Ethna_UnitTestBase
{
    var $opt;

    function setUp()
    {
        $this->opt = new Ethna_Getopt();
    }

    // {{{ readPHPArgv
    function test_readPHPArgv()
    {
        global $argv;
        $argv = array('test.php', 'a', '-b=c', '--c=d', 'e');
        
        $r = $this->opt->readPHPArgv();
        $this->assertEqual('test.php', $argv[0]);
        $this->assertEqual('a', $argv[1]);
        $this->assertEqual('-b=c', $argv[2]);
        $this->assertEqual('--c=d', $argv[3]);
        $this->assertEqual('e', $argv[4]);
    }
    // }}}

    //{{{ short option test
    function test_shortopt_required()
    {
        // no args
        $args = array();
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
 
        // option -a is defined, but no args.
        $args = array('-a');
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option -a requires an argument', $r->getMessage());

        // unknown option 
        $args = array('-c'); // -c is unknown.
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option -c', $r->getMessage());

        // unknown option part 2.
        $args = array('--foo'); // -foo is unknown.
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option --foo', $r->getMessage());

        // -a option value is b. c is nonparsed.
        $args = array('-a', 'b', 'c');
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertEqual('b', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('c', $nonparsed_arg[0]);

        // -a value is bcd, e is nonparsed.
        $args = array('-abcd', 'e');
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertEqual('bcd', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('e', $nonparsed_arg[0]);
    }

    function test_shortopt_optional()
    {
        // no args
        $args = array();
        $shortopt = 'a::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
 
        // option -a is defined, but no args.
        $args = array('-a');
        $shortopt = 'a::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        // -a value is bcd, e is nonparsed arg.
        $args = array('-abcd', 'e');
        $shortopt = 'a::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertEqual('bcd', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('e', $nonparsed_arg[0]);
 
        // -a option value is none. b, c is nonparsed.
        $args = array('-a', 'b', 'c');
        $shortopt = 'a::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('b', $nonparsed_arg[0]);
        $this->assertEqual('c', $nonparsed_arg[1]);
    }

    function test_shortopt_disabled()
    {
        // no args
        $args = array();
        $shortopt = 'a';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
 
        // option -a is defined, but no args.
        $args = array('-a');
        $shortopt = 'a';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        // option -a is defined, but value is disabled.
        // value will be NEVER interpreted.
        $args = array('-a', 'b', 'c');
        $shortopt = 'a';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('b', $nonparsed_arg[0]);
        $this->assertEqual('c', $nonparsed_arg[1]);

        // successive option definition, but unrecognized option. :)
        $args = array('-ab');
        $shortopt = 'a';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual("unrecognized option -b", $r->getMessage());

        // option setting will be refrected even when after values. :)
        $args = array('-a', 'b', '-c', 'd', '-e', 'f');
        $shortopt = 'ac:e::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);
        $this->assertEqual('c', $parsed_arg[1][0]);
        $this->assertEqual('d', $parsed_arg[1][1]);
        $this->assertEqual('e', $parsed_arg[2][0]);
        $this->assertNULL($parsed_arg[2][1]);
 
        $nonparsed_arg = array_shift($r);
        $this->assertEqual('b', $nonparsed_arg[0]);
        $this->assertEqual('f', $nonparsed_arg[1]);
    }

    function test_shortopt_complex()
    {
        //  complex option part 1.
        $args = array();
        $shortopt = 'ab:c::';
        $args = array('-abc', '-cd');
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $this->assertEqual('b', $parsed_arg[1][0]);
        $this->assertEqual('c', $parsed_arg[1][1]);

        $this->assertEqual('c', $parsed_arg[2][0]);
        $this->assertEqual('d', $parsed_arg[2][1]);

        //  complex option part 2.
        $args = array('-a', '-c', 'd', 'e');
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $this->assertEqual('c', $parsed_arg[1][0]);
        $this->assertNULL($parsed_arg[1][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('d', $nonparsed_arg[0]);
        $this->assertEqual('e', $nonparsed_arg[1]);

        $args = array('-cd', '-ad');
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual("unrecognized option -d", $r->getMessage());
    }
    // }}}

    // {{{  long option test
    function test_longopt_required()
    {
        // no args
        $args = array();
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));
    
        // option -a is defined, but no args.
        $args = array('--foo');
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option --foo requires an argument', $r->getMessage());

        // unknown option.
        $args = array('--bar'); // -bar is unknown.
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option --bar', $r->getMessage());

        // unknown option part 1.
        $args = array('--bar'); // -bar is unknown.
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option --bar', $r->getMessage());

        // unknown option part 2.
        $args = array('-a'); // -a is unknown.
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option -a', $r->getMessage());

        // --foo option value is bar. hoge is nonparsed. 
        $args = array('--foo=bar', 'hoge');
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('--foo', $parsed_arg[0][0]);
        $this->assertEqual('bar', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('hoge', $nonparsed_arg[0]);
 
        // --foo option value is bar. hoge is nonparsed.
        $args = array('--foo', 'bar', 'hoge');
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('--foo', $parsed_arg[0][0]);
        $this->assertEqual('bar', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('hoge', $nonparsed_arg[0]);
    }

    function test_longopt_optional()
    {
        // no args
        $args = array();
        $shortopt = NULL;
        $longopt = array("foo==");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));
 
        // option --foo is defined, but no args.
        $args = array('--foo');
        $shortopt = NULL;
        $longopt = array("foo==");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        // -foo value is bar, hoge is nonparsed arg.
        $args = array('--foo', 'bar', 'hoge');
        $shortopt = NULL;
        $longopt = array("foo==");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('--foo', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('bar', $nonparsed_arg[0]);
        $this->assertEqual('hoge', $nonparsed_arg[1]);

        // -foo value is bar, hoge, moge is nonparsed arg.
        $args = array('--foo=bar', 'hoge', 'moge');
        $shortopt = NULL;
        $longopt = array("foo==");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEqual('--foo', $parsed_arg[0][0]);
        $this->assertEqual('bar', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('hoge', $nonparsed_arg[0]);
        $this->assertEqual('moge', $nonparsed_arg[1]);
    }

    function test_longopt_disabled()
    {
        // no args
        $args = array();
        $shortopt = NULL;
        $longopt = array("foo");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        // option -foo is defined, but no args.
        $args = array('--foo');
        $shortopt = null;
        $longopt = array("foo");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertfalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertequal('--foo', $parsed_arg[0][0]);
        $this->assertnull($parsed_arg[0][1]);

        // option -foo is defined, but value is disabled.
        $args = array('--foo=bar');
        $shortopt = null;
        $longopt = array("foo");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual("option --foo doesn't allow an argument", $r->getMessage());

        $args = array('--foo', 'hoge', 'bar');
        $shortopt = null;
        $longopt = array("foo");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertequal('--foo', $parsed_arg[0][0]);
        $this->assertNull($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEqual('hoge', $nonparsed_arg[0]);
        $this->assertEqual('bar', $nonparsed_arg[1]);
    }
    // }}}

    // {{{  short option, long option mixed.
    function test_mixed_option()
    {
        // no args
        $shortopt = 'ab:c::';
        $longopt = array('foo=', 'bar==', 'hoge');

        $args = array();
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));
        
        $args = array('-a', '--foo', 'bar', '--bar=moge', 'hoge', '--hoge');
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));
 
        $parsed_arg = array_shift($r);
        $this->assertequal('a', $parsed_arg[0][0]);
        $this->assertNull($parsed_arg[0][1]);
        $this->assertequal('--foo', $parsed_arg[1][0]);
        $this->assertEqual('bar', $parsed_arg[1][1]);
        $this->assertequal('--bar', $parsed_arg[2][0]);
        $this->assertEqual('moge', $parsed_arg[2][1]);
        $this->assertequal('--hoge', $parsed_arg[3][0]);
        $this->assertNULL($parsed_arg[3][1]);


        $nonparsed_arg = array_shift($r);
        $this->assertEqual('hoge', $nonparsed_arg[0]);
    }
    // }}}
}

?>
