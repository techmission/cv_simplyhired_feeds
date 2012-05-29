<?php

  /**
   *  @file: A PHP command-line script for parsing and storing
   *  SimplyHired feed data.
   */

// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');

// Define constants.
define('IS_CLI', PHP_SAPI === 'cli'); // whether this is command-line context
define('TABLE_FEEDS_JOBS', 'tbl_feeds'); // name of jobs table

define('DEFAULT_LOGFILE', 'cli-results.csv');  // name of log file for results

// Temporarily display runtime errors to the screen.
ini_set('display_errors', TRUE);

/**
 * Initializes the class for SimplyHired CV.org integration, 
 * set up search query, get back results, then save results to DB table.
 */
if (IS_CLI && class_exists( 'JobsDb')) {
	// Initialize the database handler.
	try {
	  $jobsDb = new JobsDB();
	}
	catch(Exception $e) {
	  echo "Exception: " . $e->getMessage() . "\n";
	}
	
	// Set to not log, since logging does not work in command line mode.
	$jobsDb->isLogging = FALSE;
	
	// Connect to the database;
	$jobsDb->connect();
	
	// Delete all old jobs records for compliance with SimplyHired API TOS.
	$jobsDb->truncate();
	
	// Empty the log file.
	// This will create it if it doesn't already exist.
	$handle = @fopen(DEFAULT_LOGFILE, 'w');

	exit(0); // Exit with a zero status code: all is well.
}
else {
  exit(1); // Exit with error status code
}
