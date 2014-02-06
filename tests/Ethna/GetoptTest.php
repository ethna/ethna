<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Getopt_Test extends PHPUnit_Framework_TestCase
{
    public $opt;

    public function setUp()
    {
        $this->opt = new Ethna_Getopt();
    }

    public function test_readPHPArgv()
    {
        global $argv;
        $argv = array('test.php', 'a', '-b=c', '--c=d', 'e');

        $r = $this->opt->readPHPArgv();
        $this->assertEquals('test.php', $argv[0]);
        $this->assertEquals('a', $argv[1]);
        $this->assertEquals('-b=c', $argv[2]);
        $this->assertEquals('--c=d', $argv[3]);
        $this->assertEquals('e', $argv[4]);
    }

    public function test_shortopt_required()
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
        $this->assertEquals('option -a requires an argument', $r->getMessage());

        // unknown option
        $args = array('-c'); // -c is unknown.
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals('unrecognized option -c', $r->getMessage());

        // unknown option part 2.
        $args = array('--foo'); // -foo is unknown.
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals('unrecognized option --foo', $r->getMessage());

        // -a option value is b. c is nonparsed.
        $args = array('-a', 'b', 'c');
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertEquals('b', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('c', $nonparsed_arg[0]);

        // -a value is bcd, e is nonparsed.
        $args = array('-abcd', 'e');
        $shortopt = 'a:';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertEquals('bcd', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('e', $nonparsed_arg[0]);
    }

    public function test_shortopt_optional()
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
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertEquals('bcd', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('e', $nonparsed_arg[0]);

        // -a option value is none. b, c is nonparsed.
        $args = array('-a', 'b', 'c');
        $shortopt = 'a::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('b', $nonparsed_arg[0]);
        $this->assertEquals('c', $nonparsed_arg[1]);
    }

    public function test_shortopt_disabled()
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
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        // option -a is defined, but value is disabled.
        // value will be NEVER interpreted.
        $args = array('-a', 'b', 'c');
        $shortopt = 'a';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));
        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('b', $nonparsed_arg[0]);
        $this->assertEquals('c', $nonparsed_arg[1]);

        // successive option definition, but unrecognized option. :)
        $args = array('-ab');
        $shortopt = 'a';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals("unrecognized option -b", $r->getMessage());

        // option setting will be refrected even when after values. :)
        $args = array('-a', 'b', '-c', 'd', '-e', 'f');
        $shortopt = 'ac:e::';
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('b', $nonparsed_arg[0]);
        $this->assertEquals('-c', $nonparsed_arg[1]);
        $this->assertEquals('d', $nonparsed_arg[2]);
        $this->assertEquals('-e', $nonparsed_arg[3]);
        $this->assertEquals('f', $nonparsed_arg[4]);
    }

    public function test_shortopt_complex()
    {
        //  complex option part 1.
        $args = array();
        $shortopt = 'ab:c::';
        $args = array('-abcd', '-cd');
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $this->assertEquals('b', $parsed_arg[1][0]);
        $this->assertEquals('cd', $parsed_arg[1][1]);

        $this->assertEquals('c', $parsed_arg[2][0]);
        $this->assertEquals('d', $parsed_arg[2][1]);

        //  complex option part 2.
        $args = array('-a', '-c', 'd', 'e');
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $this->assertEquals('c', $parsed_arg[1][0]);
        $this->assertNULL($parsed_arg[1][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('d', $nonparsed_arg[0]);
        $this->assertEquals('e', $nonparsed_arg[1]);

        $args = array('-cd', '-ad');
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals("unrecognized option -d", $r->getMessage());
    }

    public function test_longopt_required()
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
        $this->assertEquals('option --foo requires an argument', $r->getMessage());

        // unknown option.
        $args = array('--bar'); // -bar is unknown.
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals('unrecognized option --bar', $r->getMessage());

        // unknown option part 1.
        $args = array('--bar'); // -bar is unknown.
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals('unrecognized option --bar', $r->getMessage());

        // unknown option part 2.
        $args = array('-a'); // -a is unknown.
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals('unrecognized option -a', $r->getMessage());

        // --foo option value is bar. hoge is nonparsed.
        $args = array('--foo=bar', 'hoge');
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('--foo', $parsed_arg[0][0]);
        $this->assertEquals('bar', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('hoge', $nonparsed_arg[0]);

        // --foo option value is bar. hoge, -fuga is nonparsed.
        $args = array('--foo', 'bar', 'hoge', '-fuga');
        $shortopt = NULL;
        $longopt = array("foo=");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('--foo', $parsed_arg[0][0]);
        $this->assertEquals('bar', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('hoge', $nonparsed_arg[0]);
        $this->assertEquals('-fuga', $nonparsed_arg[1]);
    }

    public function test_longopt_optional()
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
        $this->assertEquals('--foo', $parsed_arg[0][0]);
        $this->assertNULL($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('bar', $nonparsed_arg[0]);
        $this->assertEquals('hoge', $nonparsed_arg[1]);

        // -foo value is bar, hoge, moge is nonparsed arg.
        $args = array('--foo=bar', 'hoge', 'moge');
        $shortopt = NULL;
        $longopt = array("foo==");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('--foo', $parsed_arg[0][0]);
        $this->assertEquals('bar', $parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('hoge', $nonparsed_arg[0]);
        $this->assertEquals('moge', $nonparsed_arg[1]);
    }

    public function test_longopt_disabled()
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
        $this->assertEquals('--foo', $parsed_arg[0][0]);
        $this->assertnull($parsed_arg[0][1]);

        // option -foo is defined, but value is disabled.
        $args = array('--foo=bar');
        $shortopt = null;
        $longopt = array("foo");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertTrue(Ethna::isError($r));
        $this->assertEquals("option --foo doesn't allow an argument", $r->getMessage());

        $args = array('--foo', 'hoge', 'bar');
        $shortopt = null;
        $longopt = array("foo");
        $r = $this->opt->getopt($args, $shortopt, $longopt);
        $this->assertFalse(Ethna::isError($r));

        $parsed_arg = array_shift($r);
        $this->assertEquals('--foo', $parsed_arg[0][0]);
        $this->assertNull($parsed_arg[0][1]);

        $nonparsed_arg = array_shift($r);
        $this->assertEquals('hoge', $nonparsed_arg[0]);
        $this->assertEquals('bar', $nonparsed_arg[1]);
    }

    public function test_mixed_option()
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
        $this->assertEquals('a', $parsed_arg[0][0]);
        $this->assertNull($parsed_arg[0][1]);
        $this->assertEquals('--foo', $parsed_arg[1][0]);
        $this->assertEquals('bar', $parsed_arg[1][1]);
        $this->assertEquals('--bar', $parsed_arg[2][0]);
        $this->assertEquals('moge', $parsed_arg[2][1]);


        $nonparsed_arg = array_shift($r);
        $this->assertEquals('hoge', $nonparsed_arg[0]);
        $this->assertEquals('--hoge', $nonparsed_arg[1]);
    }
}

