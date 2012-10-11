<?php

namespace hoverboard\workers;

class DrupalDataMiner
{
	public function dig(array $data)
	{
		$diamonds = array();

		foreach ($data as $node) {
			$diamond = new \stdClass;
			foreach ($node as $key => $value) {
				$diamond->$key = $this->extract($value);
			}
			$diamonds[] = $diamond;
		}
		
		return $diamonds;
	}

    protected function extract($value)
    {
    	// text field labels we want to grab in order of precedence
    	$textLabels = array("safe_value", "value", "target_id");
    	$termLabel = "tid";
    	$fileLabel = "fid";

    	if (!is_array($value)) {
    		return $value;
    	}

		// get inside the "und" array where all the goods are
		$value = $value["und"];

		// text field
		if (count($value == 1) && !isset($value[0][$fileLabel]) && !isset($value[0][$termLabel])) {

			$value = $value[0];

			// loop through our prefered labels until we find a match
			foreach ($textLabels as $label) {
				if (isset($value[$label])) {
					return $value[$label];
				}
			}

		// file(s)
		} elseif (isset($value[0]["fid"])) {
			$files = array();
			foreach($value as $k => $v) {
				$files[] = $v["fid"];
			}
			return $files;

		// image(s)
		} elseif (isset($value[0]["tid"])) {
			$terms = array();
			foreach($value as $k => $v) {
				$terms[] = $v["tid"];
			}
			return $terms;
		} else {
			return "we missed something.";
		}
    }
}