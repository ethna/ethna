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
abstract class Ethna_Plugin_Abstract
{
    /**#@+
     *  @access private
     */

    /** @var    string  plugin type */
    public $type = null;

    /** @var    string  plugin name */
    public $name = null;

    /** @var Ethna_Controller $controller   Controller Object */
    public $controller;
    public $ctl; /* Alias */

    /** @var Ethna_Backend $backend       Backend Object */
    public $backend;

    /** @var Ethna_ActionForm $action_form    ActionForm Object */
    public $action_form;
    public $af; /* Alias */

    /** @var  Ethna_Session $session       Session Object */
    public $session;

    /** @var    array   plugin configure */
    public $config;

    /** @var array   plugin configure for default */
    public $config_default = array();

    /** @var Ethna_Logger $logger        Logger Object */
    public $logger;

    /**
     *  Constructor
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    Controller Object
     */
    public function __construct($controller, $type = null, $name = null)
    {
        $this->controller = $controller;
        $this->ctl = $this->controller;

        $this->backend = $this->controller->getBackend();

        $this->logger = $controller->getLogger();

        $this->action_form = $controller->getActionForm();
        $this->af = $this->action_form;

        $this->session = $controller->getSession();

        // if constractor called without parameter $type or $name, auto detect type and name of self.
        if ($this->type === null) {
            $this->type = $this->_detectType($type);
        }

        if ($this->name === null) {
            $this->name = $this->_detectName($name);
        }

        // load config
        $this->_loadConfig();

        // load plugin hook
        $this->_load();
    }

    /**
     *  getType
     *
     *  @access public
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *  getType
     *
     *  @access public
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *  getConfig
     *
     *  @return array   $config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *  _load
     *
     *  @access protected
     */
    public function _load()
    {
    }

    /**
     *  _loadConfig
     *
     *  @access protected
     */
    public function _loadConfig()
    {
        $config = $this->ctl->getConfig();
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
     *  _detectType
     *
     *  @access protected
     */
    public function _detectType($type = null)
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
     *  _detectName
     *
     *  @access protected
     */
    public function _detectName($name = null)
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
