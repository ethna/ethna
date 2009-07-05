<?php
/**
 *  Abstract.php
 *
 *  @author     Sotaro Karasawa <sotaro.k /at/ gmail.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Abstract
/**
 *  The abstract class of all plugins.
 *
 *  @author     Sotaro Karasawa <sotaro.k /at/ gmail.com>
 *  @access     public
 *  @package    Ethna
 */
// abstract class Ethna_Plugin_Abstract
class Ethna_Plugin_Abstract
{
    /**#@+
     *  @access private
     */

    var $controller;

    /** @var    object  Ethna_Controller    Controller Object */
    var $ctl;

    /** @var    object  Ethna_Config        Configure Object */
    var $config;

    /** @var    object  Ethna_Logger        Logger Object */
    var $logger;

    /**
     *  Constructor
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    Controller Object
     */
    // function __construct(&$controller)
    function Ethna_Plugin_Abstract(&$controller)
    {
        $this->controller =& $controller;
        $this->ctl =& $this->controller;

        $this->config =& $controller->getConfig();
        $this->logger =& $this->controller->getLogger();
    }

}
