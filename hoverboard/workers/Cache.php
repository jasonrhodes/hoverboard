<?php

namespace hoverboard\workers;

class Cache
{
	protected $requestCacheKey;

	public function fetch($key)
	{
		// apc_delete(md5($key));
		return apc_fetch(md5($key));
	}

	public function store($key, $data, $time = 5)
	{
		return apc_store(md5($key), $data, $time);
	}
}