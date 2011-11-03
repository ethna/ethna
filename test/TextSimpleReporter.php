<?php
/**
 * TextSimpleReporter.php
 *
 * @package Ethna
 * @author  Sotaro KARASAWA <sotaro.k@gmail.com>
 */

/**
 * TextSimpleReporter
 *
 * @author  Sotaro KARASAWA <sotaro.k@gmail.com>
 */
class TextSimpleReporter extends SimpleReporter
{
    const E_TYPE_FAIL = 'fail';
    const E_TYPE_ERROR = 'error';
    const E_TYPE_EXCEPTION = 'exception';

    /**
     *  array[] = array('test_name', 'message', 'original')
     */
    protected $errors = array();

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
    public function paintHeader($test_name) {
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
    public function paintFooter($test_name) {
        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            echo "\nAll OK\n";
        } else {
            echo PHP_EOL, PHP_EOL;
            echo "There was ", $this->getFailCount(), " fail(s), ", $this->getExceptionCount(), " error(s).", PHP_EOL, PHP_EOL;

            $this->printAllErrors();

            echo "\n[41;37mFAILURES!!![0m\n";
        }
        echo "Test cases run: " . $this->getTestCaseProgress() .
            "/" . $this->getTestCaseCount() .
            ", Passes: " . $this->getPassCount() .
            ", Failures: " . $this->getFailCount() .
            ", Exceptions: " . $this->getExceptionCount() . PHP_EOL
            ;
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    public function paintFail($message) {
        parent::paintFail($message);

        $this->addError(
            self::E_TYPE_FAIL,
            $this->getTestList(),
            $message,
            $message
        );
    }

    public function addError($type, $testlist, $message, $original)
    {
        $this->errors[] = array($type, $testlist, $message, $original);
    }

    public function printAllErrors()
    {
        $before = "";
        $testcount = 0;
        foreach ($this->errors as $error) {
            ++$testcount;
            list($type, $testlist, $message, $original) = $error;

            $breadcrumb = $testlist;
            array_shift($breadcrumb);
            $testname = reset(array_reverse($breadcrumb));
            if ($before != $testname) {
                echo "##) ", $testname, PHP_EOL;
            }
            $before = $testname;

            if ($type == self::E_TYPE_FAIL) {
                echo "$testcount) [41;37mFAILS[0m: ",
                    str_replace(' at [/', "\n\tat [/", $message);
                echo PHP_EOL;
            } else if ($type == self::E_TYPE_ERROR) {
                echo "$testcount) ",
                    str_replace(' in [/', "\n\tin [/",
                        str_replace(' severity [', "\n\tseverity [", $message)
                    );
                echo PHP_EOL;
            } elseif ($type == self::E_TYPE_EXCEPTION) {
                echo "$testcount) [", get_class($original), '] ', $original->getMessage(), "\n\tin [", $original->getFile(), ' line ', $original->getLine(), ']';
                echo PHP_EOL;
            }

            echo PHP_EOL;
        }
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message is ignored.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);

        $this->addError(
            self::E_TYPE_ERROR,
            $this->getTestList(),
            $message,
            $message
        );
    }

    function paintException($exception) {
        parent::paintException($exception);

        $this->addError(
            self::E_TYPE_EXCEPTION,
            $this->getTestList(),
            $exception->getMessage(),
            $exception
        );
    }

    function paintSkip($message)
    {
        // TODO : increment skip count and stack skip messages.
        // echo $message, PHP_EOL;
        throw new Exception('This unit tester is not support skip method.');
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
        $this->before_fails = $this->getFailCount() + $this->getExceptionCount();
    }

    public $test_count = 0;
    function paintMethodEnd($test_name)
    {
        if ($this->before_fails != $this->getFailCount() + $this->getExceptionCount()) {
            echo "E";
        } else {
            echo ".";
        }

        if (++$this->test_count % 50 === 0) {
            echo PHP_EOL;
        }
        //echo PHP_EOL;
    }

    function paintCaseStart($test_name)
    {
        return parent::paintCaseStart($test_name);
    }
}
