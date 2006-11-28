<?php
/**
 *  Ethna_UnitTestManager.php
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once 'simpletest/unit_tester.php';
require_once 'Ethna_UnitTestCase.php';
require_once 'Ethna_UnitTestReporter.php';

/**
 *  Ethnaユニットテストマネージャクラス
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_UnitTestManager extends Ethna_AppManager
{
    /** @var    object  Ethna_Controller    コントローラオブジェクト */
    var $ctl;

    /** @var    array                       一般テストケース定義 */
    var $testcase = array();

    /**
     *  Ethna_UnitTestManagerのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   &$backend   Ethna_Backendオブジェクト
     */
    function Ethna_UnitTestManager(&$backend)
    {
        parent::Ethna_AppManager($backend);
        $this->ctl =& Ethna_Controller::getInstance();
        $this->class_factory =& $this->ctl->getClassFactory();
    }

    /**
     *  アクションテストクラスを取得する
     *
     *  @access private
     *  @return array
     */
    function _getTestAction()
    {
        $em =& new Ethna_InfoManager($this->backend);
        $action_class_list = array_keys($em->getActionList());

        // テストの存在するアクション
        $action_dir = $this->ctl->getActiondir();
        foreach ($action_class_list as $key => $action_name) {
            
            $action_path = $this->ctl->getDefaultActionPath($action_name, false);
            if (!file_exists("$action_dir$action_path")) {
                unset($action_class_list[$key]);
                continue;
            }
            include_once $action_dir . $action_path;
            $action_class = $this->ctl->getDefaultActionClass($action_name, false).'_TestCase';
            if (!class_exists($action_class)) {
                unset($action_class_list[$key]);
            }
        }

        return $action_class_list;
    }

    /**
     *  ビューテストクラスを取得する
     *
     *  @access private
     *  @return array
     */
    function _getTestView()
    {
        $em =& new Ethna_InfoManager($this->backend);
        $view_class_list = array_keys($em->getForwardList());

        // テストの存在するビュー
        $view_dir = $this->ctl->getViewdir();
        foreach ($view_class_list as $key => $view_name) {

            $view_path = $this->ctl->getDefaultViewPath($view_name, false);
            if (!file_exists("$view_dir$view_path")) {
                unset($view_class_list[$key]);
                continue;
            }
            include_once $view_dir . $view_path;
            $view_class = $this->ctl->getDefaultViewClass($view_name, false).'_TestCase';
            if (!class_exists($view_class)) {
                unset($view_class_list[$key]);
            }
        }

        return $view_class_list;
    }

    /**
     *  ユニットテストを実行する
     *
     *  @access private
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function run()
    {
        $action_class_list = $this->_getTestAction();
        $view_class_list = $this->_getTestView();
        
        $test =& new GroupTest("Ethna UnitTest");

        // アクション
        foreach ($action_class_list as $action_name) {
            $action_class = $this->ctl->getDefaultActionClass($action_name, false).'_TestCase';
            $action_form = $this->ctl->getDefaultFormClass($action_name, false).'_TestCase';

            $test->addTestCase(new $action_class($this->ctl));
            $test->addTestCase(new $action_form($this->ctl));
        }

        // ビュー
        foreach ($view_class_list as $view_name) {
            $view_class = $this->ctl->getDefaultViewClass($view_name, false).'_TestCase';

            $test->addTestCase(new $view_class($this->ctl));
        }

        // 一般
        foreach ($this->testcase as $class_name => $file_name) {
            $dir = $this->ctl->getBasedir().'/';
            include_once $dir . $file_name;
            $testcase_name = $class_name.'_TestCase';
            $test->addTestCase(new $testcase_name($this->ctl));
        }

        // ActionFormのバックアップ
        $af =& $this->ctl->getActionForm();
        
        //出力したい形式にあわせて切り替える
        $reporter = new Ethna_UnitTestReporter();
        $test->run($reporter);

        // ActionFormのリストア
        $this->ctl->action_form =& $af;
        $this->backend->action_form =& $af;
        $this->backend->af =& $af;

        return array($reporter->report, $reporter->result);
    }
}
?>
