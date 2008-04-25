<?php
/**
 * {$file_path}
 * 
 * @author    {$author}
 * @package   {$project_id}.Test
 * @version   $Id$
 */

/**
 * {$name} TestCase 
 * 
 * @author    {$author}
 * @package   {$project_id}.Test
 * @version   1.0
 */
class {$name}_TestCase extends Ethna_UnitTestCase
{
    /**
     * テストの初期化
     * 
     * @access public
     */
    function setUp()
    {
        // TODO: テストに際しての初期化コードを記述してください
        // 例: テスト用のデータをDBから読み込む
    }
    
    /**
     * テストの後始末
     * 
     * @access public
     */
    function tearDown()
    {
        // TODO: テスト終了に際してのコードを記述してください
        // 例: テスト用のデータから開発用のデータに戻す
    }
    
    /**
     * サンプルのテストケース
     * 
     * @access public
     */
    function test_{$name}()
    {
        /**
         *  TODO: テストケースを記述して下さい。
         *  @see http://simpletest.org/en/first_test_tutorial.html
         *  @see http://simpletest.org/en/unit_test_documentation.html
         */
        $this->fail('No Test! write Test!');
    }
}

?>
