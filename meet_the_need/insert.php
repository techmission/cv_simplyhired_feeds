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
function getFeed($type) {
 $response = make_http_request(
   'https://meettheneed.org/connect/v1/' . $type,
   array(
     'key'    => 'aea0214e4fd160562a9c128bcccfd3c9',
     'type'   => 'xml',
   )
 );
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
    $needs = xt_xml_to_array(getFeed('needs'));
    $needs = $needs['Need'];
    $_orgs = xt_xml_to_array(getFeed('organizations'));
    $_orgs = $_orgs['Organization'];
    $orgs = array();
    foreach($_orgs as $o) {
    	$orgs[$o['ID']] = $o;
    }
    $opps = array();
    foreach($needs as $need) {
    	if($need['QuantityType'] != 'Volunteers') continue;  	
    	$org = $orgs[$need['OrganizationID']];
    	$timing_type = array_keys($need['Timing']);
        $timing_type = $timing_type[0];
    	$start_date = null;
    	$end_date = null;
        echo $timing_type;
    	switch($timing_type) {
    		case "SingleDay":
    			$start_date = $need['Timing']['SingleDay']['Date'];
    			$end_date = $need['Timing']['SingleDay']['Date'];
    			break;
    		case "Recurring":
    			$start_date = $need['Timing']['Recurring']['StartDate'];
    			$end_date = $need['Timing']['Recurring']['EndDate'];
    			break;
    		case "Ongoing":
    			$end_date = $need['Timing']['Ongoing']['EndDate'];
    			break;
    	}    	
        if($start_date) $start_date = strtotime($start_date);
        if($end_date) $end_date = strtotime($end_date);

    	$opps[] = array(
            "position_type"       => 4794, // Local volunteering taxonomy tid from Position Type vocabulary.
            "status"              => 1,
    		"title"               => $need['Title'],
    		"description"         => $need['Description'],
    		"short_description"   => $need['Description'],
    		"source"              => "Meet The Need",
    		"org_name"            => $org['Name'],
    		"referralurl"         => $need['URL'],
    		"source_guid"         => $need['ID'],
         	"location_city"       => $org['City'],
    		"location_province"   => $org['State'],
    		"location_postal_code" => $org['PostalCode'],
    		"location_country"     => "us",
    		"start_date"          => $start_date,
    	    "end_date"            => $end_date,
    		"latitude"            => $need['Latitude'],
    		"longitude"           => $need['Longitude'],
    		"created"             => strtotime($need['Meta']['Added']),
    		"changed"             => strtotime($need['Meta']['Added'])
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
