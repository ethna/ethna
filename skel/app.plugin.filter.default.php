<?php
/**
 *  Ethna_Plugin_Filter_ExecutionTime.php
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  filter plugin implementation for measuring execution time.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class Ethna_Plugin_Filter_ExecutionTime extends Ethna_Plugin_Filter
{
    /**#@+
     *  @access private
     */

    /**
     *  @var    int     Start time.
     */
    var $stime;

    /**#@-*/


    /**
     *  filter before first processing.
     *
     *  @access public
     */
    function preFilter()
    {
        $stime = explode(' ', microtime());
        $stime = $stime[1] + $stime[0];
        $this->stime = $stime;
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
        return null;
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
        return null;
    }

    /**
     *  filter which will be executed at the end.
     *
     *  @access public
     */
    function postFilter()
    {
        $etime = explode(' ', microtime());
        $etime = $etime[1] + $etime[0];
        $time   = round(($etime - $this->stime), 4);

        print "\n<!-- page was processed in $time seconds -->\n";
    }
}
