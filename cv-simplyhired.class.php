<?php

/**
Simply Hired API for saving to denormalized table.
Based on Webstractions plugin.
*/

/**
	License: GPL2
	This script is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This script is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Constants - none as yet. */

/* This class extends the SimplyHired_API class wrapper. */
require_once(dirname(__FILE__) . '/simplyhired-api.class.php');

/**
 * Main plugin class
 */
class CV_SimplyHired_API extends SimplyHired_API {
	/* Constants. */
	const SOURCE_NAME = 'simplyhired'; // source name for database
	
	const QRY_DEFAULT = TRUE; // if the default query should be used
	const LOCATION_DEFAULT = '02124'; // default search location
	
	const OP_OR = 'OR'; // operators to join query string
	const OP_AND = 'AND';
	
	const RES_SIZE_DEFAULT = 100; // by default query for the maximum that you can get in one page
	const RES_RADIUS_DEFAULT = 100; // query for a 100-mile radius by default
	
	const MAX_OFFSET = 9; // the maximum number of pages beyond the first page of results to get (1000 results total)

	/* Class variables. */
    
	/* Options passed in when instantiating class. -
	   this is a public variable so that it can be modified later, if needed. */
	public $options = array();
	public $is_logging = FALSE;
	
	/**      
	 * Constructor for class.
	 *
	 * @param array $options Options for the SimplyHired query. (optional)
	 * 
	 * @return void
	 */
	function __construct($options = array()) {
		
		/* Options */
		$this->options = $options;
		
		/* To query SimplyHired requires a Job-a-matic account. */
		if ( isset( $this->options['publisher_id'] ) 
			&& 	isset( $this->options['jobboard_url'] ) ) {
			/* Initialize SimplyHired API */
			$this->init( $this->options['publisher_id'], $this->options['jobboard_url'] );
			
			/* Set broad ONET code (can be over-ridden via shortcode ) */
			if ( $this->options['broad_onet_code'] ) {
				$this->setOnet($this->options['broad_onet_code']);
			}
			$this->setIsUsa( $this->options['is_usa'] );
			$radius = (!empty($this->options['radius'])) ? $this->options['radius'] : self::RES_RADIUS_DEFAULT;
			$this->setRadius($radius);
			$this->setDisableTracking( $this->options['disable_tracking']);
		}
	}
	
	/**
	 * Sets a default query for the system, using ORs for all our faith terms.
	 */
	public function buildDefaultQuery($pOperator = self::OP_OR) {
	  $lQryIncludesArray = $this->_getDefaultQueryIncludes();
	  // Leave out certain terms outside the US
	  if($this->country != 'en-us') {
	  	unset($lQryIncludesArray[8]);    // "ministry" - b/c used in gov't jobs
	  	unset($lQryIncludesArray[3]);    // "minister" - b/c used in gov't jobs
	  	unset($lQryIncludesArray[12]);   // "faith" - shows up in non-discrimination statements
	  }
	  $lQryExcludesArray = $this->_getDefaultQueryExcludes();
	  // Put spaces around the operator.
	  $lOperator = ' ' . $pOperator . ' ';
	  $lDefaultQueryIncludes = '(' . implode($lOperator, $lQryIncludesArray) . ')';
	  $lDefaultQueryExcludes = '';
	  if(count($lQryExcludesArray) > 0) {
	    $lDefaultQueryExcludes = ' AND NOT ' . implode(' AND NOT ', $lQryExcludesArray);
	  }
	  // Query has both inclusions and exclusions.
	  $lDefaultQuery = $lDefaultQueryIncludes . $lDefaultQueryExcludes;
	  return $lDefaultQuery;
	}
	
	/* Defines the default query to be used for Christian job searches. */
	private function _getDefaultQueryIncludes() {
	  $lQryArray = array(0 => 'pastor',
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
	  		             14 => 'missionary',
	  		             15 => '"rescue mission"',
	  		             16 => '"Union Mission"',
	  		             17 => '"Salvation Army"',
	  		             18 => '"World Vision"',
	  		             19 => 'missionary',
	  		             20 => 'baptist',
	  		             21 => 'lutheran',
	  		             22 => 'methodist',
	  		             23 => 'presbyterian',
	  		             24 => 'pentecostal',
	  		             25 => 'denominational',
	  		             26 => 'evangelical',
	  		             27 => 'calvary',
	  		             28 => '"born again"',
	  		             29 => 'orthodox',
	  		             30 => 'anglican',
	  		             //31 => 'reformed',          /* word stemming was causing this to be "reform" */
	  		             32 => 'god',
	  		             33 => 'apostolic',
	  		             34 => 'worship',
	  		             35 => 'choir',
	  		             37 => 'chapel',             /* added this & those below */
	  		             38 => 'diocese',
	  		             39 => 'parish',
	  		             40 => '"Assemblies of God"',
	  		             41 => 'discipleship',
	  		             //42 => '"Catholic Charities"',
	  		             43 => '"Volunteers of America"',
	  		             //44 => '"Catholic Relief"',
	  		             45 => '"Food for the Poor"',
	  		             46 => '"Samaritan\'s Purse"',
	  		             //47 => '"Christian Aid"',
	  		             48 => '"Compassion International"',
	  		             //49 => '"Christian Children\'s Fund"',
	  		             //50 => '"Catholic Medical Mission Board"',
	  		             51 => '"Covenant House"',
	  		             //52 => '"Campus Crusade"',
	  		             //53 => '"Christian Missionary Alliance"',
	  		             //54 => '"Trinity Broadcasting"',
	  		             //55 => '"Christian Broadcasting"',
	  		             //56 => '"Young Life"',
	  		             //57 => '"Focus on the Family"',
	  		             //58 => '"Wycliffe Bible Translators"',
	  		             //59 => '"Billy Graham"',
	  		             //60 => '"Christian Blind Mission"',
	  		             //61 => '"Interchurch Medical Assistance"',
	  		             //62 => '"Christa Ministries"',
	  		             //63 => '"In Touch Ministries"',
	  		             //64 => '"InterVarsity Christian Fellowship"',
	  		             //65 => '"Fellowship of Christian Athletes"',
	  		             //66 => '"Willow Creek Community Church"',
	  		             //67 => '"Christian Reformed Church"',     // replace "reformed"	  		             
	  		         );
	  return $lQryArray; 	
	}
	
	private function _getDefaultQueryExcludes() {
	  $lQryArray = array(1 => 'Muslim', 
	  		             2 => 'Jewish',
	  		             3 => 'Unitarian',
	  		             4 => 'Mormon',
	  		             5 => 'hospital',            /* maybe add this back */
	  		             6 => 'LGBT',                /* would be a source of controversy */
	  		             7 => '"Falls Church"',
	  		             8 => '"Church Street"',
	  		             9 => '"Church Road"',
	  		             10 => '"Gospel Street"',
	  		             11 => '"Faith Technologies"',
	  		             12 => '"Church\'s Chicken"',
	  		             13 => '"Garden of the Gods"',
	  		             14 => '"Christ Church"',
	  		             15 => 'ChristianVolunteering.org',
	  		             16 => 'healthcare',
	  		             17 => '"medical center"',
	  		           );
	  return $lQryArray;
	}

	/**
	 * Runs search, but only returns the number of items.
	 * @todo: Condense this and fetchJobs into a single function, or wrap the functionality somehow?
	 */
	public function fetchCount($pQuery = self::QRY_DEFAULT) {
	  $lCount = 0;
	  // Set query.
	  $lQuery = $pQuery;
	  if($pQuery == self::QRY_DEFAULT) {
	  	$lQuery = $this->buildDefaultQuery();
	  	$this->setQuery($lQuery);
	  }
	  else if(is_string($pQuery) && !empty($pQuery)) {
	  	$this->setQuery($pQuery);
	  }
	  // Set location if not already set.
	  if(empty($this->location)) {
	  	$this->setLocation = self::LOCATION_DEFAULT;
	  }
	  // Do the search based on the parameters set.
	  $results = $this->doSearch();
	  if(!empty($results) && !isset($results->error)) {
	    $lCount = $this->parseResultNum($results);
	  }
	  else {
	  	$lCount = '-1'; // error condition
	  }
	  return $lCount;	
	}
	
	/**
	 * Runs search and returns jobs.
	 * Pages through results if there is more than 100 returned.
	 * 
	 * @param bool|string $pQuery
	 *   The search query. By default it uses the class' default query.
	 * @param int $pLimit
	 *   The maximum number of results to return.
	 */
	public function fetchJobs($pQuery = self::QRY_DEFAULT, $pLimit = self::RES_SIZE_DEFAULT) {
	  $lJobsArray = array();
	  $lOffset = 0;
	  $retJobsArray = array();
	  // Error checking on maximum value.
	  if(!is_int($pMax) || $pMax < 0 || $pMax > 100) {
	  	$pMax = self::RES_SIZE_DEFAULT;
	  }
	  // Set query.
	  $lQuery = $pQuery;
	  if($pQuery == self::QRY_DEFAULT) {
	  	$lQuery = $this->buildDefaultQuery();
	  	$this->setQuery($lQuery);
	  }
	  else if(is_string($pQuery) && !empty($pQuery)) {
	  	$this->setQuery($pQuery);
	  }
	  // Set location if not already set.
	  if(empty($this->location)) {
	  	$this->setLocation = self::LOCATION_DEFAULT;
	  }
	  // Fetch jobs.
	  $lJobsArray = $retJobsArray = $this->_fetchJobs($pLimit);
	  while(count($lJobsArray) == 100) {
	  	$lJobsArray = $this->_fetchJobs($pLimit, $lOffset);
	  	$retJobsArray = array_merge($lJobsArray, $retJobsArray);
	  	$lOffset++;
	  	// Don't go over 10 pages (the maximum that a resultset will say that it has).
	  	if($lOffset == self::MAX_OFFSET) {
	  	  break;
	  	}
	  }
	  return $retJobsArray;
	}
	
	/* Private function to get job results. */
	private function _fetchJobs($pLimit, $pOffset = 0) {
	  $results = $this->doSearch($pLimit, $pOffset);
	  $lJobsArray = $this->_buildJobsArray($results);
	  return $lJobsArray;
	}
	
    /**
	 * Turns the response from SimplyHired into an array of jobs, for saving into a denormalized table.
	 *
	 * @param SimpleXMLElement $results - uses response for search (rq)
	 *
	 * @return int|void
	 *   The number of jobs in the array, or no return value if an error in feed.
	 */
	private function _buildJobsArray($results) {
		if($results->error) { 
		  $this->jobsArray = array(); // If the results had an error, jobsArray can't be set, so clear previous (if any).
		  return;
		}

		// Iterates over the r elements in the rs node of the XML document, setting the job values for each.
		$numJobs = 0;
		$lJobsArray = array();
		if(isset($results->rs->r)) {
		  foreach($results->rs->r as $res) {
		    // Source is always 'simplyhired'
		    $lJobsArray[$i]['source'] = self::SOURCE_NAME;
		    // Get the title.
		    $lJobsArray[$i]['title'] = xt_getInnerXML($res->jt);
		    // Get the organization name.
		    // If it is not in the cn tag, then check the src tag.
		    $cn_inner = xt_getInnerXML($res->cn);
		    $src_inner = xt_getInnerXML($res->src);
		    $lJobsArray[$i]['org_name'] = (!empty($cn_inner)) ? $cn_inner : $src_inner;
		    // Get the original URL from the url attribute on the src element.
		    $lJobsArray[$i]['referralurl'] = xt_getAttrVal($res->src['url']);
		    // Get the source GUID from that URL.
		    $lJobsArray[$i]['source_guid'] = $this->_getSourceGuid($lJobsArray[$i]['referralurl']);
		    // Get the location values from the attributes on loc element.
		    // All of these don't necessarily have values all of the time.
		    $lJobsArray[$i]['city'] = xt_getAttrVal($res->loc['cty']);
		    $lJobsArray[$i]['province'] = xt_getAttrVal($res->loc['st']);
		    $lJobsArray[$i]['postal_code'] = xt_getAttrVal($res->loc['postal']);
		    $country_code = xt_getAttrVal($res->loc['country']);
		    // Correct the country code for Great Britain.
		    $gb_countries = array('ENGLAND', 'SCOTLAND', 'NORTHERN IRELAND'); // England, Scotland, Northern Ireland
		    if(in_array($country_code, $gb_countries)) {
		  	  $country_code = 'GB';
		    }
		    $lJobsArray[$i]['country'] = $country_code;
		    // Get the created and changed dates.
		    $lJobsArray[$i]['created'] = strtotime(xt_getInnerXML($res->dp));
		    $lJobsArray[$i]['changed'] = strtotime(xt_getInnerXML($res->ls));
		    // Get the job description.
		    $lJobsArray[$i]['description'] = xt_getInnerXML($res->e);
		    // Teaser should have same value as description, for this provider.
		    $lJobsArray[$i]['teaser'] = $lJobsArray[$i]['description'];
		    $i++;
		  }
		}
		// Return the number of jobs in the array.
		return $lJobsArray;
	}
	
	private function _getSourceGuid($pUrl) {
	  $lGuid = '';
	  // Use a pattern with a PCRE named group to match the substring.
	  // The alphanumeric character class is used to restrict the match.
	  // @see http://www.php.net/manual/en/function.preg-match.php#108117
	  // @see http://www.php.net/manual/en/regexp.reference.character-classes.php
	  $lPattern = '/\/jobkey-(?P<guid>[a-zA-Z0-9.-]+)\//';
	  $lResults = array();
	  preg_match($lPattern, $pUrl, $lResults);
	  if(!empty($lResults['guid'])) {
	  	$lGuid = $lResults['guid'];
	  }
	  // Debug failed matches if we are logging and Krumo class exists.
	  else {
	  	if($this->is_logging && function_exists('krumo')) {
	  	  krumo($lResults);
	  	}
	  }
	  return $lGuid;
	}
	
	/**
	 * Prints the jobs array as HTML.
	 */
	public function printJobsResults(array $pJobsArray) {
	  if(count($pJobsArray) == 0) {
	  	return;
	  }	
	  else {
	  	// @todo: Nicer formatting, more fields.
	  	foreach($pJobsArray as $job) {
	  	  echo "<h1>" . $job['title'] . "</h1>";
	  	  echo "<p>Org Name: " . $job['org_name'] . "</p>";
	  	  echo "<p>Created: " . $job['created'] . "</p>";
	  	  echo "<p>Changed: " . $job['changed'] . "</p>";
	  	  echo "<p>" . $job['description'] . "</p>";
	  	}
	  }
	}
}
