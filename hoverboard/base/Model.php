<?php

namespace hoverboard\base;
use \hoverboard\workers\Database;
use \PDO;
use \hoverboard\workers\HTTP;
use \hoverboard\workers\Router;
use \hoverboard\workers\Messages;
use \Resty;

class Model
{
	/**
	 * @var string $tableName
	 *
	 * Dynamically created based on the model's class name.
	 */
	protected $tableName;


	/**
	 * @var PDO $pdo
	 *
	 * PDO object stored here after it's created.
	 */
	protected $pdo;


	/**
	 * @var PDO $this->pdoPrepared
	 *
	 * PDO prepared statement stored here after it's created, so we have
	 * access to it for error reporting, etc.
	 */
	protected $pdoPrepared;


	/**
	 * @var HTTP $http
	 *
	 * HTTP object stored here after it's created.
	 */
	protected $http;


	/**
	 * @var Router $router
	 *
	 * Router singleton object stored here.
	 */
	protected $router;


	/**
	 * @var array $data
	 *
	 * Array where data is stored after database calls are made.
	 * This data gets passed to the view from the controller.
	 *
	 * As of right now I'm saving a sample piece of data so that
	 * every template has access to it for initial testing.
	 */
	public $data = array();


	public function __construct($options = array())
	{
		// Router set up
		$this->router = Router::getInstance();

		foreach ($options as $key => $value) {
			$this->$key = $value;
		}

		$this->tableName = explode("\\", get_called_class());
		$this->tableName = array_pop($this->tableName);

		$this->data["object"] = strtolower($this->tableName);

		$this->pdo = Database::connect();

		// HTTP worker set up
		$this->http = new HTTP(new Resty());
		$this->http->setBaseURL("http://local.api.hub.jhu.edu/");
	}

	/**
	 * The table name will be dynamically set based on the model's
	 * class name (Posts.php -> table: Posts).
	 *
	 * Use this method to change that table name.
	 */
	public function setTableName($name) {
		$this->tableName = $name;
	}

	/**
     * Based on the options passed from the router, figure out which
     * template to use.
     *
     */
    public function setTemplate()
    {
    	// template base
    	$this->data["template"] = "pages/{$this->source}/";

    	// Let's just kill everything for now if we don't have results
    	// When you get back, Jen, let's figure out who calls this method
    	// and see if this is appropriate here or somewhere else
    	if (!isset($this->data["results"])) {
    		$errorMsg = "No results returned.";
    		if ($this->router->debug()) { 
    			$errorMsg .= "<br><pre>" . __METHOD__ . "()<br>" . __FILE__ . ":" . __LINE__ . "</pre>"; 
    		};
    		$this->router->halt(404, $errorMsg);
    	}
    	// Add ons

    	// single article
    	if (isset($this->slug)) {

    		// if magazine
    		if ($this->source == "magazine") {

    			$department = $this->data["results"]["magazine"]->department->machine_name;

    			if ($department) {
    				$this->data["template"] .= "sub/" . $department;
    			}
    			else {
    				// log this
    				$this->data["template"] .= "single";
    			}
    		}

    		// gazette, hub
    		else {
    			$this->data["template"] .= "single";
    		}
    	}

    	// static page
    	elseif (isset($this->page)) {
    		$this->data["template"] .= "page";
    	}

    	// collection of articles
    	else {
    		$this->data["template"] .= "collection";
    	}
    }

	/**
	 * Create a new record in this model's database table
	 *
	 * Uses PDO's prepare method to safely escape values.
	 *
	 * @param array $values key/value pair of columns and values
	 */
	public function create(array $values)
	{
		// $values = array_filter($values, function ($item) {
		// 	return in_array($item, $this->fields);
		// });

		$columns = implode(", ", array_keys($values));
		$values = array_values($values);
		$qMarks = implode(", ", array_fill(0, count($values), "?"));

		$sql = "INSERT INTO {$this->tableName} ({$columns}) VALUES ({$qMarks})";

		$this->pdoPrepared = $this->pdo->prepare($sql);
		return $this->pdoPrepared->execute($values);
	}

	/**
	 * Update a record in this model's database table
	 *
	 * @param int $id The table ID for the record you want to update
	 * @param array $values key/value pair of columns and values to update
	 */
	public function update($id, array $fields)
	{
		// $values = array_filter($values, function ($item) {
		// 	return in_array($item, $this->fields);
		// });

		// $columns = implode(", ", array_keys($values));
		// $values = array_values($values);
		// $qMarks = implode(", ", array_fill(0, count($values), "?"));

		$columns = array();
		$values = array();

		foreach ($fields as $column => $value) {
			$columns[] = "{$column} = ?";
			$values[] = $value;
		}

		$sql = "UPDATE {$this->tableName} SET " . implode(", ", $columns) . " WHERE id = {$id} AND deleted = 0";

		$this->pdoPrepared = $this->pdo->prepare($sql);
		return $this->pdoPrepared->execute($values);
	}


	/**
	 * Find records in this model's database table
	 *
	 * Several different methods available.
	 */
	public function findByField(array $fields, $boolean = "AND")
	{
		$columns = array();
		$values = array();

		foreach ($fields as $column => $value) {
			$columns[] = "{$column} = ?";
			$values[] = $value;
		}

		$sql = "SELECT * FROM {$this->tableName} WHERE " . implode(" {$boolean} ", $columns) . " AND deleted = 0";

		$this->pdoPrepared = $this->pdo->prepare($sql);
		$this->pdoPrepared->execute($values);

		return $this->pdoPrepared->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Find all records in this model's database table
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM {$this->tableName} WHERE deleted = 0";

		$this->pdoPrepared = $this->pdo->prepare($sql);
		$this->pdoPrepared->execute();

		return $this->pdoPrepared->fetchAll(PDO::FETCH_ASSOC);
	}


	/**
	 * Delete a record from this model's database table
	 *
	 * @param int $id The table ID of the record to delete
	 *
	 * NOTE: we should think about cascading management...
	 */
	public function delete($id = null)
	{
		if (is_null($id)) {
			$id = $this->id;
		}
		
		$sql = "UPDATE {$this->tableName} SET deleted = 1 WHERE id = {$id}";
		
		$this->pdoPrepared = $this->pdo->prepare($sql);
		$this->pdoPrepared->execute();

		return $this->affectedRows();
	}

	/**
	 * Validation methods that should be in a worker, possibly?
	 *
	 */

	public function validate($value, $rules = array())
	{
		$isValid = true;
		if (!is_array($rules)) {
			$rules = array($rules);
		}

		if (in_array("email", $rules)) {
			if (empty($value) || $value === false || is_null($value)) {
				Messages::push("error", "You know, 'email' is the only field in this whole form, and you forgot it. Just saying.");
				$isValid = false;
			}
			if (!$this->isBasicEmail($value)) {
				Messages::push("error", "That email address doesn't look quite right. Are you sure '{$value}' is a valid email?");
				$isValid = false;
			}
		}

		if (in_array("emailDoesNotExist", $rules)) {
			$userExists = $this->findByField(array("email" => $value));
			//var_dump($userExists); die();
			if (count($userExists) > 0) {
				Messages::push("error", "We already have '{$value}' in our database.");
				$emailParts = explode("@", $value);
				$suggested = $emailParts[0] . "+something@" . $emailParts[1];
				Messages::push("notice", "Note: If you need to register a second account to the same email, try using {$suggested}.");
				$isValid = false;
			}
		}

		if (in_array("usernameDoesNotExist", $rules)) {
			$userExists = $this->findByUsername($value);
			if ($userExists) {
				Messages::push("error", "Sorry, that username ({$value}) is already in use. Try something else?");
				$isValid = false;
			}
		}

		return $isValid;
	}


	/**
	 * Extremely simple email validation
	 */
	public function isBasicEmail($email) {
		$pattern = '/[^@]{1,255}@[^@\.]+\.[^\.]+/';
		return preg_match($pattern, $email) > 0;
	}


	/**
	 * PDO interface methods
	 */
	public function lastPDOErrors()
	{
		return (object) array("code" => $this->pdoPrepared->errorCode(), "info" => $this->pdoPrepared->errorInfo());
	}

	public function affectedRows()
	{
		return $this->pdoPrepared->rowCount();
	}

}