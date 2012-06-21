<?php

// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/../jobsdb.class.php');
// Load the Google geocoder class.
require_once(dirname(__FILE__) . '/../GoogleGeocoder.php');

// Define constants.
define('TABLE_FEEDS_JOBS', 'tbl_feeds'); // jobs table
define('GMAP_KEY', 'ABQIAAAADF2STd2FFyIZbSoiWXIbaxR7PiuzwriKPLyzR6zyLjSn6oZVURSUPbbY1cObAiEF0-t2-A1LNN8x1w'); // Gmap v2 API key

define('IS_CLI', PHP_SAPI === 'cli'); // whether this is command-line context

// Temporarily display runtime errors to the screen.
ini_set('display_errors', TRUE);

/**
 * Geocodes all non-geocoded jobs, up to 2500.
 */
if (class_exists( 'JobsDb')) {
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
  
  // Get back all the jobs results with no latitude or longitude as a PDO resultset.
  $fields = $jobsDb->buildSelectFields($jobsDb::FIELDS_LOCATION);
  if(!is_null($jobsDb->dbh)) {
  	$pdoSql = 'SELECT ' . $fields . ' FROM ' . $jobsDb->tableName;
  	$pdoSql .= ' WHERE latitude IS NULL AND longitude IS NULL LIMIT 2499'; // need to limit for Google API requests
    $stmt = $jobsDb->dbh->query($pdoSql);
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
  	// If there were no returned non-geocoded jobs...
  	if($stmt->rowCount() == 0) {
  	  exit(0); // Exit here; nothing to be done
  	}
  	// Otherwise, geocode the jobs and insert records.
  	else {
  	  try {
  	    $jobsDb->dbh->beginTransaction();
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
  		  // Write values to database if geocoding was successful for both latitude and longitude.
  		  // This is done rather than doing them all in one batch for the sake of saving PHP memory.
  		  if(!empty($location['latitude']) && !empty($location['longitude']) 
  			  && is_numeric($location['latitude']) && is_numeric($location['longitude'])
  			  && $location['latitude'] != 0 && $location['longitude'] != 0) {
  			$pdoSql = 'UPDATE ' . $jobsDb->tableName . ' SET  latitude = :latitude, longitude = :longitude ';
  			$pdoSql .= ' WHERE id = :id';
  			$stmt = $jobsDb->dbh->prepare($pdoSql);
  			$stmt->bindValue(':latitude', $job['latitude'], PDO::PARAM_INT);
  			$stmt->bindValue(':longitude', $job['longitude'], PDO::PARAM_INT);
  			$stmt->bindValue(':id', $job['id'], PDO::PARAM_INT);
  			$stmt->execute();
  		  }
  		}
  		$jobsDb->dbh->commit();
  	  }
  	  catch(PDOException $e) {
  		$db->rollback;
  		echo $e->getMessage();
  	  }
  	}
  }
  // Debug the results on successes and failures.
  //krumo($geocoder->returnGeocodingResults());
}
else {
  exit(1); // Not in command line or class doesn't exist.
}

?>
