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

require_once 'Console/Getopt.php';

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
     *  get handler's usage
     *
     *  @access public
     */
    function getUsage()
    {
        return "usage of " . $this->id;
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
     * easy getopt :)
     * 
     * @param   array   $long_options 
     * @access  protected
     * @return  array   list($opts, $args)
     */
    function _getopt($long_options = array())
    {
        $long_options = to_array($long_options);
        $short_options = '';
        foreach ($long_options as $lopt) {
            $short_options .= $lopt{0};
            if ($lopt{strlen($lopt) - 1} == '=') {
                $short_options .= ':';
            }
            if ($lopt{strlen($lopt) - 2} == '=') {
                $short_options .= ':';
            }
        }
        $getopt =& new Console_Getopt();
        return $getopt->getopt2($this->arg_list, $short_options, $long_options);
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
        echo "usage:\n";
        echo $this->getUsage() . "\n\n";
    }
}
// }}}
?>
