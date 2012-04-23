<?php

  /**
   *  @file: A PHP command-line script for parsing and storing
   *  SimplyHired feed data.
   */

// Load the class that does the actual requests to SimplyHired, via the API.
require_once(dirname(__FILE__) . '/cv-simplyhired.class.php');
// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');
// Load Krumo for the sake of printing variables in debugging.
require_once(dirname(__FILE__) . '/krumo/class.krumo.php');

// Define constants.
define('IS_CLI', PHP_SAPI === 'cli'); // whether this is command-line context
define('TABLE_FEEDS_JOBS', 'tbl_feeds_jobs'); // name of jobs table

// Temporarily display runtime errors to the screen.
ini_set('display_errors', TRUE);

/**
 * Initializes the class for SimplyHired CV.org integration, 
 * set up search query, get back results, then save results to DB table.
 */
if (class_exists( 'CV_SimplyHired_API') && IS_CLI) {
    if(!empty($argv[1])) {
	  $options = array('publisher_id' => 30845,
	                 'jobboard_url' => 'christianjobsdirectory.jobamatic.com');
	  $cvsha = new CV_SimplyHired_API($options);
	  $cvsha->setLocation($argv[1]); // search zipcode.
	
      // In the background, run the query and turn the results into the proper format.
	  $jobs = array();
	  $jobs = $cvsha->getJobsArray(); // Will use the default query terms.
	
	/* Print the API call. */
	echo "API call: " . $cvsha->apicall . "\n";
	
	// Echo the jobs array.
	//var_dump($jobs);
    
	/* Write the jobs array to the database. */
	
	// Initialize the database handler.
	try {
	  $jobsDb = new JobsDB();
	}
	catch(Exception $e) {
	  echo "Exception: " . $e->getMessage() . "\n";
	}
	
	// Set to log.
	$jobsDb->isLogging = TRUE;
	
	// Connect to the database;
	$jobsDb->connect();
	
	// Set the table name to count/insert, etc. Not strictly necessary.
	//$jobsDb->tableName = TABLE_FEEDS_JOBS;
	
	// Write the jobs records to the tbl_feeds_jobs table.
	$numInserted = $jobsDb->createRecords($jobs); // @todo: Why is this not showing an accurate count?
	echo "Number inserted was: " . $numInserted . "\n";

	exit(0); // Exit with a zero status code: all is well.
    }
    else {
      echo "This is a command line script. \n";
      echo "Usage: " . $argv[0] . " <location>" . "\n";
      echo "Location can be either a zipcode, state, or city. \n";
      exit(1); // Exit with error status code.
    }
}
else {
  exit(1); // Exit with error status code
}
