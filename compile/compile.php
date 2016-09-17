<?php
$filestarttime = microtime(true);
$returncode = 0;

class Compiler {
	public $root;
	public $outputfile;
	private $sourcefiles;

	public function __construct($root) {
		$this->root = $root;
		$this->outputfile = $this->root.'/dist/LoginLib.php';
		$this->sourcefiles = array(
				'Config.php',
				'Exceptions.php',
				'IDatabase.php',
				'LoginLib.php',
				'Results.php',
				'User.php'
		);
	}

	public function compile() {
		$starttime = microtime(true);

		file_put_contents($this->outputfile, "<?php\r\n");

		// add file comment
		$this->append("/**\r\n * This file contains all classes for LoginLib.\r\n * \r\n * Dont't forget the dependency on MysqliDb!\r\n */\r\n");

		// add namespace
		$this->append("namespace LoginLib;");

		// loop through the source files and add them to the output file
		foreach ($this->sourcefiles as $file) {
			if (substr($file, 0, 1) !== ".")
				$this->append(file_get_contents($this->root.'/src/'.$file));
		}

		echo "Compiling finished! (Took: ".(microtime(true) - $starttime)."ms)\n\n";
	}

	private function append($file) {
		// filter the file description and the php tag + namespaces away...
		$file = preg_replace("/\\<\\?php.*(use [a-z\\\]*;|namespace [a-z\\\]*;)/is", "", $file);

		// just append the file to the final one
		file_put_contents($this->outputfile, $file, FILE_APPEND | LOCK_EX);
	}
}

/*****************************************************************************/

echo "Compiling...\n";

$c = new Compiler(dirname(__DIR__));
$c->compile();

// Test if no php errors are thrown
require($c->outputfile);

if (!class_exists("LoginLib\LoginLib")) {
	die(trigger_error("Class LoginLib\LoginLib not found!", E_USER_ERROR));
}

// echo current LoginLib version, if build succeed
echo "Running LoginLib v".LoginLib\LoginLib::version()."\n\n";

/*****************************************************************************/

// create tables

$queriesraw = file_get_contents(__DIR__.DIRECTORY_SEPARATOR."queries".DIRECTORY_SEPARATOR."create.sql");
$queries = explode(";", $queriesraw);

foreach ($queries as $id => $query) {
	$queries[$id] = trim(str_replace(array("\r\n  ", "\r\n"), array(" ", ""), $query));
}

// TODO: fix class paths

require($c->root);
require("DatabaseAdapter.php");
require("config.php");

$db = new DatabaseAdapter($databaseConfig);

foreach ($queries as $query) {
	if (strlen($query) != 0) {
		$db->rawQuery($query);
	}
}

/*****************************************************************************/

// collect tests

if (!($h = opendir(__DIR__.DIRECTORY_SEPARATOR."tests"))) {
	die(trigger_error("Could not open directory handle for tests!", E_USER_ERROR));
}

$tests = array();

while (false !== ($entry = readdir($h))) {
	if (substr($entry, 0, 1) != "." && is_numeric(substr($entry, 0, 1))) {
		$tests[] = __DIR__.DIRECTORY_SEPARATOR."tests".DIRECTORY_SEPARATOR.$entry; 
	}
}

// running tests

$teststarttime = microtime(true);

echo "Running tests:\n";
echo "-----\n";

$ok = true;

for ($i = 0; $i < count($tests); $i++) {
	exec("php \"".$tests[$i]."\"", $output, $return);
	
	echo "Test [".($i + 1)."] (\"".substr(end(explode(DIRECTORY_SEPARATOR, $tests[$i])), 2)."\"):\n";
	foreach ($output as $line)
		echo $line."\n";
	echo "\n";
	echo "Returned: ".$return;
	echo "-----\n";
	
	if ($return != 0) {
		$ok = false;
		break;
	}
}

if ($ok)
	echo "Tests completed successfully! (took: ".(microtime(true) - $teststarttime)."ms)\n";
else
	die(trigger_error("Tests failed! (took: ".(microtime(true) - $teststarttime)."ms)\n", E_USER_ERROR));

/*****************************************************************************/

// delete tables

$queriesraw = file_get_contents(__DIR__.DIRECTORY_SEPARATOR."queries".DIRECTORY_SEPARATOR."delete.sql");
$queries = explode(";", $queriesraw);

foreach ($queries as $id => $query) {
	$queries[$id] = trim(str_replace(array("\r\n  ", "\r\n"), array(" ", ""), $query));
}

foreach ($queries as $query) {
	if (strlen($query) != 0) {
		$db->rawQuery($query);
	}
}

/*****************************************************************************/

echo "\n";
echo "Complete build with tests took: ".(microtime(true) - $filestarttime)."ms\n";

return $returncode;

