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
	const SOURCE_NAME = 'SimplyHired'; // source name for database (gets displayed, potentially)
	
	const QRY_DEFAULT = TRUE; // if the default query should be used
	const LOCATION_DEFAULT = '02124'; // default search location
	
	const OP_OR = 'OR'; // operators to join query string
	const OP_AND = 'AND';
	
	const RES_SIZE_DEFAULT = 100; // by default query for the maximum that you can get in one page
	const RES_RADIUS_DEFAULT = 100; // query for a 100-mile radius by default
	
	const MAX_OFFSET = 9; // the maximum number of pages beyond the first page of results to get (1000 results total)

	const POSITION_TYPE_JOBS = 33389;
	const POSITION_TYPE_OPPS = 4794;

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
	public function buildDefaultQuery($pOperator = self::OP_OR, $pShowJobsOnly = TRUE) {
	  $lQryIncludesArray = _get_include_terms(); // terms shared between classes
      // Leave out terms that are not explicitly Christian.
      unset($lQryIncludesArray[116]);  // "group home"
      unset($lQryIncludesArray[117]);  // "soup kitchen"
      unset($lQryIncludesArray[118]);  // "food pantry"
      unset($lQryIncludesArray[119]);   // "homeless shelter"
	  // Leave out certain terms outside the US
	  if($this->country != 'en-us') {
	    unset($lQryIncludesArray[8]);    // "ministry" - b/c used in gov't jobs
	    unset($lQryIncludesArray[3]);    // "minister" - b/c used in gov't jobs
	    unset($lQryIncludesArray[12]);   // "faith" - shows up in non-discrimination statements
	  }
	  $lQryExcludesArray = _get_exclude_terms(); // terms shared between classes
	  // Put spaces around the operator.
	  $lOperator = ' ' . $pOperator . ' ';
	  $lDefaultQueryIncludes = '(' . implode($lOperator, $lQryIncludesArray) . ')';
	  $lDefaultQueryExcludes = '';
	  if(count($lQryExcludesArray) > 0) {
	    $lDefaultQueryExcludes = ' AND NOT ' . implode(' AND NOT ', $lQryExcludesArray);
	  }
	  // Query has both inclusions and exclusions.
	  // Also filter out volunteer and unpaid positions.
	  if($pShowJobsOnly == TRUE) {
	    $lDefaultQuery = $lDefaultQueryIncludes . $lDefaultQueryExcludes . ' AND NOT volunteer AND NOT unpaid';
	  }
	  else {
	    $lDefaultQuery = $lDefaultQueryIncludes . $lDefaultQueryExcludes . ' AND (volunteer OR unpaid)';
	  }
	  // urlencode the query
	  $lDefaultQuery = urlencode($lDefaultQuery);
	  return $lDefaultQuery;
	}

	/**
	 * Runs search, but only returns the number of items.
	 * @todo: Condense this and fetchJobs into a single function, or wrap the functionality somehow?
	 */
	public function fetchCount($pQuery = self::QRY_DEFAULT, $pShowJobsOnly = TRUE) {
	  $lCount = 0;
	  // Set query.
	  $lQuery = $pQuery;
	  if($pQuery == self::QRY_DEFAULT) {
	  	$lQuery = $this->buildDefaultQuery(self::OP_OR, $pShowJobsOnly);
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
	public function fetchJobs($pQuery = self::QRY_DEFAULT, $pLimit = self::RES_SIZE_DEFAULT, $pShowJobsOnly = TRUE) {
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
	  	$lQuery = $this->buildDefaultQuery(self::OP_OR, $pShowJobsOnly);
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
	  	$lJobsArray = $this->_fetchJobs($pLimit, $lOffset, $pShowJobsOnly);
	  	$retJobsArray = array_merge($lJobsArray, $retJobsArray);
	  	$lOffset++;
	  	// Don't go over 10 pages (the maximum that a resultset will say that it has).
	  	if($lOffset == self::MAX_OFFSET) {
	  	  break;
	  	}
	  }
	  return $retJobsArray;
	}
	
	// Fetch volunteer opportunities instead of jobs.
	public function fetchOpps($pQuery = self::QRY_DEFAULT, $pLimit = self::RES_SIZE_DEFAULT) {
	 return $this->fetchJobs($pQuery, $pLimit, FALSE);
	}
	
	/* Private function to get job results. */
	private function _fetchJobs($pLimit, $pOffset = 0, $pShowJobsOnly = TRUE) {
	  $results = $this->doSearch($pLimit, $pOffset);
	  $lJobsArray = $this->_buildJobsArray($results, $pShowJobsOnly);
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
	private function _buildJobsArray($results, $pShowJobsOnly = TRUE) {
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
		    $lJobsArray[$i]['location_city'] = xt_getAttrVal($res->loc['cty']);
		    $lJobsArray[$i]['location_province'] = xt_getAttrVal($res->loc['st']);
		    $lJobsArray[$i]['location_postal_code'] = xt_getAttrVal($res->loc['postal']);
		    $country_code = xt_getAttrVal($res->loc['country']);
		    // Correct the country code for Great Britain.
		    $gb_countries = array('ENGLAND', 'SCOTLAND', 'NORTHERN IRELAND'); // England, Scotland, Northern Ireland
		    if(in_array($country_code, $gb_countries)) {
		  	  $country_code = 'GB';
		    }
		    $lJobsArray[$i]['location_country'] = $country_code;
		    // Get the created and changed dates.
		    $lJobsArray[$i]['created'] = strtotime(xt_getInnerXML($res->dp));
		    $lJobsArray[$i]['changed'] = strtotime(xt_getInnerXML($res->ls));
		    // Get the job description.
		    $lJobsArray[$i]['description'] = xt_getInnerXML($res->e);
		    // Teaser should have same value as description, for this provider.
		    $lJobsArray[$i]['short_description'] = $lJobsArray[$i]['description'];
		    // Set the position type tid. Use 33389 for jobs, and 4794 for volunteer opportunities.
		    if($pShowJobsOnly == TRUE) {
		      $lJobsArray[$i]['position_type'] = self::POSITION_TYPE_JOBS;
		    }
		    else {
		      $lJobsArray[$i]['position_type'] = self::POSITION_TYPE_OPPS;
		    }
		    $i++;
		  }
		}
		// Return the number of jobs in the array.
		return $lJobsArray;
	}
	
	private function _getSourceGuid($pUrl) {
	  $lGuid = '';
	  // Use a pattern with a PCRE named group to match the substring.
	  // Negate the forward slash to match everything else.
	  // @see http://www.php.net/manual/en/function.preg-match.php#108117
	  // @see http://www.php.net/manual/en/regexp.reference.character-classes.php
	  $lPattern = '/\/jobkey-(?P<guid>[^\/]+)\//';
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
