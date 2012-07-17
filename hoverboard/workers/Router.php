<?php

namespace hoverboard\workers;

class Router
{
	protected $engine;
	protected $routesPath;

	protected static $instances = array();
	protected $debug = false;


	public static function init($name = "default", $options = array())
	{
		if (!isset(static::$instances[$name])) {
			static::$instances[$name] = new self($options);
		}
		return static::$instances[$name];
	}


	public static function getInstance($name = "default")
	{
		if (isset(static::$instances[$name]) && get_class(static::$instances[$name]) === "hoverboard\workers\Router") {
			return static::$instances[$name];
		}
		throw new Exception("There's no router instance called {$name} (you need to run Router::init(\"{$name}\") to make it available.");
	}

	public function __construct($options = array())
	{
		extract($options);
		$this->engine = $engine;

		// Set default routes path
		$this->setRoutesPath(APP_DIR . "/config/routes.php");

		if (isset($routesPath) && file_exists($routesPath)) {
			$this->routesPath = $routesPath;
		}

		if (isset($debug)) {
			$this->debug = $debug;
		}
	}


	public function setRoutesPath($path)
	{
		if (file_exists($path)) {
			$this->routesPath = $path;
		}
	}

	public function start()
	{
		include $this->routesPath;
	}

	/**
	 * If this router object doesn't contain a called method, try to call that method
	 * on the router object directly using call_user_func_array()
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->engine, $method), $args);
	}

	// public static function setEngine($routerEngine)
	// {
	// 	static::$engine = $routerEngine;
	// 	static::$request = $routerEngine->request();
	// 	static::$response = $routerEngine->response();
	// }


	public function set($route, $function)
	{
		return $this->engine->map($route, $function)->via("GET", "POST");
	}


	// public static function run()
	// {
	// 	return static::$engine->run();
	// }


	public function dispatch($controller, $action = null, $options = array() )
	{
		$controllerName = "\app\controllers\\" . ucwords($controller) . "Controller";
		new $controllerName($action, $options, $this);
	}

	public function debug()
	{
		return $this->debug;
	}
	
}