<?php
// vim: foldmethod=marker
/**
 *	Ethna_Handle.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Handle
/**
 *  base class of Ethna (Command Line) Handlers
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Handle
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
    function Ethna_Handle($id)
    {
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
