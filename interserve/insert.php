<?php

  /**
   *  @file: A PHP command-line script for parsing and storing
   *  All For Good feed data.
   */
// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/../jobsdb.class.php');
// Load the class for making HTTP requests and parsing XML.
require_once(dirname(__FILE__) . '/../xmltools.php');

// Define constants.
define('IS_CLI', PHP_SAPI === 'cli'); // whether this is command-line context
define('TABLE_FEEDS_JOBS', 'tbl_feeds'); // name of jobs table

define('DEFAULT_LOGFILE', 'cli-results.csv');

$logging = FALSE;

// Temporarily display runtime errors to the screen.
//ini_set('display_errors', TRUE);

/**
 * Initializes the class for SimplyHired CV.org integration, 
 * set up search query, get back results, then save results to DB table.
 * 
 * Usage examples:
 * 
 * php insert.php
 */
insertOpps(fetchOpps());
exit(0);

/* Make HTTP request for feed. */
function getFeed() {
 $response = make_http_request('http://data.interserve.org/feed-v2/xml/name/job');
 if(isset($response->body) && !empty($response->body)) {
  // Do a try/catch on parsing to XML.
  try {
   // Turn off LibXML errorsi.
   libxml_use_internal_errors(FALSE);
   return new SimpleXMLElement($response->body);
  }
  catch (Exception $e) {
   echo 'error parsing XML';
   return null;
  }
 }
 return null;
}

/* Parse feed. */
function fetchOpps() {
    $jobs = xt_xml_to_array(getFeed());
    $opps = array();
    foreach($jobs['item'] as $j) {
    	$opps[] = array(
            "position_type"      => 'Job',
            "status"             => 0,
            "title"              => $j['title'],
    	    "description"        => $j['description'],
    	    "short_description"  => $j['description'],
    	    "source"             => "Interserve.org",
            "referralurl"        => 'http://interserve.org/index.php?option=com_jumi&fileid=4&Itemid=94&id=' . $j['id'],
            "source_guid"        => $j['id'],
    	    "country_name"       => $j['country-name'],
            "created"            => strtotime($j['posted']),
    	    "changed"            => strtotime($j['last-modified'])
    	);
    }
    return $opps;
}

/* Insert opportunities. */
function insertOpps($opps) {
	try {
      $db = new JobsDB();
	}
	catch(Exception $e) {
	 // Print error and exit.
	 echo "Exception: " . $e->getMessage() . "\n";
	 exit(1);
	}
	$db->connect();
	$db->createRecords($opps);
}
