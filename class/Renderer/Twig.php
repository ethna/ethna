<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Renderer_Twig extends Ethna_Renderer
{
    /** @var  Twig_Environment $engine */
    protected $engine;

    /** @var  Twig_Loader_FileSystem $loader */
    protected $loader;

    /** @var array $config */
    protected $config = array();

    protected $compile_dir;

    /**
     * @param Ethna_Controller $controller
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->setTemplateDir($controller->getTemplatedir());
        $this->setCompileDir($controller->getDirectory('template_c'));

        $this->loadEngine($this->config);
    }

    protected function setCompileDir($dir)
    {
        $this->compile_dir = $dir;
    }

    protected function getCompileDir()
    {
        return $this->compile_dir;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig';
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * render template
     *
     * @param null $template
     * @param bool $capture
     * @return bool|Ethna_Error|string
     */
    public function perform($template = null, $capture = false)
    {
        $content = $this->getEngine()->render($template, $this->prop);

        if ($capture === true) {
            return $content;
        } else {
            echo $content;
        }
    }

    /**
     * @return Twig_Environment
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $template
     * @return bool
     */
    public function templateExists($template)
    {
        return $this->loader->exists($template);
    }

    public function setPlugin($name, $type, $plugin)
    {
        return Ethna::raiseWarning("Twig render does not support plugin yet");
    }

    /**
     * setup twig
     *
     * @param array $config
     */
    protected function loadEngine(array $config)
    {
        $this->loader = new Twig_Loader_Filesystem($this->getTemplateDir());
        $this->engine = new Twig_Environment($this->loader, array(
            'cache' => $this->getCompileDir(),
            'debug' => true,
        ));
    }

    public function getCompiledContent($file)
    {
        // TBD
    }
}