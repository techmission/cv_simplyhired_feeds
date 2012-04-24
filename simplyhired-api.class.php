<?php
/***************************************************************/
/* 
	SimplyHiredAPI - a PHP class wrapper to access the SimplyHired API
	@author   Ronnie T. Dodger
	@url      http://webstractions.com
	@version  1.0 

	Software License Agreement (BSD License)

	Copyright (C) 2011, Webstractions Web Development.
	All rights reserved.
  
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	 * Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.
	 * Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	 * Neither the name of Ronnie T. Dodger or Webstractions Web Development
	   may be used to endorse or promote products derived from this software 
	   without specific prior written permission of Edward Eliot.

	  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" AND ANY
	  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
	  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/
/***************************************************************/

/**
 *  Modifications to the class by Evan Donovan.
 */

// @todo: Finish the refactor so all code comforms to the same standard: CamelCase for all OOP.

class SimplyHired_API {

	/* API Endpoint URI */
	public $endpoint = 'http://api.simplyhired.com/a/jobs-api/xml-v2/';

	/* Publisher ID */
	public $pshid = '30845';
	
	/* Job-a-matic Url */
	public $jbd = 'christianjobsdirectory.jobamatic.com';

	/* Client IP Address.  Needs to be captured and sent for each API call. */
	public $clip = '';
	
	/* O*NET code to filter search results with */
	public $onet = ''; // Not used by TechMission.
	
	/**/
	public $is_usa = TRUE; // Use US opportunities by default.
	
	/**/
	public $disable_tracking = FALSE;
	
	/* Search error (if any) 
	 * @todo: Set this to a string, not SimpleXMLElement.
	 */
	private $search_error = '';

	/*
	 * API call variables for Query, Location, Page Number, Radius. 
	 *
	 */
	public $query    = '';
	public $location = '';
	public $pagenum  = 1;
	public $radius = 25;
	
	public $apicall  = '';
	public $results = '';
	
	/* Note that there is no constructor, since it must be initialized from a subclass. 
	 * @todo: Mixins once we have multiple APIs to deal with.
	 * */
	
	function init( $pshid = false, $jbd = false ) {
		if( $pshid ){ 
			$this->pshid = $pshid;  	// Publisher ID assigned by SimplyHired
		}
		if( $jbd ) {
			$this->jbd = $jbd;		// Jobboard Url assigned by SimplyHired
		}
		$this->clip = $this->getClientIP();
	
	}

	function doSearch($number = 10, $start = 1) {
		// Build the SimplyHired API call from parameters.
		$this->apicall = $this->_buildApiCall($number, $start);

		// Get the result XML.
		$xmlstr = @file_get_contents($this->apicall);
		if(!$xmlstr == null ) {
		  $xml = new SimpleXMLElement($xmlstr);
	     }
		if( empty($xml) || $xml == null ) {
		  return null;
		}
		$this->results = $xml;
		
		// Set the search error if there was an error.
		if($xml->error) {
		  $this->search_error = $xml->error->asXML();
		}
		
		// Return SimpleXMLElement tree of results.
		return $xml;
	}
	
	/**
	 *  Build the API call as per parameters defined on 
	 *  https://www.jobamatic.com/a/jbb/partner-dashboard-advanced-xml-api.
	 */
	private function _buildApiCall($number, $start) {
	  if(!empty($this->onet)) {
		$onet_filter = 'onet:(' . $this->onet . ')+';
	  }
	  // Set the "search style" parameter (ssty) based on whether
	  // the searched location is in the US or not.
	  if($this->is_usa == TRUE) {
	    $ssty = '&ssty=2';
	  }
	  else {
	  	$ssty = '&ssty=3';
	  }
	  $lApiCall = $this->endpoint . 'q-' . $onet_filter . $this->query . '/l-' . $this->location . '/mi-' . $this->radius . '/ws-' . $number . '/pn-' . $start . '/sb-dd?pshid=' . $this->pshid .  $ssty . '&cflg=r&jbd=' . $this->jbd . '&clip=' . $this->clip;
	  return $lApiCall;
	}
	
	function setQuery( $query ) {
		$this->query = $query;
	}
	
	function setOnet( $code ) {
		$this->onet = $code;
	}

	/* Modified so that the CLI script can use short codes for locations. */
	function setLocation( $location ) {
		// Look up a location in the countries array.
		$prefix = substr($location, 0, 2);
	    if($prefix == 'en') {
	      $countries = $this->_listAllowedCountries();
	      if(array_key_exists($location, $countries)) {
	      	// Set that this is a search outside the US, if the location is not "en-us".
	      	if($location != 'en-us') {
	      	  $this->setIsUsa(FALSE);
	      	}
	      	// Set the country's name from the lookup array.
	      	$location = $countries[$location];
	      }
	    }
	    // Set the location name.
		$this->location = $location;
	}
	
	// Set the radius for searches.
	function setRadius($radius) {
	  $this->radius = $radius;	
	}
	
	function setIsUsa( $bool ) {
		$this->is_usa = $bool;
	}

	function setDisableTracking( $bool ) {
		$this->disable_tracking = $bool;
	}

	function getDisableTracking() {
		return $this->disable_tracking;
	}

	function getClientIP() {
		$ip = '';
		if (getenv("HTTP_CLIENT_IP")) {
		  $ip = getenv("HTTP_CLIENT_IP");
	    }
		else if(getenv("HTTP_X_FORWARDED_FOR")) {
		  $ip = getenv("HTTP_X_FORWARDED_FOR");
		}
		else if(getenv("REMOTE_ADDR")) {
		  $ip = getenv("REMOTE_ADDR");
		}
		// Use gethostbyname if running from command line.
		// Requires PHP 5.3
		else if(PHP_SAPI === "cli") {
		  $ip = gethostbyname(gethostname());
		}
		else {
		  // Note that this will return no results, since a valid IP is required.
		  $ip = "UNKNOWN";
		}
		return $ip;
	} 
	
	function setPagenum( $num ) {
		if ( $num > 1 ) {
			$this->pagenum = $num;
		}
	}
	
	/*
	 * Prints the Simply Hired attribution (per terms) to the screen
	 *
	 */
	 function printAttribution( $echo=true ) {
	 
		$output = '<div style="text-align: right;"><a style="text-decoration:none" href="http://www.simplyhired.com/" rel="nofollow"><span style="color: rgb(128, 128, 129);">Jobs</span></a> by <a style="text-decoration:none" href="http://www.simplyhired.com/"><span style="color: rgb(80, 209, 255); font-weight: bold;">Simply</span><span style="color: rgb(203, 244, 104); font-weight: bold;">Hired</span></a></div>';
		if ($echo)
			echo $output;
		else
			return $output;
	 }
	 
	 function getFooterScripts() {
		$output = '
<!-- SimplyHired click tracking -->		
<script type="text/javascript" src="http://api.simplyhired.com/c/jobs-api/js/xml-v2.js"></script>
';
		return $output;
	 }
	 
	 function printFooterScripts() {
		$output = '
<!-- SimplyHired click tracking -->		
<script type="text/javascript" src="http://api.simplyhired.com/c/jobs-api/js/xml-v2.js"></script>
';
		echo $output;
	 }
	 
	 function printApiCall( $echo=true ) {
	 
	 $html = '<span class="apicall" style="float:right;"><a href="' . $this->apicall . '" target="_blank">View XML</a></span>';
	 
	 if ( $echo )
		echo $html; 
	 else 
		return $html; 

	}
	
	/* @todo: Use the class variable that I created. */
	function printError ($echo = TRUE) {
	  if(isset($this->results->error)) {
	    $error_msg = $results->error->text;
	    $html = '<span class="error-message">' . $error_msg . '</span>';
      }
	  else {
	    $html = '<span class="error-message">No errors in feed.</span>';
      }
	  
	  if ($echo)
		echo $html;
	  else
		return $html;
	}
	
	function parseResultNum($results) {
	  // Parses out result number from the results XML.
	  $result_num = $results->rq->tv;
	  // If more than 1000, the initial resultset will not say by how much.
	  if($result_num == '1000') {
		$result_num = '>1000';
	  }
	  return $result_num;
	}
	
	function printResultTotals($echo = TRUE) {
		/* Total results display */
		$result_start = $this->results->rq->si + 1;
		$result_end = $this->results->rq->si + $this->results->rq->rpd;
		if( $result_end > $this->results->rq->tv )
			$result_end = $this->results->rq->tv;
		$result_num = $this->parseResultNum($this->results);
		if($result_num == '>1000') {
		  $result_num = 'over 1000 results';
		}
		else {
		  $result_num .= ' total results';
		}
		
		$html = '<span class="results-total">Displaying results ' . $result_start . '-' . $result_end . ' of ' . $result_num . '</span>';
		
		if ($echo)
			echo $html;
		else
			return $html;
	
	}
	
	/* Defines the allowed countries for searching. Used in setLocation. */
	private function _listAllowedCountries() {
	  $lCountries = array('en-us' => 'United States',       // ssty=2
                   'en-ar' => 'Argentina',                  /* this, and all below, ssty=3 */
                   'en-au' => 'Australia',                  
                   'en-at' => 'Austria',                    
                   'en-be' => 'Belgium',                  
                   'en-br' => 'Brazil',
                   'en-ca' => 'Canada',
                   'en-cn' => 'China',
                   'en-fr' => 'France',
                   'en-de' => 'Germany',
                   'en-in' => 'India',
                   'en-ie' => 'Ireland',
                   'en-it' => 'Italy',
                   'en-jp' => 'Japan',
                   'en-kr' => 'Korea',
                   'en-mx' => 'Mexico',
                   'en-nl' => 'Netherlands',
                   'en-pt' => 'Portugal',
                   'en-ru' => 'Russia',
                   'en-za' => 'South Africa',
                   'en-es' => 'Spain',
                   'en-se' => 'Sweden',
                   'en-ch' => 'Switzerland',
                   'en-gb' => 'United Kingdom',
	   );
	  return $lCountries;
	}
}