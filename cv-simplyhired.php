<!doctype html>
<html>
<head>
</head>
<body>
<?php

// Load the class that does the actual requests to SimplyHired, via the API.
require_once('cv-simplyhired.class.php');
// Load Krumo for the sake of printing variables in debugging.
require_once('krumo/class.krumo.php');

/**
 * Initialize the class for SimplyHired CV.org integration, 
 * run query, then save.
 */
if ( class_exists( 'CV_SimplyHired_API' ) ) {
	$options = array('publisher_id' => 30845,
	                 'jobboard_url' => 'christianjobsdirectory.jobamatic.com');
	$cvsha = new CV_SimplyHired_API($options);
	$cvsha->setQuery('Christian'); // search Christian jobs.
	$cvsha->setLocation('02124'); // search zipcode.
	// $results = $cvsha->doSearch(100);
	// krumo($cvsha);
	if(!empty($results->error)) {
      $cvsha->printError();
    }
    // In the background, run the query and turn the results into the proper format.
	$jobs = array();
	$jobs = $cvsha->getJobsArray();
	
	// Print the jobs array using Krumo.
	// @todo: Remove when finished testing.
	krumo($jobs);
	// Echo the jobs array.
    // $cvsha->printJobsResults();
}
?>
</body>
</html>
