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

    /** @var    string  plugin type */
    var $type = null;

    /** @var    string  plugin name */
    var $name = null;

    /** @var    object  Ethna_Controller    Controller Object */
    var $controller;
    var $ctl; /* Alias */

    /** @var    object  Ethna_Backend       Backend Object */
    var $backend;

    /** @var    object  Ethna_ActionForm    ActionForm Object */
    var $action_form;
    var $af; /* Alias */

    /** @var    array   plugin configure */
    var $config;

    var $config_default = array();

    /** @var    object  Ethna_Logger        Logger Object */
    var $logger;

    /**
     *  Constructor
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    Controller Object
     */
    // function __construct(&$controller)
    function Ethna_Plugin_Abstract(&$controller, $type = null, $name = null)
    {
        $this->controller =& $controller;
        $this->ctl =& $this->controller;

        $this->backend =& $this->controller->getBackend();

        //$this->config =& $controller->getConfig();
        $this->logger =& $controller->getLogger();

        $this->action_form =& $controller->getActionForm();
        $this->af =& $this->action_form;


        // if constractor called without parameter $type or $name, auto detect type and name of self.
        if ($this->type === null) {
            $this->type = $this->_detectType($type);
        }

        if ($this->name === null) {
            $this->name = $this->_detectName($name);
        }

        // load plugin hook
        $this->_onLoad();
    }

    /**
     *  getType
     *
     *  @access public
     */
    function getType()
    {
        return $this->type;
    }

    /**
     *  getType
     *
     *  @access public
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *  getType
     *
     *  @access protected
     */
    function _onLoad()
    {
        $this->_loadConfig();
    }

    /**
     *  getType
     *
     *  @access protected
     */
    function _loadConfig()
    {
        $config =& $this->ctl->getConfig();
        $plugin_config = $config->get('plugin');

        if ($plugin_config === null || !isset($plugin_config[$this->type])
            || ($this->name !== null && !isset($plugin_config[$this->type][$this->name]))) {
            $this->config = $this->config_default;
        }
        else {
            if ($this->name === null) {
                $this->config = array_merge($this->config_default, $plugin_config[$this->type]);
            }
            else {

                $this->config = array_merge($this->config_default, $plugin_config[$this->type][$this->name]);
            }
        }

        return true;
    }

    /**
     *  getType
     *
     *  @access protected
     */
    function _detectType($type = null)
    {
        if ($type !== null) {
            return strtolower($type);
        }

        $type = array_shift(explode("_", str_replace("Ethna_Plugin_", "",  get_class($this))));
        if ($type !== "") {
            return strtolower($type);
        }
        else {
            return null;
        }
    }

    /**
     *  getType
     *
     *  @access protected
     */
    function _detectName($name = null)
    {
        if ($name !== null) {
            return strtolower($name);
        }

        $name = explode("_", str_replace("Ethna_Plugin_", "", get_class($this)));
        if (count($name) === 2) {
            return strtolower($name[1]);
        }
        else {
            return null;
        }
    }
}
