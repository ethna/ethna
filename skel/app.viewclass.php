<?php
// vim: foldmethod=marker
/**
 *  {$project_id}_ViewClass.php
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

// {{{ {$project_id}_ViewClass
/**
 *  View class.
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @access     public
 */
class {$project_id}_ViewClass extends Ethna_ViewClass
{
    /**#@+
     *  @access protected
     */

    /** @var  string レイアウト(HTMLの外枠を記述するファイル)のテンプレートファイルを指定   */
    var $_layout_file = 'layout.tpl';

    /**#@+
     *  @access public
     */

    /** @var boolean  レイアウトテンプレートの使用フラグ       */
    var $use_layout = true;

    /**
     *  set common default value.
     *
     *  @access protected
     *  @param  object  {$project_id}_Renderer  Renderer object.
     */
    function _setDefault(&$renderer)
    {
    }

}
// }}}

?>
