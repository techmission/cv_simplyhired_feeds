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
		
		/* Constants */
        /* none as yet */
		
		/* Options */
		$this->options = $options;
		
		/* To query SimplyHired requires a Job-a-matic account. */
		if ( isset( $this->options['publisher_id'] ) 
			&& 	isset( $this->options['jobboard_url'] ) ) {
			/* Initialize SimplyHired API */
			$this->init( $this->options['publisher_id'], $this->options['jobboard_url'] );
			
			/* Set broad ONET code (can be over-ridden via shortcode ) */
			if ( $this->options['broad_onet_code'] ) {
				$this->set_onet($this->options['broad_onet_code']);
			}
			$this->set_is_usa( $this->options['is_usa'] );
			$this->set_disable_tracking( $this->options['disable_tracking']);
		}
	}
	
    /**
	 * Turns the response from SimplyHired into an array of jobs, for saving into a denormalized table.
	 *
	 * @param SimpleXMLElement $results - uses response for search (rq)
	 *
	 * @return array
	 */
	function get_jobs_array( $results ) {
		
		$jobs_array = array();
		
		if ( $results->error  ) 
			return $jobs_array; // If the results had an error, then return an empty array.
		
		// Iterates over the r elements in the rs node of the XML document, setting the job values for each.
		$i = 0;
		foreach($results->rs->r as $res) {
		  // Get the title.
		  $jobs_array[$i]['title'] = xt_getInnerXML($res->jt);
		  // Get the organization name.
		  $jobs_array[$i]['org_name'] = xt_getInnerXML($res->cn);
		  // Get the original url from the url attribute on the src element.
		  $jobs_array[$i]['referralurl'] = xt_getAttrVal($res->src['url']);
		  // Get the location values from the attributes on loc element.
		  $jobs_array[$i]['city'] = xt_getAttrVal($res->loc['cty']);
		  $jobs_array[$i]['province'] = xt_getAttrVal($res->loc['st']);
		  $jobs_array[$i]['postal_code'] = xt_getAttrVal($res->loc['postal']);
		  $jobs_array[$i]['country'] = xt_getAttrVal($res->loc['country']);
		  // Get the created and changed dates.
		  $jobs_array[$i]['created'] = strtotime(xt_getInnerXML($res->dp));
		  $jobs_array[$i]['changed'] = strtotime(xt_getInnerXML($res->ls));
		  // Get the job description.
		  $jobs_array[$i]['description'] = xt_getInnerXML($res->e);
		  $i++;
		}
		
		return $jobs_array;
	}
}
