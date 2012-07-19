<?php

namespace hoverboard\workers;
use \Phpass\Hash as Bcrypt;

class Hash
{
	protected static $hasher;
	
	public static function create($password)
	{
		return static::getHasher()->hashPassword($password);
	}

	public static function verify($password, $hash)
	{
		return static::getHasher()->checkPassword($password, $hash);
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