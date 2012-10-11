<?php

namespace hoverboard\workers;

class DomainMapper
{
	protected $map;

	public function __construct($mapJSON)
	{
		$this->map = json_decode($mapJSON);
	}

}