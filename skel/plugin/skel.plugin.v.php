<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Validator_{$plugin_name}
 *
 *  @author     your name <yourname@example.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna_Plugin
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_{$plugin_name}
/**
 *  Validator Plugin Class {$plugin_name}.
 *
 *  @author     yourname <yourname@example.com>
 *  @access     public
 *  @package    Ethna_Plugin 
 */
class Ethna_Plugin_Validator_{$plugin_name} extends Ethna_Plugin_Validator 
{
    /** @var    bool    配列を受け取るかフラグ */
    var $accept_array = true;

    // {{{ perform
    /**
     *  Validate something
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    function &validate($name, $var, $params)
    {
        //
        //   sample
        //
        //$form_def = $this->getFormDef($name);
        //var_dump($form_def['type']);
        //if (empty($var)) {
        //    return Ethna::raiseNotice("empty!");
        //}
        return true;
    }
    // }}}
}
// }}}
?>
