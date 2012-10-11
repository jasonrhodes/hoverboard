<?php

namespace hoverboard\workers;

class DrupalDataMiner
{
	public static $targets = array(
		"safe_value" => "", 
		"value" => "", 
		"target_id" => "", 
		"tid" => "", 
		"fid" => ""
	);

	public function dig(array $data)
	{
		$dug = array();

		foreach ($data as $node) {
			foreach ($node as $key => $value) {
				// echo $key . "<br>";
				$dug[$this->cleanKey($key)] = $this->extract($value);
			}
		}
		return $dug;
	}

	public function cleanKey($key)
	{
		if (substr($key, 0, 6) == "field_") {
			$key = substr($key, 6);
		}

		if ($key == "nid") {
			$key = "id";
		}

		if ($key == "title") {
			$key = "slug";
		}

		if ($key == "created") {
			$key = "publish_date";
		}

		if ($key == "related_items") {
			$key = "related_articles";
		}

		if ($key == "summary") {
			$key = "excerpt";
		}

		return $key;
	}

	public function extract($value, &$parent = array())
	{
		// Return the value if it's not an array
		if (!is_array($value)) {
			return $value;
		}

		// Find any keys in the current level of the array that match our targeted keys
		$matching = array_keys(array_intersect_key(static::$targets, $value));

		// If we found any matching target keys
		if (!empty($matching)) {
			// If the parent array has more than one child (ie this value has siblings)
			if (count($parent) > 1) {
				$results = array();
				// Return all of the matching target values in each of the siblings
				foreach ($parent as $child) {
					$results[] = $child[$matching[0]];
				}
				return $results;
			} else {
				// Otherwise just return this matching value
				return $value[$matching[0]];
			}
		} else {
			// Save off the current array to pass as the parent
			$parent = $value;			
			// Recursively call this function on the next child array
			return $this->extract(array_shift($value), $parent);
		}
	}

}