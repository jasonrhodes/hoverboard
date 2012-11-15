<?php

namespace hoverboard\workers;

class HTTP
{
	protected $engine;
	protected $endpoint;
	protected $queryStringParams = array();
	protected $request;
	protected $headerConstants = array();
	protected $headers = array();
	protected $response;

	public function __construct($engine, $headerConstants = array())
	{
		$this->engine = $engine;
		$this->headerConstants = $headerConstants;
	}

	/**
	 * If this HTTP object doesn't contain a called method, try to call that method
	 * directly on the engine using call_user_func_array()
	 */
	public function __call($method, $args)
	{
		if (substr($method, 0, 3) == "get") {
			$property = lcfirst(substr($method, 3));
			if (isset($this->$property)) {
				return $this->$property;
			}

		} else {
			$returned = call_user_func_array(array($this->engine, $method), $args);
			return !is_null($returned) ? $returned : $this;
		}
	}


	public function setEndpoint($endpoint)
	{
		$this->endpoint = $endpoint;
		return $this;
	} 


	public function addQueryStringParam($key, $value)
	{
		$this->queryStringParams[$key] = $value;
		return $this;
	}


	public function get()
	{
		$this->request = $this->endpoint;
	
		$this->response = $this->engine->get($this->request, $this->queryStringParams, $this->getHeaders());
		$this->response = $this->response["body"];

		$body = $this->response;

		if (is_object($body) && get_class($body) == "SimpleXMLElement") {
			$body = $body->asXML();
		}

		$this->queryStringParams = array();

		return $this->response;
	}

	protected function getHeaders()
	{
		return $this->headers + $this->headerConstants;
	}

	public function clearHeaders()
	{
		$this->headers = array();
	}


	public function getLastResponse()
	{
		return $this->response;
	}

}