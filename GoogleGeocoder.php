<?php

/**
 * @file
 * Google geocoder.
 * Object-oriented version of the Google geocoder included as part of the Drupal Location module.
 * 
 * @todo Add more robust logging.
 * 
 */

class GoogleGeocoder {

	private $key = ''; // the API key (must be set to valid for domain to work)
	public static $isLogging = FALSE; // whether or not to log to the screen
	
	const ENDPOINT_URL = 'http://maps.google.com/maps/geo'; // Endpoint URL for the Gmap v2 API
	
	// List of valid countries for Google Maps geocoder.
	// @todo: Find out where this is used.
	const COUNTRIES_LIST_URL = 'http://spreadsheets.google.com/feeds/list/p9pdwsai2hDMsLkXsoM05KQ/default/public/values';

	/**
	 * Class constructor.
	 * Requires API key for Google Maps v2 API.
	 * 
	 * @param string $pKey
	 *   The API key
	 * 
	 * @throws Exception
	 */
	function __construct($pKey) {
	  if(is_string($pKey) && !empty($pKey)) {
	    // Sets the API key based on what was passed in.
	    $this->key = $pKey;
	  }
	  else {
	  	// @todo: Also throw an exception if the API key is not valid for this domain.
	  	throw new Exception('Invalid Gmap API key.');
	  }
	}	
	
	/**
	 * Performs geocoding on a location array, using the Google v2 geocoding API.
	 * 
	 * @todo: Use v3 API.
	 * 
	 * @param array $location
	 *   The location array to process.
	 * @param bool $reverse
	 *   Whether to perform reverse geocoding (address from lat/lon) or not.
	 *   Default FALSE (i.e., regular geocoding.)
	 * @return
	 *   an associative array with keys 'lat' and 'lon' containing the coordinates.
	 *   
	 *   was google_geocode_location()
	 */
	public function geocodeLocation(array $location, $reverse = FALSE) {
	    // Builds query.
	    $query = $this->_buildQuery($location, $reverse);
	    
	    // Sets location and json_response variable to default to empty array.
		$location = array();
		$json_response = array();
		
		// Makes the HTTP request.
		$response = make_http_request(self::ENDPOINT_URL, $query);
		dpm($response, 'http response');
		
		// Parses the response using json_decode (expects a JSON string).
		if($response->code = 200 && !empty($response->body)) {
		  $json_response = json_decode($response->body, TRUE);
		}
		dpm($json_response, 'Google-returned json array');
	    
		// Checks whether Google says this is a valid request.
		$api_status = $this->_checkResponseStatus($json_response);
		dpm($api_status, 'api status');
		
		// If this was a valid response, then parse for the location.
		// An empty array will be returned if no valid location could be found.
        if($api_status == TRUE) {
          $location = $this->_parseLocation($json_response);
        }
        dpm($location, 'location array');
        
        return $location;
	}
	
	/**
	 * Builds the query for a request to Google Maps.
	 *
	 * @param array $location
	 *   The location array
	 * @param bool $reverse
	 *   Whether reverse geocoding is needed
	 */
	private function _buildQuery(array $pLocation, $pReverse = FALSE) {
		$key = $this->key;
		 
		$query = array();
		 
		$gmap_q = $this->_flattenQuery($pLocation, $pReverse);
		 
		// Query parameters must be in associative array to be used with HttpRequest.
		$query = array(
				'key' => $key,
				'sensor' => 'false', // Required by TOS.
				'output' => 'json',
				//'ll' => 0,
				//'spn' => 0,
				'q' => $gmap_q,
		);
		if(!empty($pLocation['country'])) {
			$query['gl'] = $pLocation['country'];
		}
		return $query;
	}
	
	/**
	 * Checks whether this is a JSON response from Google
	 * that can be parsed into a location array.
	 *
	 * @param array $pJsonResponse
	 */
	private function _checkResponseStatus($pJsonResponse) {
		$lStatus = TRUE; // If true, then can continue in main geocoding method.
		if(is_array($pJsonResponse) && isset($pJsonResponse['Status']['code'])) {
			$status_code = $pJsonResponse['Status']['code'];
			if ($status_code != 200) {
				if ($status_code == 620) {
					//echo 'Google geocoding returned status code: ' . $status_code . ' This usually means you have been making too many requests within a short window of time.';
				}
				else if ($status_code == 602) {
					//echo 'Google geocoding return status code: ' . $status_code . ' This usually means that the format you used for the address was incorrect.';
				}
				else {
					//echo 'Google geocoding returned status code:  ' . $status_code;
				}
				$lStatus = FALSE;
			}
		}
		else {
			$lStatus = FALSE;
		}
		return $lStatus;
	}
	
	/**
	 * Parses out a location from a v2 Google Maps API response.
	 * Location data is returned as an associative array from the JSON response of the Google geocoder.
	 * This collapses the 
	 *
	 * @param array $pJsonResponse
	 * 
	 * @return array
	 *   An array of all the valid values that were found within the JSON response.
	 */
	private function _parseLocation($pJsonResponse) {
		$geocoded_location = array();
		
		// Latitude
		if(isset($pJsonResponse['Placemark'][0]['Point']['coordinates'][1])) {
			$geocoded_location['latitude'] = $pJsonResponse['Placemark'][0]['Point']['coordinates'][1];
		}
		// Longitude
		if(isset($pJsonResponse['Placemark'][0]['Point']['coordinates'][0])) {
			$geocoded_location['longitude'] = $pJsonResponse['Placemark'][0]['Point']['coordinates'][0];
		}
		// Street
		if(isset($pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['Thoroughfare']['ThoroughfareName'])) {
			$geocoded_location['street'] = $pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['Thoroughfare']['ThoroughfareName'];
		}
		// City
		if(isset($pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['LocalityName'])) {
			$geocoded_location['city'] = $pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['LocalityName'];
		}
		// Province
		if(isset($pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['AdministrativeAreaName'])) {
			$geocoded_location['province'] = $pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['AdministrativeAreaName'];
		}
		// Postal Code
		if(isset($pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['PostalCode']['PostalCodeNumber'])) {
			$geocoded_location['postal_code'] = $pJsonResponse['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['PostalCode']['PostalCodeNumber'];
		}
		// Country
		if(isset($pJsonResponse['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'])) {
			$geocoded_location['country'] = $pJsonResponse['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'];
		}
		// Accuracy (by Google's standards)
		// @todo: Use this somehow?
		if(isset($pJsonResponse['Placemark'][0]['AddressDetails']['Accuracy'])) {
			$geocoded_location['accuracy'] = $pJsonResponse['Placemark'][0]['AddressDetails']['Accuracy'];
		}
		return $geocoded_location;
	}

	
	/**
	 * Returns general information about this geocoder.
	 */
	public function getInfo() {
		return array(
				'name' => 'Google Maps',
				'url' => 'http://maps.google.com',
				'tos' => 'http://www.google.com/help/terms_local.html',
				'general' => TRUE,
		);
	}

	/**
	 * Returns the list of ISO3166 codes supported by this geocoder.
	 * Coverage list: http://gmaps-samples.googlecode.com/svn/trunk/mapcoverage_filtered.html
	 * Coverage list feed: http://spreadsheets.google.com/feeds/list/p9pdwsai2hDMsLkXsoM05KQ/default/public/values
	 *
	 * was google_geocode_country_list()
	 */
	public function listCountries() {
		// Get the google data from the feed.
		$xml = $this->_listCountriesXml;

		// Loop through google data and find all valid entries.
		$regionclean = array();
		foreach($xml->entry as $region) {
			$pos = strpos($region->content, 'geocoding:') + 11;
			$geocoding = substr($region->content, $pos, strpos($region->content, ',', $pos) - $pos);
			if (strpos($geocoding, "Yes") !== FALSE) {
				$regionclean[] = htmlentities($region->title);
			}
		}

		// Get the countries list and clean it up so that names will match to google.
		// The regex removes parenthetical items so that both of the "Congo" entries
		// and the "Coco Islands" work.
		// The $countriesfixes overwrites values in the Drupal API countries list
		// with values that will match to google's entries.
		// "Sao Tome and Principe" are non-accented in the Drupal API so the entry
		// here is to match the htmlentities() fix in the foreach loop below.
		// Note: it may be neccessary to adjust/add to the fixes list in the future
		// if google adds countries that don't match the Drupal API list for whatever
		// reason.
		$countries = $this->listCountriesISO();
		$regex = "#[ (].*[)]#e";
		$cntryclean = preg_replace($regex, "", $countries);
		$countriesfixes = array_merge($cntryclean, array(
				"hk" => "China",
				"mo" => "China",
				"pn" => "Pitcairn Islands",
				"wf" => "Wallis Futuna",
				"st" => "S&Atilde;&pound;o Tom&Atilde;&copy; and Pr&Atilde;&shy;ncipe",
		));

		// Compare new google data found to fixed country name values and return
		// matches with abbreviations as keys.
		$googlematched = array_intersect($countriesfixes, $regionclean);

		// Compare new keys to original Drupal API and return the array with the
		// original name values.
		$fixedkeys = array_intersect_key($countries, $googlematched);
		return array_keys($fixedkeys);
	}

	/**
	 * Lists all countries by name in an array keyed by ISO code.
	 *
	 * From Drupal Location module - location_get_iso3166_list()
	 *
	 * @param bool $upper
	 *   Whether to convert to uppercase as per ISO standard.
	 *
	 * @return array
	 */
	public function listCountriesISO($upper = FALSE) {
		static $countries;

		if (isset($countries)) {
			// In fact, the ISO codes for countries are all Upper Case.
			// So, if someone needs the list as the official records,
			// it will convert.
			if (!empty($upper)) {
				return array_change_key_case($countries, CASE_UPPER);
			}
			return $countries;
		}

		$countries = array(
				'ad' => 'Andorra',
				'ae' => 'United Arab Emirates',
				'af' => 'Afghanistan',
				'ag' => 'Antigua and Barbuda',
				'ai' => 'Anguilla',
				'al' => 'Albania',
				'am' => 'Armenia',
				'an' => 'Netherlands Antilles',
				'ao' => 'Angola',
				'aq' => 'Antarctica',
				'ar' => 'Argentina',
				'as' => 'American Samoa',
				'at' => 'Austria',
				'au' => 'Australia',
				'aw' => 'Aruba',
				'ax' => 'Aland Islands',
				'az' => 'Azerbaijan',
				'ba' => 'Bosnia and Herzegovina',
				'bb' => 'Barbados',
				'bd' => 'Bangladesh',
				'be' => 'Belgium',
				'bf' => 'Burkina Faso',
				'bg' => 'Bulgaria',
				'bh' => 'Bahrain',
				'bi' => 'Burundi',
				'bj' => 'Benin',
				'bm' => 'Bermuda',
				'bn' => 'Brunei',
				'bo' => 'Bolivia',
				'br' => 'Brazil',
				'bs' => 'Bahamas',
				'bt' => 'Bhutan',
                'bv' => 'Bouvet Island',
				'bw' => 'Botswana',
				'by' => 'Belarus',
				'bz' => 'Belize',
				'ca' => 'Canada',
				'cc' => 'Cocos (Keeling) Islands',
				'cd' => 'Congo (Kinshasa)',
                'cf' => 'Central African Republic',
				'cg' => 'Congo (Brazzaville)',
                'ch' => 'Switzerland',
				'ci' => 'Ivory Coast',
				'ck' => 'Cook Islands',
				'cl' => 'Chile',
				'cm' => 'Cameroon',
				'cn' => 'China',
				'co' => 'Colombia',
				'cr' => 'Costa Rica',
                'cs' => 'Serbia And Montenegro', // Transitional reservation
				'cu' => 'Cuba',
				'cv' => 'Cape Verde',
				'cx' => 'Christmas Island',
				'cy' => 'Cyprus',
				'cz' => 'Czech Republic',
				'de' => 'Germany',
				'dj' => 'Djibouti',
				'dk' => 'Denmark',
				'dm' => 'Dominica',
				'do' => 'Dominican Republic',
				'dz' => 'Algeria',
				'ec' => 'Ecuador',
				'ee' => 'Estonia',
				'eg' => 'Egypt',
				'eh' => 'Western Sahara',
				'er' => 'Eritrea',
				'es' => 'Spain',
				'et' => 'Ethiopia',
				'fi' => 'Finland',
				'fj' => 'Fiji',
				'fk' => 'Falkland Islands',
				'fm' => 'Micronesia',
				'fo' => 'Faroe Islands',
				'fr' => 'France',
				'ga' => 'Gabon',
				'gd' => 'Grenada',
				'ge' => 'Georgia',
				'gf' => 'French Guiana',
				'gg' => 'Guernsey',
				'gh' => 'Ghana',
				'gi' => 'Gibraltar',
				'gl' => 'Greenland',
				'gm' => 'Gambia',
				'gn' => 'Guinea',
				'gp' => 'Guadeloupe',
				'gq' => 'Equatorial Guinea',
				'gr' => 'Greece',
                'gs' => 'South Georgia and the South Sandwich Islands',
				'gt' => 'Guatemala',
				'gu' => 'Guam',
                'gw' => 'Guinea-Bissau',
				'gy' => 'Guyana',
                'hk' => 'Hong Kong S.A.R., China',
                'hm' => 'Heard Island and McDonald Islands',
				'hn' => 'Honduras',
				'hr' => 'Croatia',
				'ht' => 'Haiti',
				'hu' => 'Hungary',
				'id' => 'Indonesia',
				'ie' => 'Ireland',
				'il' => 'Israel',
				'im' => 'Isle of Man',
				'in' => 'India',
				'io' => 'British Indian Ocean Territory',
				'iq' => 'Iraq',
				'ir' => 'Iran',
				'is' => 'Iceland',
				'it' => 'Italy',
				'je' => 'Jersey',
				'jm' => 'Jamaica',
				'jo' => 'Jordan',
				'jp' => 'Japan',
				'ke' => 'Kenya',
				'kg' => 'Kyrgyzstan',
				'kh' => 'Cambodia',
				'ki' => 'Kiribati',
				'km' => 'Comoros',
				'kn' => 'Saint Kitts and Nevis',
				'kp' => 'North Korea',
				'kr' => 'South Korea',
				'kw' => 'Kuwait',
				'ky' => 'Cayman Islands',
				'kz' => 'Kazakhstan',
				'la' => 'Laos',
				'lb' => 'Lebanon',
				'lc' => 'Saint Lucia',
				'li' => 'Liechtenstein',
				'lk' => 'Sri Lanka',
				'lr' => 'Liberia',
				'ls' => 'Lesotho',
				'lt' => 'Lithuania',
				'lu' => 'Luxembourg',
				'lv' => 'Latvia',
				'ly' => 'Libya',
				'ma' => 'Morocco',
				'mc' => 'Monaco',
				'md' => 'Moldova',
				'me' => 'Montenegro',
				'mg' => 'Madagascar',
				'mh' => 'Marshall Islands',
				'mk' => 'Macedonia',
				'ml' => 'Mali',
				'mm' => 'Myanmar',
				'mn' => 'Mongolia',
				'mo' => 'Macao S.A.R., China',
				'mp' => 'Northern Mariana Islands',
				'mq' => 'Martinique',
				'mr' => 'Mauritania',
				'ms' => 'Montserrat',
				'mt' => 'Malta',
				'mu' => 'Mauritius',
				'mv' => 'Maldives',
				'mw' => 'Malawi',
				'mx' => 'Mexico',
				'my' => 'Malaysia',
				'mz' => 'Mozambique',
				'na' => 'Namibia',
				'nc' => 'New Caledonia',
				'ne' => 'Niger',
				'nf' => 'Norfolk Island',
				'ng' => 'Nigeria',
				'ni' => 'Nicaragua',
				'nl' => 'Netherlands',
				'no' => 'Norway',
				'np' => 'Nepal',
				'nr' => 'Nauru',
				'nu' => 'Niue',
				'nz' => 'New Zealand',
				'om' => 'Oman',
				'pa' => 'Panama',
				'pe' => 'Peru',
				'pf' => 'French Polynesia',
				'pg' => 'Papua New Guinea',
				'ph' => 'Philippines',
				'pk' => 'Pakistan',
				'pl' => 'Poland',
				'pm' => 'Saint Pierre and Miquelon',
				'pn' => 'Pitcairn',
				'pr' => 'Puerto Rico',
				'ps' => 'Palestinian Territory',
				'pt' => 'Portugal',
				'pw' => 'Palau',
				'py' => 'Paraguay',
				'qa' => 'Qatar',
				're' => 'Reunion',
				'ro' => 'Romania',
				'rs' => 'Serbia',
				'ru' => 'Russia',
				'rw' => 'Rwanda',
				'sa' => 'Saudi Arabia',
				'sb' => 'Solomon Islands',
				'sc' => 'Seychelles',
				'sd' => 'Sudan',
				'se' => 'Sweden',
				'sg' => 'Singapore',
				'sh' => 'Saint Helena',
				'si' => 'Slovenia',
				'sj' => 'Svalbard and Jan Mayen',
				'sk' => 'Slovakia',
				'sl' => 'Sierra Leone',
				'sm' => 'San Marino',
				'sn' => 'Senegal',
				'so' => 'Somalia',
				'sr' => 'Suriname',
				'st' => 'Sao Tome and Principe',
				'sv' => 'El Salvador',
				'sy' => 'Syria',
				'sz' => 'Swaziland',
				'tc' => 'Turks and Caicos Islands',
				'td' => 'Chad',
				'tf' => 'French Southern Territories',
				'tg' => 'Togo',
				'th' => 'Thailand',
				'tj' => 'Tajikistan',
				'tk' => 'Tokelau',
				'tl' => 'East Timor',
				'tm' => 'Turkmenistan',
				'tn' => 'Tunisia',
				'to' => 'Tonga',
				'tr' => 'Turkey',
				'tt' => 'Trinidad and Tobago',
				'tv' => 'Tuvalu',
				'tw' => 'Taiwan',
				'tz' => 'Tanzania',
				'ua' => 'Ukraine',
				'ug' => 'Uganda',
				'uk' => 'United Kingdom',
				'um' => 'United States Minor Outlying Islands',
				'us' => 'United States',
				'uy' => 'Uruguay',
				'uz' => 'Uzbekistan',
				'va' => 'Vatican',
				'vc' => 'Saint Vincent and the Grenadines',
				've' => 'Venezuela',
				'vg' => 'British Virgin Islands',
				'vi' => 'U.S. Virgin Islands',
				'vn' => 'Vietnam',
				'vu' => 'Vanuatu',
				'wf' => 'Wallis and Futuna',
				'ws' => 'Samoa',
				'ye' => 'Yemen',
				'yt' => 'Mayotte',
				'za' => 'South Africa',
				'zm' => 'Zambia',
				'zw' => 'Zimbabwe',
		);

		// Sort the list.
		natcasesort($countries);

		// In fact, the ISO codes for countries are all Upper Case.
		// So, if someone needs the list as the official records,
		// it will convert.
		if (!empty($upper)) {
			return array_change_key_case($countries, CASE_UPPER);
		}
		return $countries;
	}

	/**
	 * Returns an XML document containing the list of countries supported by the
	 * Google geocoder.
	 * was google_geocode_country_list_xml() - should it parse this?
	 */
	private function _listCountriesXml() {
		// Get the google data from the feed.
		$response = make_http_request('http://spreadsheets.google.com/feeds/list/p9pdwsai2hDMsLkXsoM05KQ/default/public/values');

		if (!defined('LIBXML_VERSION') || (version_compare(phpversion(), '5.1.0', '<'))) {
			$xml = simplexml_load_string($response->body, NULL);
		}
		else {
			$xml = simplexml_load_string($response->body, NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
		}

		return $xml;
	}

	/**
	 * Builds 'q' query string parameter for Google geocoder based on location data.
	 * Uses $reverse parameter if you want reverse geocoding (address based on lat/lon).
	 * @param array $location
	 *   The location array to process.
	 * @param bool $reverse
	 *   Whether to perform reverse geocoding (address from lat/lon) or not.
	 *   Default FALSE (i.e., regular geocoding.)
	 *  @return $address
	 *    A comma-delimited query string for Google's geocoder.
	 *
	 *  was function _google_geocode_flatten
	 */
	private function _flattenQuery($location = array(), $reverse = FALSE) {
		// Check if its a valid address
		if (empty($location)) {
			return '';
		}

		// If reverse geocoding is wanted, check to see if there's a lat/lon
		// & build query string parameter off of these.
		if($reverse == TRUE) {
			$address = '';
			if(!empty($location['latitude']) && !empty($location['longitude'])) {
				$address .= $location['latitude'] . ',' . $location['longitude'];
			}
			else {
				return $address;
			}
		}
		// Otherwise, build query string parameter off of address values.
		else {
			$address = '';
			if (!empty($location['street'])) {
				$address .= $location['street'];
			}
			if (!empty($location['city'])) {
				if (!empty($location['street'])) {
					$address .= ', ';
				}
				$address .= $location['city'];
			}

			if (!empty($location['province'])) {
				if (!empty($location['street']) || !empty($location['city'])) {
					$address .= ', ';
				}
				// @todo: Fix this!
				if (substr($location['province'], 0, 3) == $location['country'] .'-') {
					$address .= substr($location['province'], 3);
					//watchdog('Location', 'BUG: Country found in province attribute.');
				}
				else {
					$address .= $location['province'];
				}
			}

			if (!empty($location['postal_code'])) {
				if (!empty($address)) {
					$address .= ' ';
				}
				$address .= $location['postal_code'];
			}
		}

		return $address;
	}
}

/**
 *  Make an HTTP Request, using the PECL HTTP library.
 *  
 *  @return string
 *    Either the value or the error code.
 */
function make_http_request($pUrl, array $pQuery = array(), $pMethod = HttpRequest::METH_GET) {
  if(class_exists('HttpRequest')) {
    $r = new HttpRequest($pUrl, HttpRequest::METH_GET);
    $lResponse = new stdClass();
    // Set the query string data, if any.
    if(count($pQuery) > 0) {
      $r->addQueryData($pQuery);
    }
    try {
	  $r->send();
	  $lResponse->message = $r->getRawRequestMessage();
	  $lResponse->code = $r->getResponseCode();
	  if($lResponse->code == 200) {
		$lResponse->body = $r->getResponseBody();
	  }
	  else {
	  	$lResponse->body = NULL;
	  }
    }
    catch(HttpException $e) {
	  $lResponse->code = $e->getMessage();
	  $lResponse->body = NULL;
    } 
  }
  else {
    throw new Exception('Class does not exist: HttpRequest');
  }
  return $lResponse;
}

/**
 * Temp: Wrapper around krumo 
 * @todo: Make this a separate logging class for the whole project.
 */
function dpm($var, $label = 'variable') {
  if(GoogleGeocoder::isLogging == TRUE) {
    if(function_exists('krumo')) {
	  krumo(array($label => $var));
    }
    else {
	  echo $label . ': ' . $var;
    }
  }
}

