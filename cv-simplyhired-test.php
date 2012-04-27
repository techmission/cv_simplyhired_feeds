<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Test Feed</title>
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
 * Just a test of the queries.
 */
if (class_exists( 'CV_SimplyHired_API')) {
	$options = array('publisher_id' => 30845,
	                 'jobboard_url' => 'christianjobsdirectory.jobamatic.com');
	$cvsha = new CV_SimplyHired_API($options);
	//$cvsha->setQuery('Christian'); // search Christian jobs. (not needed - b/c class has default query)
	$cvsha->setIsUsa(FALSE);           // search non-US jobs.
	$cvsha->setCountry('en-ca');  // search jobs in Canada
	$cvsha->setLocation('QC');    // search jobs in Quebec.
	
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
	/* Print the query string. */
	krumo($cvsha->querystring);
	
}
?>
</body>
</html>
