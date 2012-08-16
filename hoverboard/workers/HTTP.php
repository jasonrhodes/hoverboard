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
		$returned = call_user_func_array(array($this->engine, $method), $args);
		return !is_null($returned) ? $returned : $this;
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

		// apc_delete($this->requestCacheKey);
		
		if ($cached = $this->requestIsCached()) {

			if (!is_object($cached) && substr($cached, 0, 5) == "<?xml") {
				$cached = simplexml_load_string($cached);
			} 
			
			$this->response = $cached;

		} else {
			$this->response = $this->engine->get($this->request);
			$body = $this->response["body"];

			if (is_object($cached) && get_class($body) == "SimpleXMLElement") {
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