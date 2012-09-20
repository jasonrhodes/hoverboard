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

	static public function setDebug($message)
	{
		static::$client->captureMessage($message);
	}
}