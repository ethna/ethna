<?php
/**
 * TextDetailReporter.php
 *
 */

/**
 * TextDetailReporter
 *
 */
class TextDetailReporter extends SimpleReporter {

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start.
     *    @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *    Paints the title only.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        if (!self::inCli()) {
            header('Content-type: text/plain');
        }
        print "{$test_name}\n";
        flush();
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            print "\nAll OK\n";
        } else {
            print "\nFAILURES!!!\n";
        }
        print "Test cases run: " . $this->getTestCaseProgress() .
            "/" . $this->getTestCaseCount() .
            ", Passes: " . $this->getPassCount() .
            ", Failures: " . $this->getFailCount() .
            ", Exceptions: " . $this->getExceptionCount() . "\n";
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        print "\n\t" . $this->getFailCount() . ") $message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        print "\n";
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message is ignored.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        print "Exception " . $this->getExceptionCount() . "!\n$message\n";
    }

    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        print "$message\n";
        flush();
    }

    function paintMethodStart($test_name)
    {
        print "  |--- {$test_name}";
    }

    function paintMethodEnd($test_name)
    {
        if ($this->getFailCount() != 0) {
            print " - [41;37mNG[0m";
        } else {
            print " - OK";
        }
        print "\n";
    }

    function paintCaseStart($test_name)
    {
        print "\n {$test_name}\n";
        return parent::paintCaseStart($test_name);
    }
}
