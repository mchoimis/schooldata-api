<?php

// MySQL credentials and SQL queries.
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "schooldata");
define("DB_HOST", "");

// A label to use for logging errors (for greping syslog, yo).
define("LOG_LABEL", "SchoolData");

// Include required classes.
require 'classes/limonade/limonade.php';
require 'classes/db/class.db.php';

// Routes for HTTP requests.
dispatch('/', 'schoolsSummary');
dispatch('/:code/:data', 'schoolLookup');

// Run this sucker!
run();

/*
 * Return summary school info.
 */
function schoolsSummary() {
	try {
		// Set response header.
		header('Content-type: application/json');
		// Render JSON response.
		return json_encode(getData('GetSchoolSummary'));
	}
	catch (DBException $ex) {
		logError($ex->getMessage());
		header("HTTP/1.0 500 Internal Server Error");
	}
}

/*
 * Lookup school information.
 */
function schoolLookup() {
	try {
		
		// Extract parameters from URL.
		$code = (int) params('code');
		$data = params('data');
		// Set response header.
		header('Content-type: application/json');
		// Render JSON response.
		$data = $data ? $data : 'school_information';
		return json_encode(getData('GetSchoolData', array($data, $code)));
	}
	catch (DBException $ex) {
		logError($ex->getMessage());
		header("HTTP/1.0 500 Internal Server Error");
	}
}

/*
 * Run SQL query to get school data.
 */
function getData($name, Array $parameters=array()) {
	$db = new DB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$result = $db->executeStatement($name, $parameters);
	$json_array = array();
	while($row = mysqli_fetch_assoc($result)) {
		array_push($json_array, $row);	
	}
	return $json_array;
}

/*
 * Log an error.
 */
function logError($error) {
	openlog(LOG_LABEL, LOG_PID, LOG_LOCAL0);
	syslog(LOG_ERR, $error);
	closelog();
}

?>