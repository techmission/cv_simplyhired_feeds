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
 * php insert.php 54 45
 */
if(!empty($argv[1]) && !empty($argv[2])) {
  $lat = $argv[1];
  $long = $argv[2];
  $opps = fetchOppsNear($lat, $long);
}
else {
  $opps = fetchVirtualOpps();
}
insertOpps($opps);
exit(0);

function coreSearchParams() {
  $include_terms = _get_include_terms();
  $exclude_terms = _get_exclude_terms();
  return array(
    'key'       => 'christianvolunteering',
    'output'   => 'rss',
    'q'        => '-detailurl:http*christianvolunteering* AND -detailurl:http*churchvolunteering* AND (' . implode(' OR ', array_slice($include_terms, 0, 40)).  ') AND -(' . implode(' OR ', array_slice($exclude_terms, 0, 25)) . ')'
  );
}

function locationBasedSearchParams($lat, $long) {
  $params = coreSearchParams();
  $params['num'] = '100';
  $params['vol_dist'] = '100';
  $params['vol_loc'] = $lat . ',' . $long;
  return $params;
}

function virtualSearchParams() {
  $params = coreSearchParams();
  $params['type'] = 'virtual';
  return $params;
}

function fetchOppsNear($lat, $long) {
  return fetchOppsCore(locationBasedSearchParams($lat, $long));
}

function fetchVirtualOpps() {
  $to_return = array();
  return fetchOppsCore(virtualSearchParams());
}


function fetchOppsCore($params) {
	// Set return variable to null by default.
	$xml = null;

	$response = make_http_request(
	  'http://www.allforgood.org/api/volopps',
          $params 
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
        $virtual_summary = array();
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
          
          $url_pieces = explode('#', $opp['xml_url']);
          $url = $url_pieces[0];
          
          $opportunities[] = array(
			"title"        => $opp['title'],
                        "description"  => $opp['description'],
			"short_description"  => $opp['description'],
			"source"      => "All For Good",
			"org_name"    => $opp['sponsoringOrganizationName'],
			"referralurl" => $url,
			"source_guid" => $url,
			"location_city"        => $opp['city'], // do these actually have values? i think you may need to
			"location_province"    => $opp['region'], // reverse-geocode to get these ~ead
			"location_postal_code" => $opp['postalCode'],
			"location_country"     => $opp['country'],
			"start_date"  => $opp['startDate'],
			"end_date"    => $opp['endDate'],
			"latitude"    => $coords[0],
			"longitude"   => $coords[1],
			"created"     => strftime('%Y-%m-%d %H:%M:%S'), // is there nothing in the feed to correlate with this? ~ead
			"changed"     => strftime('%Y-%m-%d %H:%M:%S'),
                        "position_type" => ((string) $opp['virtual']) == 'True' ? 'Virtual Volunteering (from home)' : 'Local Volunteering (in person)', // @todo: Figure out if we can set local vs. virtual
		);
	}
	return $opportunities;
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
