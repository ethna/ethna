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
        $this->SimpleReporter();
    }

    /**
     *    Paints the title only.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        if (!SimpleReporter::inCli()) {
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
        //print "Start {$test_name} Test\n";
        print "  |--- {$test_name}";
        $this->before_fails = $this->_fails;
    }

    var $before_fails = 0;
    
    function paintMethodEnd($test_name)
    {
        //print "End {$test_name} Test\n";
        if ($this->before_fails != $this->_fails) {
            print " - NG";
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
