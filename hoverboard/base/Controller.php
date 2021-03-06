<?php

namespace hoverboard\base;
use \hoverboard\base\View;
use \hoverboard\workers\Router;
use \hoverboard\workers\Logger;

class Controller
{
	protected $objectName;
	protected $model;
	protected $id;
	protected $router;

	protected $log;

	public function __construct($action, $options)
	{
		$this->router = Router::getInstance();
		$this->id = isset($options["id"]) ? $options["id"] : null;

		$this->log = Logger::getInstance();

		$this->objectName = str_replace("storefront\\app\\controllers\\", "", substr(get_called_class(), 0, -10));
		$modelName = "\\storefront\\app\\models\\" . $this->objectName;
		$this->model = new $modelName($options);
		View::setModel($this->model);
		View::setLogger($this->log);

		$action = $action ? $action : "index";

		$this->$action();
	}

	public function render($templateName, $addData = array())
	{
		echo View::render($templateName, $addData);
	}

	public function index()
	{
		// Gather data
		$this->model->create(array(
			"title" => "Topics: Arts & Culture's Effect",
			"stream" => "topics/arts-culture-effect",
			"template" => "topics"
		));

		// Render template?
		$this->render(strtolower($this->objectName) . "/index");
	}

}