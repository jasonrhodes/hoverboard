<?php

namespace hoverboard\workers;

class DIContainer
{	
	static protected $instances = array();

	protected $name;
	protected $registry = array();

	protected function __construct($name = "default")
	{
		$this->name = $name;
	}


	public static function getInstance($name = "default")
	{
		if (isset(static::$instances[$name])) {
			return static::$instances[$name];
		} else {
			$instance = new static($name);
			static::$instances[$name] = $instance;
			return $instance;
		}
	}

	public function add($name, $object)
	{
		$this->registry[$name] = $object;
	}

	public function get($name)
	{
		if (empty($this->registry[$name])) {
			throw new \Exception("There is no '{$name}' dependency registered");
		} else {
			return $this->registry[$name];
		}
	}
}