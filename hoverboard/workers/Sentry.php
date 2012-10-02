<?php

namespace hoverboard\workers;
use \Raven\Autoloader;
use \Raven\Client;

class Sentry
{
	protected static $client;
	protected static $instances = array();

	public function __construct($sentryDSN)
	{
		\Raven_Autoloader::register();
		static::$client = new \Raven_Client($sentryDSN);
	}

	public static function init($sentryDSN, $name = "default")
	{
		if (!isset(static::$instances[$name])) {
			static::$instances[$name] = new self($sentryDSN);
		}
		return static::$instances[$name];
	}


	public static function getInstance($name = "default")
	{
		if (isset(static::$instances[$name]) && get_class(static::$instances[$name]) === "hoverboard\workers\Sentry") {
			return static::$instances[$name];
		}
		throw new \Exception("There's no Sentry instance called {$name} (you need to run Sentry::init(\"{$name}\") to make it available.");
	}

	/**
     * Send a debug message
     *
     * @param string $message Message to record in Sentry.
     * @param boolean $stack Whether to include a stacktrace in the log
     */
	static public function debug($message, $stack = true)
	{
		static::$client->captureMessage($message, array(), "debug", $stack);
	}

	/**
     * Send an info message
     *
     * @param string $message Message to record in Sentry.
     * @param boolean $stack Whether to include a stacktrace in the log
     */
	static public function info($message, $stack = true)
	{
		static::$client->captureMessage($message, array(), "info", $stack);
	}

	/**
     * Send a warning message
     *
     * @param string $message Message to record in Sentry.
     * @param boolean $stack Whether to include a stacktrace in the log
     */
	static public function warning($message, $stack = true)
	{
		static::$client->captureMessage($message, array(), "warning", $stack);
	}

	/**
     * Send an error message
     *
     * @param string $message Message to record in Sentry.
     * @param boolean $stack Whether to include a stacktrace in the log
     */
	static public function error($message, $stack = true)
	{
		static::$client->captureMessage($message, array(), "error", $stack);
	}

	/**
     * Send a fatal error message
     *
     * @param string $message Message to record in Sentry.
     * @param boolean $stack Whether to include a stacktrace in the log
     */
	static public function fatal($message, $stack = true)
	{
		static::$client->captureMessage($message, array(), "fatal", $stack);
	}
}