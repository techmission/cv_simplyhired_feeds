<!doctype html>
<html>
<head>
</head>
<body>
<?php

// Load the class that does the actual requests to SimplyHired, via the API.
require_once(dirname(__FILE__) . '/cv-simplyhired.class.php');
// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');
// Load Krumo for the sake of printing variables in debugging.
require_once(dirname(__FILE__) . '/krumo/class.krumo.php');

// Define constants.
define('TABLE_FEEDS_JOBS', 'tbl_feeds_jobs');

// Temporarily display runtime errors to the screen.
ini_set('display_errors', TRUE);

/**
 * Initializes the class for SimplyHired CV.org integration,
 * queries the database for all existing records, and prints them out.
 */
if (class_exists( 'CV_SimplyHired_API')) {
  // Initialize the database handler.
  try {
	$jobsDb = new JobsDB();
  }
  catch(Exception $e) {
	echo $e->getMessage();
  }
  
  $jobsDb->isLogging = FALSE;
  
  // Connect to the database;
  $jobsDb->connect();
  
  // Get back all the jobs results, as a PDO result set.
  $jobs = $jobsDb->selectAllRecords($jobsDb::RECORDS_JOB, $jobsDb::FIELDS_ALL, FALSE);
  // Fields from SimplyHired:
  // id, source_guid, guid, title, org_name, referralurl, city, province, postal_code, 
  // country, created, changed, description
  if(is_array($jobs) || (is_object($jobs) && get_class($jobs) == 'PDOStatement')) {
    foreach($jobs as $job) {
  	  echo "<h2>" . $job['title'] . "</h2>";
  	  echo "<p><strong>Description:</strong> " . $job['description'] . "</p>";
  	  echo "<p><strong>Org Name:</strong> " . $job['org_name'] . "</p>";
  	  echo "<p><strong>Location:</strong>" . $job['city'] . ", " . $job['province'] . " " . $job['postal_code'] . ", " . $job['location'];
    }
  }
}

?>
</body>
</html>