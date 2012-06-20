<?php

  /**
   *  @file: A PHP command-line script for parsing and storing
   *  All For Good feed data.
   */
// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pdo_ext.class.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'jobsdb.class.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'xmltools.php');

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
 * php cv-simplyhired-cli.php 02124               # query and insert, no logging
 * php cv-simplyhired-cli.php 02124 -l            # query and insert, with logging
 * php cv-simplyhired-cli.php London -f:en_gb     # query for results in the city of London (foreign country), no logging
 * php cv-simplyhired-cli.php England -f:en_gb    # query for results in England (foreign country), no logging
 * php cv-simplyhired-cli.php 02124 -c            # just get the count of results
 * php cv-simplyhired-cli.php London -f -l        # query and insert for London, with logging
 * php cv-simplyhired-cli.php London -f -c -l     # counts only for London, with logging
 */
if(!empty($argv[1]) && !empty($argv[2])) {
  $lat = $argv[1];
  $long = $argv[2];
  insertOpps(fetchOpps($lat, $long));
  exit(0);
}
else {
  echo "This is a command line script. \n";
  echo "Usage: \n";
  echo "php " . $argv[0] . " <latitude> <logitude>" . "\n";
  exit(1); // Exit with error status code.
}

function fetchOpps() {
    $needs = getFeed('needs')->Need;
    $_orgs = getFeed('orgs')->Organization;
    $orgs = array();
    foreach($_orgs as $o) {
    	$orgs[$o->ID] = $o;
    }
	
    $opps = array();
    foreach($needs as $need) {
    	if($need->QuantityType != 'Volunteers') continue;  	
    	$org = $orgs[$need->OrganizationID];
    	$timing_type = array_keys($need->Timing->children());
    	$timing_type = $timing_types[0];
    	$start_date = "";
    	$end_date = "";
    	switch($timingType) {
    		case "SingleDay":
    			$start_date = $need->Timing->SingleDay->Date;
    			$end_date = $need->Timing->SingleDay->Date;
    			break;
    		case "Recurring":
    			$start_date = $need->Timing->Recurring->StartDate;
    			$end_date = $need->Timing->Recurring->EndDate;
    			break;
    		case "Ongoing":
    			$end_date = $need->Timing->Ongoing->EndDate;
    			break;
    	}
    	
    	$opps[] = array(
    		"title" => $need->Description,
    		"description" => $need->Description,
    		"teaser" => $need->Description,
    		"source" => "Meet The Need",
    		"org_name" => $org->Name,
    		"referralurl" => "",
    		"source_guid" => $need->ID,
    	    "city" => $org->City,
    		"province" => $org->State,
    		"postal_code" => $org->PostalCode,
    		"country" => "us",
    		"start_date" => "",
    	    "end_date" => "",
    		"latitude" => $need->Latitude,
    		"longitude" => $need->Longitude,
    		"created" => strtotime($need->Meta->Added),
    		"changed" => strtotime($need->Meta->Added)
    	);
    }
        $opps = array();
	foreach($xml->channel->item as $o) {
        $opp = array();
          foreach($o->children('fp', true) as $k => $v) $opp[$k] = $v;
          foreach($o->children() as $k => $v) $opp[$k] = $v;
          $opps[] = $opp;
        }
        foreach($opps as $opp) {
                $coords = explode(",", $opp['latlong']);
                $opportunities[] = array(
			"title"       => $opp['title'],
			"description" => $opp['description'],
			"teaser"      => $opp['description'],
			"source"      => "All For Good",
			"org_name"    => $opp['sponsoringOrganizationName'],
			"referralurl" => $opp['xml_url'],
			"source_guid" => $opp['id'],
			"city"        => $opp['city'],
			"province"    => $opp['region'],
			"postal_code" => $opp['postalCode'],
			"country"     => $opp['country'],
			"start_date"  => $opp['startDate'],
			"end_date"    => strtotime($opp['endDate']),
			"latitude"    => $coords[0],
			"longitude"   => $coords[1],
			"created"     => time(),
			"changed"     => time()
		);
	}
	return $opportunities;
}

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
			// Turn off LibXML errors.
			libxml_use_internal_errors(FALSE);
			return new SimpleXMLElement($response->body);
		}
		catch (Exception $e) {
			echo 'error parsing xml';
			return null;
		}
	}
	return null;
}

function insertOpps($jobs) {
	$db = new JobsDB();
	$db->connect();
	$db->createRecords($jobs);
}
