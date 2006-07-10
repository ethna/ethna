<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle
/**
 *  コマンドラインハンドラプラグインの基底クラス
 *  
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle
{
    /** @var    handler's id */
    var $id;

    /** @var    command line arguments */
    var $arg_list;

    /**
     *  Ethna_Handle constructor (stub for php4)
     *
     *  @access public
     */
    function Ethna_Plugin_Handle(&$controller, $type, $name)
    {
        $id = $name;
        $id = preg_replace('/^([A-Z])/e', "strtolower('\$1')", $id);
        $id = preg_replace('/([A-Z])/e', "'-' . strtolower('\$1')", $id);
        $this->id = $id;
    }

    /**
     *  get handler-id
     *
     *  @access public
     */
    function getId()
    {
        return $this->id;
    }

    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "description of " . $this->id;
    }

    /**
     *  set arguments
     *
     *  @access public
     */
    function setArgList($arg_list)
    {
        $this->arg_list = $arg_list;
    }

    /**
     *  just perform
     *
     *  @access public
     */
    function perform()
    {
    }

    /**
     *  show usage
     *
     *  @access public
     */
    function usage()
    {
    }
}
// }}}
?>
