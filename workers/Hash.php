<?php

namespace hoverboard\workers;
use \PHPassLib\Hash\Bcrypt;

class Hash
{
	protected static $hasher;
	
	public static function create($password)
	{
		return static::getHasher()->hash($password);
	}

	public static function verify($password, $hash)
	{
		return static::getHasher()->verify($password, $hash);
	}

	protected static function getHasher()
	{
		if (is_object(static::$hasher) && get_class(static::$hasher) == "\PHPassLib\Hash\BCrypt") {
			return static::$hasher;
		}
		$hasher = new BCrypt;
		static::$hasher = $hasher;
		return $hasher;
	}
	
}