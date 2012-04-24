<?php

  /**
   *  @file: A PHP command-line script for parsing and storing
   *  SimplyHired feed data.
   */

// Load the class that does the actual requests to SimplyHired, via the API.
require_once(dirname(__FILE__) . '/cv-simplyhired.class.php');
// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');

// Define constants.
define('IS_CLI', PHP_SAPI === 'cli'); // whether this is command-line context
define('TABLE_FEEDS_JOBS', 'tbl_feeds_jobs'); // name of jobs table

/* Behaviors of script based on flag. */
define('BEHAVIOR_COUNT', 0);
define('BEHAVIOR_QUERY', 1);

$logging = FALSE;

// Temporarily display runtime errors to the screen.
ini_set('display_errors', TRUE);

/**
 * Initializes the class for SimplyHired CV.org integration, 
 * set up search query, get back results, then save results to DB table.
 * 
 * Usage examples:
 * 
 * php cv-simplyhired-cli.php 02124         # query and insert, no logging
 * php cv-simplyhired-cli.php 02124 -l      # query and insert, with logging
 * php cv-simplyhired-cli.php London -f     # query for results in the city of London (foreign country), no logging
 * php cv-simplyhired-cli.php en_gb -f      # query for results in the United Kingdom (foreign country), no logging
 * php cv-simplyhired-cli.php 02124 -c      # just get the count of results
 * php cv-simplyhired-cli.php London -f -l  # query and insert for London, with logging
 * php cv-simplyhired-cli.php London -f -c -l  # counts only for London, with logging
 */
if (class_exists( 'CV_SimplyHired_API') && IS_CLI) {
    if(!empty($argv[1])) {
	  $options = array('publisher_id' => 30845,
	                 'jobboard_url' => 'christianjobsdirectory.jobamatic.com');
	  $behavior = BEHAVIOR_QUERY; // By default, query and save the results.
	  $cvsha = new CV_SimplyHired_API($options);
	  $cvsha->setLocation($argv[1]);   // search zipcode.
	  $cvsha->setIsUsa(TRUE);          // set to search in US by default
	  /* Set to search outside the US, if -f flag is passed. */
	  if(!empty($argv[2])) {
	  	if($argv[2] == '-f') {
	  	  $cvsha->setIsUsa(FALSE); // Search location is not in the US
	    }
	    else if($argv[2] == '-c') {
	      $behavior = BEHAVIOR_COUNT;
	    }
	    else if($argv[2] == '-l') {
	      $logging = TRUE;
	    }
	  }
	  
	  /* Just do count if set to that behavior via command line. */
	  if(!empty($argv[3])) {
	  	if($argv[3] == '-c') {
	  	  $behavior = BEHAVIOR_COUNT;
	    }
	    else if($argv[3] == '-l') {
	      $logging = TRUE;
	    }
	  }
	  
	  // Set logging.
	  if(!empty($argv[4]) && $argv[4] == '-l') {
	  	$logging = TRUE;
	  }

	  if($behavior == BEHAVIOR_QUERY) {
        // In the background, run the query and turn the results into the proper format.
	    $jobs = array();
	    $jobs = $cvsha->fetchJobs(); // Will use the default query terms.
	
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
	
	    // Set to not log at database layer, since logging does not work in command line mode.
	    $jobsDb->isLogging = FALSE;
	
	    // Connect to the database;
	    $jobsDb->connect();
	
	    // Set the table name to count/insert, etc. Not strictly necessary.
	    //$jobsDb->tableName = TABLE_FEEDS_JOBS;
	
	    // Write the jobs records to the tbl_feeds_jobs table.
	    $numInserted = $jobsDb->createRecords($jobs); // @todo: Why is this not showing an accurate count?
	    if($logging == TRUE) {
	      if(!is_int($numInserted)) {
	      	$numInserted = -1; // error condition if numInserted is not a number
	      }	
	      echo "Number inserted was: " . $numInserted . "\n";
	    }
	  }
	  // Otherwise just run the query and get count.
	  else {
	  	$count = $cvsha->fetchCount();
	  	
	  	/* Print the API call. */
	  	echo "API call: " . $cvsha->apicall . "\n";
	  	
	  	/* Print the count of jobs results. */
	  	echo "This query returns the following number of results: " . $count . "\n";
	  }

	exit(0); // Exit with a zero status code: all is well.
    }
    else {
      echo "This is a command line script. \n";
      echo "Usage: \n";
      echo "php " . $argv[0] . " <location>" . "\n";
      echo "Location can be either a zipcode, state, or city. \n";
      exit(1); // Exit with error status code.
    }
}
else {
  exit(1); // Exit with error status code
}
