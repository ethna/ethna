<?php
// vim: foldmethod=marker
/**
 *  Ethna_Handle.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Handle
/**
 *  Manager class of Ethna (Command Line) Handlers
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Handle
{
    /**#@+
     *  @access     private
     */

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /** @var    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    var $ctl;

    /** @var    object  Ethna_Pluguin       pluginオブジェクト */
    var $plugin;

    /**#@-*/

    /**
     *  Ethna_Handle constructor (stub for php4)
     *
     *  @access public
     */
    function Ethna_Handle()
    {
        $this->controller =& new Ethna_Controller();
        $this->controller->setGateway(GATEWAY_CLI);
        $this->ctl =& $this->controller;
        $this->plugin =& $this->controller->getPlugin();
    }

    /**
     *  get handler object
     *
     *  @access public
     */
    function &getHandler($id)
    {
        $name = preg_replace('/\-(.)/e', "strtoupper('\$1')", ucfirst($id));
        $handler =& $this->plugin->getPlugin('Handle', $name);
        if (Ethna::isError($handler)) {
            return $handler;
        }

        return $handler;
    }

    /**
     *  get an object list of all available handlers
     *
     *  @access public
     */
    function getHandlerList()
    {
        $handler_list = $this->plugin->getPluginList('Handle');
        usort($handler_list, array($this, "_handler_sort_callback"));

        return $handler_list;
    }

    /**
     *  sort callback method
     */
    function _handler_sort_callback($a, $b)
    {
        return strcmp($a->getId(), $b->getId());
    }
}
// }}}
?>
