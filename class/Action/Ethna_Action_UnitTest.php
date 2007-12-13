<?php
/**
 *  Ethna_Action_UnitTest.php
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  __ethna_unittest__フォームの実装
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Form_UnitTest extends Ethna_ActionForm
{
    /**
     *  @access private
     *  @var    array   フォーム値定義
     */
    var $form = array(
    );
}

/**
 *  __ethna_unittest__アクションの実装
 *
 *  @author     Takuya Ookubo <sfio@sakura.ai.to>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Action_UnitTest extends Ethna_ActionClass
{
    /**
     *  __ethna_unittest__アクションの前処理
     *
     *  @access public
     *  @return string      Forward先(正常終了ならnull)
     */
    function prepare()
    {
        return null;
    }

    /**
     *  __ethna_unittest__アクションの実装
     *
     *  @access public
     *  @return string  遷移名
     */
    function perform()
    {
        return '__ethna_unittest__';
    }
}
?>
