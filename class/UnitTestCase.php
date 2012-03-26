<?php
/**
 *  UnitTestCase.php
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  UnitTestCase実行クラス
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_UnitTestCase extends UnitTestCase
{
    /** @var    object  Ethna_Backend       backendオブジェクト */
    var $backend;

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /** @var    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    var $ctl;

    /** @var    object  Ethna_Session       セッションオブジェクト */
    var $session;

    /** @var    string                      アクション名 */
    var $action_name;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト */
    var $action_form;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト($action_formの省略形) */
    var $af;

    /** @var    object  Ethna_ActionClass   アクションクラスオブジェクト */
    var $action_class;

    /** @var    object  Ethna_ActionClass   アクションクラスオブジェクト($action_classの省略形) */
    var $ac;

    /** @var    string                      ビュー名 */
    var $forward_name;

    /** @var    object  Ethna_ViewClass     viewクラスオブジェクト */
    var $view_class;

    /** @var    object  Ethna_ViewClass     viewクラスオブジェクト($view_classの省略形) */
    var $vc;

    /**
     *  Ethna_UnitTestCaseのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    コントローラオブジェクト
     */
    public function __construct($controller)
    {
        parent::__construct();

        // オブジェクトの設定
        $this->controller = $controller;
        $this->ctl = $this->controller;
        $this->backend = $this->ctl->getBackend();
        $this->session = $this->backend->getSession();

        // 変数の初期化
        $this->action_form = $this->af = null;
        $this->action_class = $this->ac = null;
        $this->view_class = $this->vc = null;
    }

    /**
     *  アクションフォームの作成と関連付け
     *
     *  @access public
     */
    function _createActionForm($form_name)
    {
        $this->action_form = new $form_name($this->ctl);
        $this->af = $this->action_form;

        // controler&backendにafを関連付け
        $this->ctl->action_name = $this->action_name;
        $this->ctl->setActionForm($this->af);
        $this->backend->setActionForm($this->af);

        // action_error, validator の初期化
        // これにより、直前のテスト結果をひきずらない
        // ようにする
        $ae = $this->ctl->getActionError();
        $ae->clear();
        $ae->clearActionForm();
        // FIXME: This is a protected property
        unset($this->ctl->class_factory->object['plugin']->obj_registry["Validator"]);
    }

    /**
     *  アクションフォームの作成
     *
     *  @access public
     */
    function createActionForm()
    {
        $form_name = $this->ctl->getActionFormName($this->action_name);
        $this->_createActionForm($form_name);
    }

    /**
     *  validateOneTime()
     *
     *  @access public
     *  @return int $result
     */
    function validateOneTime()
    {
        if ($this->af == null) {
            $this->createActionForm();
        }

        $result = $this->af->validate();
        $this->af->ae->clear();

        return $result;
    }

    /**
     *  単純なアクションフォームの作成
     *
     *  @access public
     */
    function createPlainActionForm()
    {
        $form_name = 'Ethna_ActionForm';
        $this->_createActionForm($form_name);
    }

    /**
     *  アクションの作成
     *
     *  @access public
     */
    function createActionClass()
    {
        if ($this->af == null) {
            $this->createActionForm();
        }

        // オブジェクト生成
        $action_class_name = $this->ctl->getActionClassName($this->action_name);
        $this->action_class = new $action_class_name($this->backend);
        $this->ac = $this->action_class;

        // backendにacを関連付け
        $this->backend->setActionClass($this->ac);
    }

    /**
     *  ビューの作成
     *
     *  @access public
     */
    function createViewClass()
    {
        if ($this->af == null) {
            $this->createPlainActionForm();
        }

        // オブジェクト生成
        $view_class_name = $this->ctl->getViewClassName($this->forward_name);
        $this->view_class = new $view_class_name($this->backend, $this->forward_name, $this->ctl->_getForwardPath($this->forward_name));
        $this->vc = $this->view_class;
    }
}
