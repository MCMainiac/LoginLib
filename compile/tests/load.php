<?php
$root = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR;

require($root."dist".DIRECTORY_SEPARATOR."LoginLib.php");

require("MysqliDb.php");
require("DatabaseAdapter.php");
require("config.php");

try {
    if (isset($databaseConfig)) {
        $db = new DatabaseAdapter($databaseConfig);
    }
	$db->connect();
} catch (Exception $e) {
	trigger_error("Could not connect to database: " . $e->getMessage(), E_USER_ERROR);
	return 1;
}

try {
    if (isset($config)) {
        $loginlib = new LoginLib\LoginLib($config, $db);
    }
} catch(LoginLib\Exceptions\ConfigurationException $e) {
	trigger_error("Caught ConfigurationException: ".$e->getMessage());
	return 1;
}

function logEntry($message) {
	if (is_array($message)) {
		print_r($message);
	} else
		echo $message . "\n";
}