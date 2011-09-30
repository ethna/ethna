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
        echo "{$test_name}\n";
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
            echo "\nAll OK\n";
        } else {
            echo "\n[41;37mFAILURES!!![0m\n";
        }
        echo "Test cases run: " . $this->getTestCaseProgress() .
            "/" . $this->getTestCaseCount() .
            ", Passes: " . $this->getPassCount() .
            ", Failures: " . $this->getFailCount() .
            ", Exceptions: " . $this->getExceptionCount() . PHP_EOL;
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        echo "\n\t" . $this->getFailCount() . ") $message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        echo "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        echo PHP_EOL;
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message is ignored.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        echo PHP_EOL;
        echo "       Error ", $this->getExceptionCount(), "!", PHP_EOL;
        echo "       $message";
    }

    function paintException($message) {
        parent::paintException($message);
        echo PHP_EOL;
        echo "       Exception ", $this->getExceptionCount(), "!", PHP_EOL;
        echo "       {$message->getMessage()}";
    }

    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        echo "$message\n";
        flush();
    }

    protected $before_fails = 0;
    function paintMethodStart($test_name)
    {
        echo "  |--- {$test_name}";
        $this->before_fails = $this->getFailCount() + $this->getExceptionCount();
    }

    function paintMethodEnd($test_name)
    {
        if ($this->before_fails != $this->getFailCount() + $this->getExceptionCount()) {
            echo " - [41;37mNG[0m";
        } else {
            echo " - OK";
        }
        echo PHP_EOL;
    }

    function paintCaseStart($test_name)
    {
        echo "\n {$test_name}\n";
        return parent::paintCaseStart($test_name);
    }
}
