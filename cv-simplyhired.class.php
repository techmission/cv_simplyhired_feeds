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

/* This file contains utility functions for parsing XML. */
require_once(dirname(__FILE__) . '/xmltools.php');

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
	
	const MAX_OFFSET = 4; // the maximum number of pages beyond the first page of results to get

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
	  $lQryArray = array('pastor',
	  		             'church',
	  		             'chaplain',
	  		             'minister',
	  		             'christian', 
	  		             'jesus', 
	  		             'gospel', 
	  		             'catholic',
	  		             'ministry',
	  		             'religious',
	  		             'evangelical',
	  		             'christ',
	  		             'faith',
	  		             'Protestant',
	  		             'missionary',
	  		             '"rescue mission"',
	  		             '"Union Mission"',
	  		             '"Salvation Army"',
	  		             '"World Vision"',
	  		             'missionary',
	  		             'baptist',
	  		             'lutheran',
	  		             'methodist',
	  		             'presbyterian',
	  		             'pentecostal',
	  		             'denominational',
	  		             'evangelical',
	  		             'calvary',
	  		             '"born again"',
	  		             'orthodox',
	  		             'anglican',
	  		             'reformed', 
	  		             'god',
	  		             'apostolic',
	  		             'worship',
	  		             'choir',
	  		         );
	  return $lQryArray; 	
	}
	
	private function _getDefaultQueryExcludes() {
	  $lQryArray = array('Muslim', 'Jewish', 'hospital');
	  return $lQryArray;
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
	  else if(is_string($lQuery) && !empty($lQuery)) {
	  	$this->setQuery($lQuery);
	  }
	  // Set location if not already set.
	  if(empty($this->location)) {
	  	$this->setLocation = self::LOCATION_DEFAULT;
	  }
	  // Fetch jobs.
	  $lJobsArray = $retJobsArray = $this->_fetchJobs($pLimit);
	  /* while(count($lJobsArray) == 100) {
	  	$lJobsArray = $this->_fetchJobs($pLimit, $lOffset);
	  	$retJobsArray = array_merge($lJobsArray, $retJobsArray);
	  	$lOffset++;
	  	// Don't go over 4 pages.
	  	if($lOffset == self::MAX_OFFSET) {
	  	  break;
	  	}
	  } */
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
		  $lJobsArray[$i]['country'] = xt_getAttrVal($res->loc['country']);
		  // Get the created and changed dates.
		  $lJobsArray[$i]['created'] = strtotime(xt_getInnerXML($res->dp));
		  $lJobsArray[$i]['changed'] = strtotime(xt_getInnerXML($res->ls));
		  // Get the job description.
		  $lJobsArray[$i]['description'] = xt_getInnerXML($res->e);
		  // Teaser should have same value as description, for this provider.
		  $lJobsArray[$i]['teaser'] = $lJobsArray[$i]['description'];
		  $i++;
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
	  //krumo(array('url' => $pUrl, 'results' => $lResults));
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
