<?php
/**
 *  Logger_Test.php
 */

/**
 *  Ethna_Loggerクラスのテストケース
 *  (Logwriterではなく、LogwriterのマネージャとしてのLoggerのテスト)
 *
 *  @access public
 */
class Ethna_Logger_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        // ConfigクラスをEthna_Logger_Test_Configに設定
        $this->ctl->class['config'] = 'Ethna_Logger_Test_Config';
        $this->ctl->getConfig();
    }

    function tearDown()
    {
        // do nothing.
    }

    function _resetLoggerSetting($config)
    {
        unset($this->ctl->getClassFactory()->object['logger']);
        $config_obj = $this->ctl->getClassFactory()->object['config'];
        $config_obj->config = $config;
    }

    /**
     *  old style log setting.
     */
    function test_parseSetting_Compatible()
    {
        $config = array(
            'log_facility'      => 'echo',
            'log_level'         => 'warning',
            'log_option'        => 'pid,function,pos',
        );
        $this->_resetLoggerSetting($config);
        $this->logger = $this->ctl->getLogger();

        // facility
        $facility = $this->logger->getLogFacility();
        $this->assertEqual($facility, 'echo'); // not array, but string (for B.C.)

        // level
        $level = $this->getNonpublicProperty($this->logger, 'level');
        $level_echo = $level['echo'];
        $this->assertEqual($level_echo, LOG_WARNING);

        // option
        $option = $this->getNonpublicProperty($this->logger, 'option');
        $option_echo = $option['echo'];
        $this->assertEqual($option_echo['pid'], true);
        $this->assertEqual($option_echo['function'], true);
        $this->assertEqual($option_echo['pos'], true);
    }

    /**
     *  structured style log setting.
     */
    function test_parseSetting_Structured()
    {
        $config = array(
            'log' => array(
                'echo'  => array(
                    'level'         => 'warning',
                ),
                'file'  => array(
                    'level'         => 'notice',
                    'file'          => '/var/log/Ethna.log',
                    'mode'          => 0666,
                ),
                'alertmail'  => array(
                    'level'         => 'err',
                    'mailaddress'   => 'alert@ml.example.jp',
                ),
            ),
            'log_option'            => 'pid,function,pos',
        );
        $this->_resetLoggerSetting($config);
        $this->logger = $this->ctl->getLogger();

        // facility
        $facility = $this->logger->getLogFacility();
        $this->assertEqual($facility, array('echo', 'file', 'alertmail'));

        // level
        $level = $this->getNonpublicProperty($this->logger, 'level');
        $level_echo = $level['echo'];
        $this->assertEqual($level_echo, LOG_WARNING);
        $level_file = $level['file'];
        $this->assertEqual($level_file, LOG_NOTICE);
        $level_alertmail = $level['alertmail'];
        $this->assertEqual($level_alertmail, LOG_ERR);

        // option
        $option = $this->getNonpublicProperty($this->logger, 'option');

        $option_echo = $option['echo'];
        $this->assertEqual($option_echo['pid'], true);
        $this->assertEqual($option_echo['function'], true);
        $this->assertEqual($option_echo['pos'], true);

        $option_file = $option['file'];
        $this->assertEqual($option_file['pid'], true);
        $this->assertEqual($option_file['function'], true);
        $this->assertEqual($option_file['pos'], true);
        $this->assertEqual($option_file['file'], '/var/log/Ethna.log');
        $this->assertEqual($option_file['mode'], 0666);

        $option_alertmail = $option['alertmail'];
        $this->assertEqual($option_alertmail['pid'], true);
        $this->assertEqual($option_alertmail['function'], true);
        $this->assertEqual($option_alertmail['pos'], true);
        $this->assertEqual($option_alertmail['mailaddress'], 'alert@ml.example.jp');
    }

    /**
     *  @todo   log level filter, begin(), log(), end()
     */
    //function test_etcetc()
    //{
    //    // not implemented yet.
    //}
}

class Ethna_Logger_Test_Config extends Ethna_Config
{
    public function __construct_Config()
    {
        // do nothing.
    }
}
