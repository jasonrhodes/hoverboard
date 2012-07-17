<?php

namespace hoverboard\workers;

class HTTP
{
	protected $engine;
	protected $endpoint;
	protected $queryStringParams = array();
	protected $lastResponse;

	public function __construct($engine)
	{
		$this->engine = $engine;
	}

	/**
	 * If this HTTP object doesn't contain a called method, try to call that method
	 * directly on the engine using call_user_func_array()
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->engine, $method), $args);
	}


	public function setEndpoint($endpoint)
	{
		$this->endpoint = $endpoint;
		return $this;
	} 


	public function addQueryStringParam($key, $value)
	{
		$this->queryStringParams[] = "{$key}={$value}";
		return $this;
	}


	public function get()
	{
		return $this->lastResponse = $this->engine->get($this->endpoint . "?" . implode("&", $this->queryStringParams));
	}

}