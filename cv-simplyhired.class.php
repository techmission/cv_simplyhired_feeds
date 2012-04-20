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
require_once('simplyhired-api.class.php');

/* This file contains utility functions for parsing XML. */
require_once('xmltools.php');

/**
 * Main plugin class
 */
class CV_SimplyHired_API extends SimplyHired_API {

    private $jobsArray = array(); // only accessible through the methods
    
	/* Options passed in when instantiating class. -
	   this is a public variable so that it can be modified later, if needed. */
	public $options = array();
	
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
			$this->setDisableTracking( $this->options['disable_tracking']);
		}
	}
	
	/**
	 * Returns the jobs array.
	 */
	public function getJobsArray() {
	  if(is_array($this->jobsArray) && count($this->jobsArray) > 0) {
	  	return $this->jobsArray;
	  }
	  else {
	    if(!empty($this->query) && !empty($this->location)) {
	  	  $results = $this->doSearch();
	  	  $this->setJobsArray($results);
	  	  return $this->jobsArray;
	    }
	  }
	}
	
    /**
	 * Turns the response from SimplyHired into an array of jobs, for saving into a denormalized table.
	 *
	 * @param SimpleXMLElement $results - uses response for search (rq)
	 *
	 * @return int|void
	 *   The number of jobs in the array, or no return value if an error in feed.
	 */
	private function setJobsArray($results) {
		if($results->error) { 
		  $this->jobsArray = array(); // If the results had an error, jobsArray can't be set, so clear previous (if any).
		  return;
		}

		// Iterates over the r elements in the rs node of the XML document, setting the job values for each.
		$numJobs = 0;
		$lJobsArray = array();
		foreach($results->rs->r as $res) {
		  // Get the title.
		  $lJobsArray[$i]['title'] = xt_getInnerXML($res->jt);
		  // Get the organization name.
		  $lJobsArray[$i]['org_name'] = xt_getInnerXML($res->cn);
		  // Get the original url from the url attribute on the src element.
		  $lJobsArray[$i]['referralurl'] = xt_getAttrVal($res->src['url']);
		  // Get the location values from the attributes on loc element.
		  $lJobsArray[$i]['city'] = xt_getAttrVal($res->loc['cty']);
		  $lJobsArray[$i]['province'] = xt_getAttrVal($res->loc['st']);
		  $lJobsArray[$i]['postal_code'] = xt_getAttrVal($res->loc['postal']);
		  $lJobsArray[$i]['country'] = xt_getAttrVal($res->loc['country']);
		  // Get the created and changed dates.
		  $lJobsArray[$i]['created'] = strtotime(xt_getInnerXML($res->dp));
		  $lJobsArray[$i]['changed'] = strtotime(xt_getInnerXML($res->ls));
		  // Get the job description.
		  $lJobsArray[$i]['description'] = xt_getInnerXML($res->e);
		  $i++;
		}
		
		// Set the jobs array variable.
		$this->jobsArray = $lJobsArray;
		
		// Return the number of jobs in the array.
		return $numJobs;
	}
	
	/**
	 * Prints the jobs array as HTML.
	 */
	public function printJobsResults() {
	  if(empty($this->jobsArray)) {
	  	return;
	  }	
	  else {
	  	// @todo: Nicer formatting, more fields.
	  	foreach($this->jobsArray as $job) {
	  	  echo "<h1>" . $job['title'] . "</h1>";
	  	  echo "<p>Org Name: " . $job['org_name'] . "</p>";
	  	  echo "<p>Created: " . $job['created'] . "</p>";
	  	  echo "<p>Changed: " . $job['changed'] . "</p>";
	  	  echo "<p>" . $job['description'] . "</p>";
	  	}
	  }
	}
}
