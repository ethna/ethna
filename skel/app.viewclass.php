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

    /** @var  string  set layout template file   */
    var $_layout_file = 'layout';

    /**#@+
     *  @access public
     */

    /** @var boolean  layout template use flag   */
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
