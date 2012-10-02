<?php

namespace hoverboard\workers;

class HTTP
{
	protected $engine;
	protected $endpoint;
	protected $queryStringParams = array();
	protected $request;
	protected $requestCacheKey;
	protected $response;

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
		$this->queryStringParams[] = "{$key}={$value}";
		return $this;
	}


	public function get()
	{
		$this->request = $cacheKey = $this->endpoint;
		if (!empty($this->queryStringParams)) {
			$this->request .= "?" . implode("&", $this->queryStringParams);
			asort($this->queryStringParams);
			$cacheKey .= "?" . implode("&", $this->queryStringParams);
		}

		$this->requestCacheKey = md5($cacheKey);

		$cached = $this->requestIsCached();

		if ($cached !== false) {
			if (is_string($cached) && substr($cached, 0, 5) == "<?xml") {
				$cached = simplexml_load_string($cached);
			} 
			$this->response = $cached;

		} else {
			$this->response = $this->engine->get($this->request);
			$this->response = $this->response["body"];
			$body = $this->response;

			if (is_object($body) && get_class($body) == "SimpleXMLElement") {
				$body = $body->asXML();
			}

			$this->cache($body);
		}

		return $this->response;
	}


	public function requestIsCached()
	{
		return apc_fetch($this->requestCacheKey);
	}


	public function cache($result)
	{
		return apc_store($this->requestCacheKey, $result, 120);
	}


	public function getLastResponse()
	{
		return $this->response;
	}

}