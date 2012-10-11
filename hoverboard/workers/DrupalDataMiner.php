<?php

namespace hoverboard\workers;

class DrupalDataMiner
{
	public function dig(array $data)
	{
		$diamonds = array();

		print_r($data); die();

		foreach ($data as $node) {
			$diamond = new \stdClass;

			print_r($node); die();

			foreach ($node as $key => $value) {
				$diamond->$key = $this->extract($value);
			}

			$diamonds[] = $diamond;
		}

		print_r($diamonds); die();
		
		return $diamonds;
	}

	/**
     * Quick extraction of the standard Drupal nested array
     *
     * @param array $value
     * @param string $label (optional) defaults to "safe_value"
     *
     */
    protected function extract($value, $label = "safe_value") 
    {
    	if (!is_array($value)) {
    		return $value;
    	}

		if (!isset($value["und"]) || !isset($value["und"][0][$label])) {
            return $value;
        }

        if (isset($value["und"][0])) {
            return $value["und"][0][$label];
        }
        else {
            return $value["und"][$label];
        }
    }
}