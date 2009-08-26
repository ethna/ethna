<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Filter_{$plugin_name}
 *
 *  @author     your name <yourname@example.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna_Plugin
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Filter_{$plugin_name}
/**
 *  Filter Plugin Class {$plugin_name}.
 *
 *  @author     yourname <yourname@example.com>
 *  @access     public
 *  @package    Ethna_Plugin 
 */
class Ethna_Plugin_Filter_{$plugin_name} extends Ethna_Plugin_Filter
{
    /**
     *  filter before first processing.
     *
     *  @access public
     */
    function preFilter()
    {
    }

    /**
     *  filter BEFORE executing action.
     *
     *  @access public
     *  @param  string  $action_name  Action name.
     *  @return string  null: normal.
     *                string: if you return string, it will be interpreted
     *                        as Action name which will be executed immediately.
     */
    function preActionFilter($action_name)
    {
    }

    /**
     *  filter AFTER executing action.
     *
     *  @access public
     *  @param  string  $action_name    executed Action name.
     *  @param  string  $forward_name   return value from executed Action.
     *  @return string  null: normal.
     *                string: if you return string, it will be interpreted
     *                        as Forward name.
     */
    function postActionFilter($action_name, $forward_name)
    {
    }

    /**
     *  filter which will be executed at the end.
     *
     *  @access public
     */
    function postFilter()
    {
    }
}
// }}}
