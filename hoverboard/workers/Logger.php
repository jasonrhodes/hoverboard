<?php

namespace hoverboard\workers;

class Logger {

	protected $engine;
	protected static $loggers = array();

	protected function __construct($logEngine = null)
	{
		$this->setEngine($logEngine);
	}

	/**
	 * @method createInstance
	 *
	 * This method makes it easy to see where
	 * a logger instance is first being created without confusing
	 * people by using getInstance to a logger that doesn't exist.
	 * 
	 * Note: getInstance() WILL WORK for this, as you can see by how
	 * createInstance() calls it directly, but this method name is
	 * clearer as to the programmer's intention.
	 */
	public static function createInstance($name = "default", $logEngine)
	{
		return static::getInstance($name, $logEngine);
	}

	public static function getInstance($name = "default", $logEngine = null)
	{
		if (isset(static::$loggers[$name]) && is_object(static::$loggers[$name])) {
			return static::$loggers[$name];
		}
		return static::$loggers[$name] = new self($logEngine);
	}

	public function setEngine($logEngine) 
	{
		if (is_object($logEngine)) {
			$this->engine = $logEngine;
		}
	}

	public function setFileHandler($path, $minimumLevel = \Monolog\Logger::DEBUG, $bubble = false)
	{
		if (!file_exists($path)) {
			throw new Exception("Error log file at " . $path . " doesn't appear to exist.");
		}
		$this->engine->pushHandler(new \Monolog\Handler\StreamHandler($path, $minimumLevel, $bubble));
	}

	public function setEmailHandler($mailer, $message, $minimumLevel = null, $bubble = null)
	{
		$this->engine->pushHandler(new \Monolog\Handler\SwiftMailerHandler($mailer, $message, $minimumLevel, $bubble));
	}

	public function setBufferedHandler()
	{

	}

	public function __call($method, $args)
	{
		// If it's an addDebug() or addInfo() style method, we can
		// check to make sure the action is an approved action for the logger
		// and if so, just call the logger function directly.
		$approvedLevels = array("debug", "info", "notice", "warning", "error", "critical", "alert");
		$startsWithAdd = strpos($method, "add") === 0;
		$errorLevel = strtolower(substr($method, 3));
		$hasApprovedLevel = in_array($errorLevel, $approvedLevels);
		if ($startsWithAdd && $hasApprovedLevel) {
			$method = "add" . ucwords($errorLevel);
			call_user_func_array(array($this->engine, $method), $args);
		}
		else {
			echo "No can do, bucko + " . $method;
			echo "<br>";
			var_dump(strpos($method, "add"));
			echo "<br>";
			var_dump(substr($method, 3));
		}
	}

}