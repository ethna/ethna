<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Renderer_DummyRenderer extends Ethna_Renderer
{
    public $templates = array();

    function perform($template = null, $capture = false)
    {
        $this->templates[] = $template;
    }

    function getEngine()
    {
    }

    function getTemplateDir()
    {
    }

    function getProp($name)
    {
    }

    function removeProp($name)
    {
    }

    function setPropArray($array)
    {
    }

    function setPropArrayByRef(&$array)
    {
    }

    function setProp($name, $value)
    {
    }

    function setPropByRef($name, &$value)
    {
    }

    function setTemplate($template)
    {
    }

    function setTemplateDir($dir)
    {
    }

    function templateExists($template)
    {
        return true;
    }

    function setPlugin($name, $type, $plugin)
    {
    }

    function assign($name, $value)
    {
    }

    function assign_by_ref($name, &$value)
    {
    }

    function display($template = null)
    {
    }

    protected function loadEngine(array $config)
    {
    }

}