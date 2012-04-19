<?php

// Load the class that does the actual requests to SimplyHired, via the API.
require_once ( 'cv-simplyhired.class.php');
// Load Krumo for the sake of printing variables.
require_once ( 'krumo/class.krumo.php');

/**
 * Initialize the class for SimplyHired CV.org integration, 
 * run query, then save.
 */
if ( class_exists( 'CV_SimplyHired_API' ) ) {
	$options = array('publisher_id' => 30845,
	                 'jobboard_url' => 'christianjobsdirectory.jobamatic.com');
	$cvsha = new CV_SimplyHired_API($options);
	$cvsha->set_query('Christian'); // search Christian jobs.
	/* $cvsha->set_location('02124'); // search zipcode. */
	$results = $cvsha->search(100);
	// Print the query that was run.
	$cvsha->print_apicall();
	// Print the error that was returned.
	$cvsha->print_error();
	$jobs = array();
	$jobs = $cvsha->get_jobs_array($results);
	// @todo: Write code in here to save to database in denormalized table.
	// May want to write a database abstraction class to handle the saving,
	// so that I can reuse it down the road for other ones.
	
	// First, just include Krumo so I can do krumo on the jobs array and see what I'm getting for a given URL.
	krumo($jobs);
}