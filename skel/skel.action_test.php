<?php
/**
 *  {$action_path}
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  {$action_name}フォームのテストケース
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$action_form}_TestCase extends Ethna_UnitTestCase
{
    /**
     *  @access private
     *  @var    string  アクション名
     */
    var $action_name = '{$action_name}';

    /**
     *    テストの初期化
     *
     *    @access public
     */
    function setUp()
    {
        $this->createActionForm();  // アクションフォームの作成
    }

    /**
     *    テストの後始末
     *
     *    @access public
     */
    function tearDown()
    {
    }

    /**
     *  {$action_name}アクションフォームのサンプルテストケース
     *
     *  @access public
     */
    /*
    function test_formSample()
    {
        // フォームの設定
        $this->af->set('id', 1);

        // {$action_name}アクションフォーム値検証
        $this->assertEqual($this->af->validate(), 0);
    }
    */
}

/**
 *  {$action_name}アクションのテストケース
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$action_class}_TestCase extends Ethna_UnitTestCase
{
    /**
     *  @access private
     *  @var    string  アクション名
     */
    var $action_name = '{$action_name}';

    /**
     *    テストの初期化
     *
     *    @access public
     */
    function setUp()
    {
        $this->createActionForm();  // アクションフォームの作成
        $this->createActionClass(); // アクションクラスの作成

        $this->session->start();            // セッションの開始
    }

    /**
     *    テストの後始末
     *
     *    @access public
     */
    function tearDown()
    {
        $this->session->destroy();      // セッションの破棄
    }

    /**
     *  {$action_name}アクションクラスのサンプルテストケース
     *
     *  @access public
     */
    /*
    function test_actionSample()
    {
        // フォームの設定
        $this->af->set('id', 1);

        // {$action_name}アクション実行前の認証処理
        $forward_name = $this->ac->authenticate();
        $this->assertNull($forward_name);

        // {$action_name}アクションの前処理
        $forward_name = $this->ac->prepare();
        $this->assertNull($forward_name);

        // {$action_name}アクションの実装
        $forward_name = $this->ac->perform();
        $this->assertEqual($forward_name, '{$action_name}');
    }
    */
}
?>
