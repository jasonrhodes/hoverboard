<?php

namespace hoverboard\workers;
use \PDO;

class Database
{
	protected static $connections = array();
	protected static $connection;
	protected static $result = array();

	protected static $columnDefaults = array(
		"primary" => false,
		"required" => false,
		"data_type" => "varchar",
		"length" => false,
		"default" => false
	);

	public static function connections($name = "default", array $options)
	{
		extract($options);
		static::$connections[$name]["dsn"] = "{$type}:dbname={$database};host={$host}";
		static::$connections[$name]["username"] = $username;
		static::$connections[$name]["password"] = $password;
	}

	public static function connect($name = null)
	{
		if (is_null($name)) {
			if (!is_null(constant("ENVIRONMENT"))) {
				$name = constant("ENVIRONMENT");
			} else {
				$name = "default";
			}
		}
		if (static::$connection) {
			return static::$connection;
		}
		else {
			extract(static::$connections[$name]);
			return new PDO($dsn, $username, $password);
		}
	}

	public static function flushConnection() 
	{
		static::$connection = null;
	}

	public static function loadSchema($connectionName = "default")
	{
		$pdo = static::connect($connectionName);
		$schema = json_decode(file_get_contents(APP_DIR . "/config/schema.json"));

		$sql = "";
		$columnArray = array();

		foreach ($schema as $table) {
			$sql .= "CREATE TABLE IF NOT EXISTS {$table->name} (";
			foreach ($table->columns as $column) {
				$columnSql = "";
				if (isset($column->primary) && $column->primary) {
					$columnSql .= "`{$column->name}` int(11) unsigned NOT NULL AUTO_INCREMENT";
					$primaryKey = ", PRIMARY KEY (`{$column->name}`)";
				}
				else {
					$columnSql .= "`{$column->name}` {$column->data_type}";
					
					// setup the default length for varchar
					if (!$column->length && $column->data_type == "varchar"){
						$column->length = 255;
					}

					if ($column->length) {
						$columnSql .= "($column->length)";
					}

					$columnSql .= (isset($column->required) && $column->required) ? " NOT NULL " : " ";
					$columnSql .= (isset($column->default) ? "DEFAULT " . $column->default : "");
				}
				$columnArray[] = $columnSql;
			}
			$sql .= implode(", ", $columnArray);
			$sql .= $primaryKey;
			$sql .= ");";
		}

		//$pdo->query($sql);
	}

	public static function loadSchemaYaml($connectionName = "default")
	{
		$pdo = static::connect($connectionName);
		$schema = yaml_parse_file(APP_DIR . "/config/schema.yaml");

		$sql = "";
		$result = array();

		foreach ($schema as $table => $options) {
			if($pdo->query("SELECT * FROM {$table} LIMIT 1;")) {
				$result["messages"][] = "{$table} table already exists. It was not recreated.";
				continue;
			}
			$sql .= "CREATE TABLE IF NOT EXISTS {$table} (";

			$columnArray = array();
			$columnNames = array();

			foreach ($options["columns"] as $column) {

				extract($column + static::$columnDefaults);
				$columnNames[] = $name;
				$columnSql = "";

				if ($primary) {
					$columnSql .= "`{$name}` int(11) unsigned NOT NULL AUTO_INCREMENT";
					$primaryKey = ", PRIMARY KEY (`{$name}`)";
				}
				else {

					$columnSql .= "`{$name}` {$data_type}";

					// setup the default length for varchar
					if (!$length && $data_type == "varchar"){
						$length = 255;
					}

					if ($length) {
						$columnSql .= "($length)";
					}

					$columnSql .= $required ? " NOT NULL " : " ";
					$columnSql .= $default ? "DEFAULT " . $default : "";
				}
				$columnArray[] = $columnSql;
			}
			$sql .= implode(", ", $columnArray);
			$sql .= $primaryKey;
			$sql .= ");";

			$result["messages"][] = "{$table} table was created with columns: " . implode(", ", $columnNames);
		}

		$result["PDOStatement"] = $pdo->query($sql);
		if (!$result["PDOStatement"]) {
			$result["messages"][] = array("The PDO query failed.");
		}
		return $result;

	}
}