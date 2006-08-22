<?php
// vim: foldmethod=marker
/**
 *  {$project_id}_ActionClass.php
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

// {{{ {$project_id}_ActionClass
/**
 *  action実行クラス
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @access     public
 */
class {$project_id}_ActionClass extends Ethna_ActionClass
{
    /**
     *  アクション実行前の認証処理を行う
     *
     *  @access public
     *  @return string  遷移名(nullなら正常終了, falseなら処理終了)
     */
    function authenticate()
    {
        return parent::authenticate();
    }

    /**
     *  アクション実行前の処理(フォーム値チェック等)を行う
     *
     *  @access public
     *  @return string  遷移名(nullなら正常終了, falseなら処理終了)
     */
    function prepare()
    {
        return parent::prepare();
    }

    /**
     *  アクション実行
     *
     *  @access public
     *  @return string  遷移名(nullなら遷移は行わない)
     */
    function perform()
    {
        return parent::perform();
    }
}
// }}}
?>
