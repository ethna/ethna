<?php
/**
 *  UnitTestManager.php
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once 'simpletest/unit_tester.php';
require_once 'UnitTestCase.php';
require_once 'UnitTestReporter.php';

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
     *  @param  object  Ethna_Backend   $backend   Ethna_Backendオブジェクト
     */
    public function __construct($backend)
    {
        parent::__construct($backend);
        $this->ctl = Ethna_Controller::getInstance();
        $this->class_factory = $this->ctl->getClassFactory();
        $this->testcase = array_merge($this->testcase, $this->_getTestCaseList());
    }

    /**
     *  action, view 以外のテストケースの一覧を取得する
     *
     *  @access private
     *  @param  テストケースが含まれているディレクトリ名
     */
    function _getTestCaseList($test_dir = NULL)
    {
        $r = array();

        if (is_null($test_dir)) {
            $test_dir = $this->ctl->getTestdir();
        }
        $base = $this->ctl->getBasedir();

        //  テストディレクトリはユーザが変更できる
        //  ため、実行時の変更のタイミング次第では
        //  WARNING が出る可能性があるのをケアする
        if (!is_dir($test_dir)) {
            return array();
        }

        $child_dir_list = array();

        $dh = opendir($test_dir);
        if ($dh == false) {
            return;
        }

        $ext = $this->ctl->getExt('php');
        while (($file = readdir($dh)) !== false) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $file = $test_dir . $file;

            if (is_dir($file)) {
                $child_dir_list[] = $file;
                continue;
            }

            if (preg_match("/\.$ext\$/", $file) == 0) {
                continue;
            }

            $file = str_replace($this->ctl->getTestdir(), '', $file);

            $key = ereg_replace("^(.*)Test\.$ext", '\1', $file);
            $key = str_replace('/', '', $key);

            $r[$key] = str_replace($base . '/', '', $this->ctl->getTestdir() . $file);
        }

        closedir($dh);

        foreach ($child_dir_list as $child_dir) {
            $tmp = $this->_getTestCaseList($child_dir . "/");
            $r = array_merge($r, $tmp);
        }

        return $r;
    }

    /**
     *  定義済みアクション一覧を取得する
     *
     *  @access public
     *  @return array   アクション一覧
     */
    function _getActionList()
    {
        $im = new Ethna_InfoManager($this->backend);
        return $im->getActionList();
    }

    /**
     *  クラス名からビュー名を取得する
     *
     *  @access public
     *  @param  string  $class_name     ビュークラス名
     *  @return string  アクション名
     */
    function viewClassToName($class_name)
    {
        $prefix = sprintf("%s_View_", $this->ctl->getAppId());
        if (preg_match("/$prefix(.*)/", $class_name, $match) == 0) {
            // 不明なクラス名
            return null;
        }
        $target = $match[1];

        $action_name = substr(preg_replace('/([A-Z])/e', "'_' . strtolower('\$1')", $target), 1);

        return $action_name;
    }

    /**
     *  指定されたクラス名を継承しているかどうかを返す
     *
     *  @access private
     *  @param  string  $class_name     チェック対象のクラス名
     *  @param  string  $parent_name    親クラス名
     *  @return bool    true:継承している false:いない
     */
    function _isSubclassOf($class_name, $parent_name)
    {
        while ($tmp = get_parent_class($class_name)) {
            if (strcasecmp($tmp, $parent_name) == 0) {
                return true;
            }
            $class_name = $tmp;
        }
        return false;
    }

    /**
     *  ビュースクリプトを解析する
     *
     *  @access private
     *  @param  string  $script ファイル名
     *  @return array   ビュークラス定義一覧
     */
    function __analyzeViewScript($script)
    {
        $class_list = array();

        $source = "";
        $fp = fopen($script, 'r');
        if ($fp == false) {
            return null;
        }
        while (feof($fp) == false) {
            $source .= fgets($fp, 8192);
        }
        fclose($fp);

        // トークンに分割してクラス定義情報を取得
        $token_list = token_get_all($source);
        for ($i = 0; $i < count($token_list); $i++) {
            $token = $token_list[$i];

            if ($token[0] == T_CLASS) {
                // クラス定義開始
                $i += 2;
                $class_name = $token_list[$i][1];       // should be T_STRING
                if ($this->_isSubclassOf($class_name, 'Ethna_ViewClass')) {
                    $view_name = $this->viewClassToName($class_name);
                    $class_list[$view_name] = array(
                        'template_file' => $this->ctl->_getForwardPath($view_name),
                        'view_class' => $class_name,
                        'view_class_file' => $this->ctl->getDefaultViewPath($view_name),
                    );
                }
            }
        }

        if (count($class_list) == 0) {
            return null;
        }
        return $class_list;
    }

    /**
     *  ディレクトリ以下のビュースクリプトを解析する
     *
     *  @access private
     *  @param  string  $action_dir     解析対象のディレクトリ
     *  @return array   ビュークラス定義一覧
     */
    function __analyzeViewList($view_dir = null)
    {
        $r = array();

        if (is_null($view_dir)) {
            $view_dir = $this->ctl->getViewdir();
        }
        $prefix_len = strlen($this->ctl->getViewdir());

        $ext = '.' . $this->ctl->getExt('php');
        $ext_len = strlen($ext);

        $dh = opendir($view_dir);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                $path = "$view_dir/$file";
                if ($file != '.' && $file != '..' && is_dir($path)) {
                    $tmp = $this->__analyzeViewList($path);
                    $r = array_merge($r, $tmp);
                    continue;
                }
                if (substr($file, -$ext_len, $ext_len) != $ext) {
                    continue;
                }

                include_once($path);
                $class_list = $this->__analyzeViewScript($path);
                if (is_null($class_list) == false) {
                    $r = array_merge($r, $class_list);
                }
            }
        }
        closedir($dh);

        return $r;
    }

    /**
     *  定義済みビュー一覧を取得する
     *
     *  @access public
     *  @return array   ビュー一覧
     */
    function _getViewList()
    {
        $im = new Ethna_InfoManager($this->backend);
        //$view_class_list = array_keys($im->getForwardList());

        $r = array();

        // テンプレート/ビュースクリプトを解析する
        $forward_list = $im->_analyzeForwardList();
        $view_list = $this->__analyzeViewList();

        // ビュー定義エントリ一覧
        $manifest_forward_list = $im->_getForwardList_Manifest($forward_list);

        // ビュー定義省略エントリ一覧
        $implicit_forward_list = $im->_getForwardList_Implicit($forward_list, $manifest_forward_list);

        $r = array_merge($view_list, $manifest_forward_list, $implicit_forward_list);
        ksort($r);

        return $r;
    }

    /**
     *  アクションテストクラスを取得する
     *
     *  @access private
     *  @return array
     */
    function _getTestAction()
    {
        $action_class_list = array_keys($this->_getActionList());

        // テストの存在するアクション
        foreach ($action_class_list as $key => $action_name) {
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
        $view_class_list = array_keys($this->_getViewList());

        // テストの存在するビュー
        foreach ($view_class_list as $key => $view_name) {
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

        $test = new TestSuite("Ethna UnitTest");

        // アクション
        foreach ($action_class_list as $action_name) {
            $action_class = $this->ctl->getDefaultActionClass($action_name, false).'_TestCase';
            $action_form = $this->ctl->getDefaultFormClass($action_name, false).'_TestCase';

            $test->add(new $action_class($this->ctl));
            $test->add(new $action_form($this->ctl));
        }

        // ビュー
        foreach ($view_class_list as $view_name) {
            $view_class = $this->ctl->getDefaultViewClass($view_name, false).'_TestCase';

            $test->add(new $view_class($this->ctl));
        }

        // 一般
        foreach ($this->testcase as $class_name => $file_name) {
            $dir = $this->ctl->getBasedir().'/';
            include_once $dir . $file_name;
            $testcase_name = $class_name.'_TestCase';
            $test->add(new $testcase_name($this->ctl));
        }

        // ActionFormのバックアップ
        $af = $this->ctl->getActionForm();

        //出力したい形式にあわせて切り替える
        $cli_enc = $this->ctl->getClientEncoding();
        $reporter = new Ethna_UnitTestReporter($cli_enc);
        $test->run($reporter);

        // ActionFormのリストア
        $this->ctl->setActionForm($af);
        $this->backend->setActionForm($af);

        return array($reporter->report, $reporter->result);
    }
}
