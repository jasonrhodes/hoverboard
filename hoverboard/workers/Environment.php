<?php

namespace hoverboard\workers;

class Environment {

	public static function define($subdomainMap = array())
	{
		if (!defined("ENVIRONMENT")) {
		    # First check the Apache environment
		    $env = getenv("APPLICATION_ENV");

		    # If it hasn't been set, getenv() will return false, so fall back
		    if (!$env) {
		        $env = "production";
		        
		        $host = explode(".", $_SERVER["HTTP_HOST"]);
		        $key = $host[0];
		        if (array_key_exists($key, $subdomainMap)) {
		            $env = strtolower($subdomainMap[$key]);
		        }
		    }

		    define("ENVIRONMENT", $env);
		}
		
		return ENVIRONMENT;
	}

	public static function get()
	{
		if (!defined("ENVIRONMENT")) {
			static::define();
		}

		return ENVIRONMENT;
	}

}