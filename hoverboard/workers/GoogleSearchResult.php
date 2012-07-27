<?php 

namespace hoverboard\workers;

class GoogleSearchResult
{
	/**
	 * Google XML return documentation found at:
	 * https://developers.google.com/search-appliance/documentation/46/xml_reference
	 * 
	 */
	protected $unprocessed = array();

	public function __construct($xml)
	{
		if (!is_object($xml) || get_class($xml) !== "SimpleXMLElement") {
			throw new Exception("Value passed to GoogleSearchResult constructor is not a SimpleXMLElement object as expected.");
		}

		$elementMap = array(
			"TM" => "searchTime",
			"Q" => "query",
			"PARAM" => array($this, "processParam"),
			"RES" => array($this, "processResults")
		);

		$this->xml = $xml;
		$this->process($this->xml, $elementMap);
		$this->params = (object) $this->params;
	}

	protected function process($loop, $map, &$target = null)
	{
		$target = is_null($target) ? $this : (object) $target;

		foreach ($loop as $key => $value) {
			if (array_key_exists($key, $map)) {
				$mapped = $map[$key];

				if (is_callable($mapped)) {
					call_user_func_array($mapped, array($value, &$target));
				}
				elseif (is_string($mapped)) {
					$target->$mapped = (string) $value;
				}
				else {
					$target->unprocessed[$key] = $value;
				}
			}
			else {
				$target->unprocessed[$key] = $value;
			}
		}
	}

	protected function processParam($param, &$target)
	{
		(object) $target->params[(string) $param["name"]] = (string) $param["value"];
	}

	protected function processResults($results, &$target)
	{
		$target->results = array();
		$resultsMap = array(
			"M" => "rCount",
			"NB" => array($this, "processPagination"),
			"R" => array($this, "processEachResult")
		);
		$target->results["records"] = array();
		$this->process($results, $resultsMap, $target->results);
	}

	protected function processPagination($pageLinks, &$target)
	{
		$target->pagination = new \StdClass;
		$target->pagination->next = isset($pageLinks->NU) ? (string) $pageLinks->NU : false;
		$target->pagination->prev = isset($pageLinks->PU) ? (string) $pageLinks->PU : false;
	}

	protected function processEachResult($record, &$target)
	{
		$resultMap = array(
			"U" => "url",
			"UE" => "urlEscaped",
			"T" => "title",
			"RK" => "relevance",
			"S" => "snippet"
		);
		
		$r = array();
		$this->process($record, $resultMap, $r);
		$target->records[] = $r;

	}

}