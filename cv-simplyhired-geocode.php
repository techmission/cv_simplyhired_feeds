<!doctype html>
<html>
<head>
<title>Jobs Table Results Browser</title>
</head>
<body>
<?php

// Load the class that does the actual requests to SimplyHired, via the API.
require_once(dirname(__FILE__) . '/cv-simplyhired.class.php');
// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');
// Load the Google geocoder class.
require_once(dirname(__FILE__) . '/GoogleGeocoder.php');
// Load Krumo for the sake of printing variables in debugging.
require_once(dirname(__FILE__) . '/krumo/class.krumo.php');

// Define constants.
define('TABLE_FEEDS_JOBS', 'tbl_feeds_jobs'); // jobs table
define('GMAP_KEY', 'ABQIAAAADF2STd2FFyIZbSoiWXIbaxR7PiuzwriKPLyzR6zyLjSn6oZVURSUPbbY1cObAiEF0-t2-A1LNN8x1w'); // Gmap v2 API key

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
  
  $jobsDb->isLogging = TRUE;
  
  // Connect to the database;
  $jobsDb->connect();
  
  // Get back all the jobs results, as a PDO result set.
  //$jobs = $jobsDb->selectAllRecords($jobsDb::RECORDS_JOB, $jobsDb::FIELDS_LOCATION, FALSE);
  $jobs = $jobsDb->selectRecords('id', array(13022, 13023), $jobsDb::FIELDS_LOCATION, $jobsDb::TYPE_INT, $jobsDb::RECORDS_JOB, FALSE);
  
  // Initialize the geocoder.
  try {
  	$geocoder = new GoogleGeocoder(GMAP_KEY);
  }
  catch(Exception $e) {
    echo $e->getMessage();  	
  }
  
  // Location fields from SimplyHired:
  // id, street, city, province, postal_code, country
  if(is_object($jobs) && get_class($jobs) == 'PDOStatement') {
  	if($jobs->rowCount() == 0) {
  	  echo "<p>There are no jobs currently in the urbmi5_data.tbl_feeds_jobs table.</p>";
  	}
  	else {
  	  $jobCoded = $geocoder->geocodeLocation($job, TRUE);
  	  krumo($job);
  	  krumo($jobCoded);
  	}
  }
}

?>
</body>
</html>