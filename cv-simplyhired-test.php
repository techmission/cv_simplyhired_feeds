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
 * set up search query, get back results, then save results to DB table.
 */
if (class_exists( 'CV_SimplyHired_API')) {
	$options = array('publisher_id' => 30845,
	                 'jobboard_url' => 'christianjobsdirectory.jobamatic.com');
	$cvsha = new CV_SimplyHired_API($options);
	//$cvsha->setQuery('Christian'); // search Christian jobs. (not needed - b/c class has default query)
	$cvsha->setIsUsa(TRUE);           // search US jobs.
	$cvsha->setLocation('02124');    // search zipcode 02124
	
	// $results = $cvsha->doSearch(100);
	// krumo($cvsha);
	/* if(!empty($results->error)) {
      $cvsha->printError();
    } */
	
    // In the background, run the query and turn the results into the proper format.
	$jobs = array();
	$jobs = $cvsha->fetchJobs(); // Will use the default query terms.
	
	krumo($cvsha->is_usa);
	/* Print the API call. */
	krumo($cvsha->apicall);
	
	// Print the jobs array using Krumo.
	// @todo: Remove when finished testing.
	krumo($jobs);
	// Echo the jobs array.
    // $cvsha->printJobsResults();
    
	/* Write the jobs array to the database. */
	// Initialize the database handler.
	try {
	  $jobsDb = new JobsDB();
	}
	catch(Exception $e) {
	  echo $e->getMessage();
	}
	
	// Set to log.
	$jobsDb->isLogging = TRUE;
	// Set to dry run.
	$jobsDb->isDryRun = FALSE;
	// Get the database connection string.
	//$jobsDb->getConnStr(TRUE);
	
	// Connect to the database;
	$jobsDb->connect();
	
	// Set the table name to count/insert, etc. Not strictly necessary.
	//$jobsDb->tableName = TABLE_FEEDS_JOBS;
	// Get the number of rows currently stored in 'tbl_feeds_jobs'
	//$numRows = $jobsDb->countRecords();
	//krumo($numRows);
	
	// Delete all old jobs records.
	//$jobsDb->truncate();
	
	// Write the jobs records to the tbl_feeds_jobs table.
	$numInserted = $jobsDb->createRecords($jobs); // @todo: Why is this not showing an accurate count?
	krumo($numInserted);
	
	// Delete old jobs records.
	//$numDeleted = $jobsDb->deleteRecords('id', array(10, 11, 12, 13, 14, 15, 16, 17, 18, 19));
	//krumo($numDeleted);
}
?>
</body>
</html>
