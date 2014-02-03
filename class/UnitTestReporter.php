<?php
/**
 *  UnitTestReporter.php
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once 'simpletest/scorer.php';

/**
 *  Ethnaマネージャクラス
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_UnitTestReporter extends SimpleReporter {
    
    var $_character_set;

    var $report;
    var $result;

    /**
     *  Ethna_UnitTestReporterのコンストラクタ
     *
     *  @access public
     *  @param  string  $character_set  キャラクタセット
     */
    public function __construct($character_set = 'UTF-8')
    {
        parent::__construct();
        $this->_character_set = $character_set;
        $this->report= array();
        $this->result= array();
    }

    /**
     *  結果
     *
     *  @access public
     *  @param string   $test_name  テスト名称
     */
    function paintFooter($test_name)
    {
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        $this->result = array(
            'TestCaseProgress' => $this->getTestCaseProgress(),
            'TestCaseCount' => $this->getTestCaseCount(),
            'PassCount' => $this->getPassCount(),
            'FailCount' => $this->getFailCount(),
            'ExceptionCount' => $this->getExceptionCount(),
        );
    }

    /**
     *  パス
     *
     *  @access public
     *　@param string   $message    メッセージ
     */
    function paintPass($message)
    {
        parent::paintPass($message);
            
        $test_list = $this->getTestList();
        $this->report[] = array(
            'type' => 'Pass',
            'test' => $test_list[2],
            'message' => $message,
        );
    }

    /**
     *  失敗
     *
     *  @access public
     *　@param string   $message    メッセージ
     */
    function paintFail($message)
    {
        parent::paintFail($message);

        $test_list = $this->getTestList();
        $this->report[] = array(
            'type' => 'Fail',
            'test' => $test_list[2],
            'message' => $message,
        );
    }

    /**
     *  例外
     *
     *  @access public
     *　@param string   $message    メッセージ
     */
    function paintException($message)
    {
        parent::paintException($message);

        $breadcrumb = $this->getTestList();
        $test = $breadcrumb[2];
        array_shift($breadcrumb);
        $this->report[] = array(
            'type' => 'Exception',
            'test' => $test,
            'breadcrumb' => $breadcrumb,
            'message' => $message,
        );
    }

    /**
     *  テストケース開始
     *
     *  @access public
     *  @param string   $test_name  テスト名称
     */
    function paintCaseStart($test_name)
    {
        parent::paintCaseStart($test_name);

        $this->report[] = array(
            'type' => 'CaseStart',
            'test_name' => $test_name,
        );
    }

    /**
     *  テストケース終了
     *
     *  @access public
     *  @param string   $test_name  テスト名称
     */
    function paintCaseEnd($test_name)
    {
        parent::paintCaseEnd($test_name);

        $this->report[] = array(
            'type' => 'CaseEnd',
        );
    }

    /**
     *  フォーマット済みメッセージ
     *
     *  @access public
     *　@param string   $message    メッセージ
     */
    function paintFormattedMessage($message)
    {
        $this->report[] = array(
            'type' => 'FormattedMessage',
            'message' => $this->_htmlEntities($message),
        );
    }

    /**
     *  HTMLエンティティ変換
     *
     *　@access protected
     *　@param string   $message    プレーンテキスト
     *　@return string              HTMLエンティティ変換済みメッセージ
     */
    function _htmlEntities($message)
    {
        return htmlentities($message, ENT_COMPAT, $this->_character_set);
    }
}
