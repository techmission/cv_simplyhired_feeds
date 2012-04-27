<!doctype html>
<html>
<head>
<title>SimplyHired Geocoder</title>
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
  $fields = $jobsDb->buildSelectFields($jobsDb::FIELDS_LOCATION);
  if(!is_null($jobsDb->dbh)) {
    $stmt = $jobsDb->dbh->query('SELECT ' . $fields . ' FROM ' . $jobsDb->tableName . ' WHERE latitude IS NULL AND longitude IS NULL LIMIT 10');
  }
  
  // Initialize the geocoder.
  try {
  	$geocoder = new GoogleGeocoder(GMAP_KEY);
  }
  catch(Exception $e) {
    echo $e->getMessage();  	
  }
  
  // Location fields from SimplyHired:
  // id, street, city, province, postal_code, country
  $updated_jobs = array();
  if(is_object($stmt) && get_class($stmt) == 'PDOStatement') {
  	if($stmt->rowCount() == 0) {
  	  echo "<p>There are no jobs currently in the urbmi5_data.tbl_feeds_jobs table.</p>";
  	}
  	else {
  	  foreach($stmt as $job) {
  	    $location = $geocoder->geocodeLocation($job, FALSE);
  	    // Add the latitude if a valid one was returned.
  	    if(!empty($location['latitude']) && is_numeric($location['latitude']) && $location['latitude'] != 0) {
  	      $job['latitude'] = $location['latitude'];
  	    }
  	    // Add the longitude if a valid one was returned.
  	    if(!empty($location['longitude']) && is_numeric($location['longitude']) && $location['longitude'] != 0) {
  	      $job['longitude'] = $location['longitude'];
  	    }
  	    // Add to the array of jobs to update if geocoding was successful for both latitude and longitude.
  	    if(!empty($location['latitude']) && !empty($location['longitude'])) {
  	      $updated_jobs[$job['id']] = $job;
  	    }
  	  }
  	}
  }
  //krumo($updated_jobs);
  try {
    $jobsDb->dbh->beginTransaction();
    foreach($updated_jobs as $job) {
  	  $stmt = $jobsDb->dbh('UPDATE ' . $jobsDb->tableName . ' SET  latitude = :latitude, longitude = :longitude WHERE id = :id');
  	  $stmt->bindValue(':latitude', $latitude, PDO::PARAM_INT);
  	  $stmt->bindValue(':longitude', $latitude, PDO::PARAM_INT);
  	  $stmt->bindValue(':id', $latitude, PDO::PARAM_INT);
    }
    $jobsDb->dbh->commit();
  }
  catch(PDOException $e) {
  	$db->rollback;
  	echo $e->getMessage();
  }
}

?>
</body>
</html>