<?php

namespace hoverboard\adapters;
use \Twig_Loader_Filesystem;
use \Twig_Environment;

class Twig implements interfaces\TemplateEngine
{
	protected $twig;


	public function __construct($viewsDirectory, $options = array())
	{
		$loader = new Twig_Loader_Filesystem($viewsDirectory);
		$this->twig = new Twig_Environment($loader, $options);
	}


	public function render($templatePath, $data)
	{
		$templatePath .= ".twig";
		$template = $this->twig->loadTemplate($templatePath);
		echo $template->render($data);
	}


	public function loadTemplate($templatePath)
	{
		return $this->twig->loadTemplate($templatePath);
	}
}