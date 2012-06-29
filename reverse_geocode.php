<?php

// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'jobsdb.class.php');
// Load the Google geocoder class.
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GoogleGeocoder.php');

// Define constants.
define('TABLE_FEEDS_JOBS', 'tbl_opportunities'); // denormalized table for inserts
define('GMAP_KEY', 'ABQIAAAADF2STd2FFyIZbSoiWXIbaxR7PiuzwriKPLyzR6zyLjSn6oZVURSUPbbY1cObAiEF0-t2-A1LNN8x1w'); // Gmap v2 API key

define('IS_CLI', PHP_SAPI === 'cli'); // whether this is command-line context (not used)

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
  if(!is_null($jobsDb->dbh)) {
    $pdoSql = 'SELECT id, latitude, longitude FROM ' . $jobsDb->tableName;
    $pdoSql .= ' WHERE  latitude is not null 
                 and longitude is not null 
                 and (location_street is null
                   or location_city is null
                   or location_province is null
                   or location_postal_code is null
                   or location_country is null
                   or length(location_street) = 0 
                   or length(location_city) = 0
                   or length(location_province) = 0
                   or length(location_postal_code) = 0
                   or length(location_country) = 0)
                limit 2499'; // need to limit for Google API requests
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
  		  $location = $geocoder->geocodeLocation($job, TRUE);

                  $updates = array();
                  if($location['location_street']) $updates['location_street'] = $location['location_street'];
                  if($location['location_city']) $updates['location_city'] = $location['location_city'];
                  if($location['location_province']) $updates['location_province'] = $location['location_province'];
                  if($location['location_postal_code']) $updates['location_postal_code'] = $location['location_postal_code'];
                  if($location['location_country']) $updates['location_country'] = $location['location_country'];

                  if(count($updates) > 0) {
                    $pdoSql = 'UPDATE ' . $jobsDb->tableName . ' SET ';
                    $updates_sql = array();
                    foreach($updates as $col => $val) 
                      $updates_sql[] = $col . ' = :' . $col;
                    $pdoSql .= implode(" , ", $updates_sql);
                    $pdoSql .= " where id = :id";
                    $stmt = $jobsDb->dbh->prepare($pdoSql);
                    foreach($updates as $col => $val) $stmt->bindValue(':' . $col, $val, PDO::PARAM_STR);
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
