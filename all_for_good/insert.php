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

function fetchOpps($lat, $long) {
	// Set return variable to null by default.
	$xml = null;
	 
	$response = make_http_request(
	  'http://www.allforgood.org/api/volopps', 
	  array(
	         'key'       => 'christianvolunteering',
	  	      'output'   => 'rss',
	  	      'vol_loc'  => $lat . ',' . $long,
	  	      'q'        => '-detailurl:http*christianvolunteering* AND -detailurl:http*churchvolunteering* AND (christian OR jesus OR catholic OR ministry OR religious OR evangelical OR christ OR faith OR Protestant OR missionary OR pastor OR church OR "group home" OR "soup kitchen" OR "food pantry" OR "homeless shelter" OR "rescue mission" OR "Union Mission" OR "Salvation Army" OR "World Vision")',
	  	      'num'      => '100',
	  	      'vol_dist' => '100'
	  )
	);
	if(isset($response->body) && !empty($response->body)) {
		// Do a try/catch on parsing to XML.
		try {
			// Turn off LibXML errors.
			libxml_use_internal_errors(FALSE);
			$xml = new SimpleXMLElement($response->body);
		}
		catch (Exception $e) {
			echo 'error parsing xml';
		}
	}
	
	$opportunities = array();
	if(!$xml || empty($xml->channel) || empty($xml->channel->item)) return $opportunities;

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

function insertOpps($jobs) {
	$db = new JobsDB();
	$db->connect();
	$db->createRecords($jobs);
}