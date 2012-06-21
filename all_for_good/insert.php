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
	
        $include_terms = array(
                                         1 => 'church',
                                     2 => 'chaplain',
                                     3 => 'minister',            /* only in US */
                                     4 => 'christian',
                                     5 => 'jesus',
                                     6 => 'gospel',
                                     7 => 'catholic',
                                     8 => 'ministry',            /* only in US */
                                     //9 => 'religious',         /* too generic */
                                     10 => 'evangelical',
                                     11 => 'christ',
                                     12 => 'faith',
                                     13 => 'Protestant',
                                     15 => 'rescue mission',
                                     16 => 'Union Mission',
                                     17 => 'Salvation Army',
                                     18 => 'World Vision',
                                     19 => 'missionary',
                                     20 => 'baptist',
                                     21 => 'lutheran',
                                     22 => 'methodist',
                                     23 => 'presbyterian',
                                     24 => 'pentecostal',
                                     25 => 'denominational',
                                     26 => 'evangelical',
                                     27 => 'calvary',
                                     28 => 'born again',
                                     29 => 'orthodox',
                                     30 => 'anglican',
                                     //31 => 'reformed',          /* word stemming was causing this to be reform */
                                     32 => 'god',
                                     33 => 'apostolic',
                                     34 => 'worship',
                                     35 => 'choir',
                                     37 => 'chapel',             /* added this & those below */
                                     38 => 'diocese',
                                     39 => 'parish',
                                     40 => 'Assemblies of God',
                                     41 => 'discipleship',
                                     //42 => 'Catholic Charities',
                                     43 => 'Volunteers of America',
                                     //44 => 'Catholic Relief',
                                     45 => 'Food for the Poor',
                                     46 => 'Samaritan\'s Purse',
                                     //47 => 'Christian Aid',
                                     48 => 'Compassion International',
                                     //49 => 'Christian Children\'s Fund',
                                     //50 => 'Catholic Medical Mission Board',
                                     51 => 'Covenant House',
                                     52 => 'Campus Crusade',

 //53 => 'Christian Missionary Alliance',
                                     54 => 'Trinity Broadcasting',
                                     //55 => 'Christian Broadcasting',
                                     56 => 'Young Life',
                                     57 => 'Focus on the Family',
                                     58 => 'bible',
                                     59 => 'Billy Graham',
                                     //60 => 'Christian Blind Mission',
                                     61 => 'Interchurch Medical Assistance',
                                     62 => 'Christa Ministries',
                                     63 => 'In Touch Ministries',
                                     //64 => 'InterVarsity Christian Fellowship',
                                     //65 => 'Fellowship of Christian Athletes',
                                     //66 => 'Willow Creek Community Church',
                                     67 => 'Operation Blessing',
                                     68 => 'Prison Fellowship',
                                     //69 => 'Church World Service',
                                     70 => 'Medical Teams International',
                                     71 => 'MAP International',
                                     72 => 'Kingsway Charities',
                                     73 => 'Gideons International',
                                     //74 => 'Christian Appalachian Project',
                                     75 => 'Biblica',
                                     76 => 'Life Outreach',
                                     77 => 'World Relief',
                                     78 => 'Trans World Radio',
                                     79 => 'Mission Aviation Fellowship',
                                     80 => 'Alliance Defense Fund',
                                     81 => 'Blessings International',
                                     82 => 'Eternal Word Television',
                                     83 => 'cathedral',
                             84 => 'Chi Alpha',
                             85 => 'Church World Service',
                             86 => 'clergy',
                             87 => 'congregational',
                             88 => 'crisis pregnancy',
                             89 => 'deacon',
                             90 => 'diaconal',
                             91 => 'disciple',
                             92 => 'Episcopal',
                             93 => 'Feed the Children',
                             94 => 'Focus on the Family',
                             96 => 'Gideons',
                             97 => 'Here\'s Life Inner City',
                             98 => 'holiness',
                             99 => 'intercession',
                             100 => 'Intervarsity',
                             101 => 'liturgy',
                             102 => 'Mennonite',
                             103 => 'monastery',
                             104 => 'Nazarene',
                             105 => 'priest',
                             106 => 'Quaker',
                             107 => 'RUF',
                             108 => 'spiritual director',
                             109 => 'theologian',
                             110 => 'UYWI',
                             111 => 'vicar',
                             112 => 'Wesleyan',
                             113 => 'worship',
                             114 => 'holiness',
                             115 => 'pastor',
                             116 => 'group home',
                             117 => 'soup kitchen',
                             118 => 'food pantry',
			     119 => 'homeless shelter'
        );

	$exclude_terms = array(
                                     1 => 'Muslim',
                                     2 => 'Jewish',
                                     3 => 'Unitarian',
                                     4 => 'Mormon',
                                     5 => 'hospital',            /* maybe add this back */
                                     6 => 'LGBT',                /* would be a source of controversy */
                                     7 => 'Falls Church',
                                     8 => 'Church Street',
                                     9 => 'Church Road',
                                     10 => 'Gospel Street',
                                     11 => 'Faith Technologies',
                                     12 => 'Church\'s Chicken',
                                     13 => 'Garden of the Gods',
                                     14 => 'Christ Church',
                                     15 => 'ChristianVolunteering.org',
                                     16 => 'healthcare',
                                     17 => 'medical center'

	);
        foreach($include_terms as $k => $v) $include_terms[$k] = '"' . $include_terms[$k] . '"'; 
        foreach($exclude_terms as $k => $v) $exclude_terms[$k] = '"' . $exclude_terms[$k] . '"';

	$response = make_http_request(
	  'http://www.allforgood.org/api/volopps', 
	  array(
	         'key'       => 'christianvolunteering',
	  	      'output'   => 'rss',
	  	      'vol_loc'  => $lat . ',' . $long,
	  	      'q'        => '-detailurl:http*christianvolunteering* AND -detailurl:http*churchvolunteering* AND (' . implode(' OR ', array_slice($include_terms, 0, 50)).  ') AND -(' . implode(' OR ', array_slice($exclude_terms, 0, 3)) . ')',
	  	      'num'      => '100',
	  	      'vol_dist' => '100'
	  )
	);
        var_dump($response);
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
