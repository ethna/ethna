<?php
// vim: foldmethod=marker
/**
 *	Ethna_Handle_Manager.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Handle_Manager
/**
 *  Manager class of Ethna (Command Line) Handlers
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Handle_Manager
{
    /**
     *  Ethna_Handle_Manager constructor (stub for php4)
     *
     *  @access public
     */
    function Ethna_Handle_Manager()
    {
    }

    /**
     *  get handler object
     *
     *  @access public
     */
    function &getHandler($id)
    {
        if ($id == 'manager') {
            return Ethna::raiseError("invalid id [$id]");
        }
        $file = sprintf("%s/%s", $this->_getHnalderDir(), $this->_getFileFromId($id));
        if (is_file($file) == false) {
            return Ethna::raiseError("no such file or directory [$file]");
        }

        include_once($file);

        $class_name = $this->_getClassFromId($id);
        if (class_exists($class_name) == false) {
            return Ethna::raiseError("no such class [$class_name]");
        }

        $handler =& new $class_name($id);

        return $handler;
    }

    /**
     *  get an object list of all available handlers
     *
     *  @access public
     */
    function getHandlerList()
    {
        $handler_dir = $this->_getHnalderDir();
        $dh = opendir($handler_dir);
        if ($dh === false) {
            return Ethna::raiseError("failed opening handlers' dir [$handler_dir]");
        }
        
        $handler_list = array();
        while (($file = readdir($dh)) !== false) {
            $id = $this->_getIdFromFile($file);
            if (Ethna::isError($id)) {
                // seems unknown type of files
                continue;
            }
            $handler =& $this->getHandler($id);
            if (Ethna::isError($handler)) {
                continue;
            }
            $handler_list[] = $handler;
        }
        closedir($dh);

        usort($handler_list, array($this, "_handler_sort_callback"));

        return $handler_list;
    }

    /**
     *  get dir of handlers
     *
     *  @access private
     */
    function _getHnalderDir()
    {
        return dirname(__FILE__);
    }

    /**
     *  get handler-id from file
     *
     *  @access private
     */
    function _getIdFromFile($file)
    {
        // simply check file name
        if (preg_match('/^Ethna_Handle_(\w+)\.php/', $file, $match) == 0) {
            return false;
        }
        $id = $match[1];
        $id = preg_replace('/^([A-Z])/e', "strtolower('\$1')", $id);
        $id = preg_replace('/([A-Z])/e', "'-' . strtolower('\$1')", $id);
        if ($id == 'manager') {
            // skip myself
            return false;
        }
        return $id;
    }

    /**
     *  get handler-file from id
     *
     *  @access private
     */
    function _getFileFromId($id)
    {
        $id = preg_replace('/\-(.)/e', "strtoupper('\$1')", ucfirst($id));
        return sprintf("Ethna_Handle_%s.php", $id);
    }

    /**
     *  get handler-classname from id
     *
     *  @access private
     */
    function _getClassFromId($id)
    {
        $id = preg_replace('/\-(.)/e', "strtoupper('\$1')", ucfirst($id));
        return sprintf("Ethna_Handle_%s", $id);
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
