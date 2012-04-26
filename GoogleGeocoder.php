<?php

/**
 * @file
 * Google geocoder.
 * Based on the Google geocoder included as part of the Drupal Location module.
 */

class GoogleGeocoder {

	private $key = ''; // the API key

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
	    $key = $this->key;
	
		if($reverse == TRUE) {
			$gmap_q = $this->_flattenQuery($location, TRUE);
		}
		else {
			$gmap_q = $this->_flattenQuery($location);
		}
	
		$query = array(
				'key' => $key,
				'sensor' => 'false', // Required by TOS.
				'output' => 'json',
				//'ll' => 0,
				//'spn' => 0,
				'gl' => $location['country'],
				'q' => $gmap_q,
		);
	
		$url = 'http://maps.google.com/maps/geo' . '?' . implode('&', $query);
	
		dpm($url, 'Geocode URL');
	
		$google_geocode_data = array();
		$http_reply = make_http_request($url);
		// dpm($http_reply->data);
		$google_geocode_data = $this->getJSONarray($http_reply->data);
	
		$status_code = $google_geocode_data['Status']['code'];
		if ($status_code != 200) {
			if ($status_code == 620) {
				echo 'Google geocoding returned status code: ' . $status_code . ' This usually means you have been making too many requests within a short window of time.';
			}
			else {
				echo 'Google geocoding returned status code:  ' . $status_code;
			}
			return NULL;
		}
	
		dpm($google_geocode_data, 'google geocode data for location');
		// Location data is returned as an associative array from the JSON response of the Google geocoder.
		return array(
				'lat' => $google_geocode_data['Placemark'][0]['Point']['coordinates'][1],
				'lon' => $google_geocode_data['Placemark'][0]['Point']['coordinates'][0],
				'geocoded_street' => $google_geocode_data['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['Thoroughfare']['ThoroughfareName'],
				'geocoded_city' => $google_geocode_data['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['LocalityName'],
				'geocoded_state' => $google_geocode_data['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['AdministrativeAreaName'],
				'geocoded_country' => $google_geocode_data['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'],
				'geocoded_accuracy' => $google_geocode_data['Placemark'][0]['AddressDetails']['Accuracy'],
				'geocoded_postalcode' =>$google_geocode_data['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['PostalCode']['PostalCodeNumber']
		);
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
		$source = make_http_request('http://spreadsheets.google.com/feeds/list/p9pdwsai2hDMsLkXsoM05KQ/default/public/values');

		if (!defined('LIBXML_VERSION') || (version_compare(phpversion(), '5.1.0', '<'))) {
			$xml = simplexml_load_string($source->data, NULL);
		}
		else {
			$xml = simplexml_load_string($source->data, NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
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

		// If reverse geocoding is wanted, check to see if there's a lat/lon & build query string parameter off of these.
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

	/**
	 * Given JSON data, will return an associative array of the data.
	 *  @param $google_json_contents
	 *    The contents of the JSON response from Google's geocoder.
	 *  @return
	 *    the JSON response converted to an associative array.
	 *
	 *  was function _google_geocode_get_JSON_array
	 */

	private function _getJSONarray ($google_json_contents) {
		$geocode_google_data = json_decode($google_json_contents, TRUE);
		return $geocode_google_data;
	}
}

/**
 *  Based on Drupal 6 drupal_http_request:
 *  http://api.drupal.org/api/drupal/includes%21common.inc/function/drupal_http_request/6
 *  Modified to remove additional dependencies on Drupal.
 *  This includes the code to handle retries and timeouts.
 */
function make_http_request($url, $headers = array(), $method = 'GET', $data = NULL) {
	$result = new stdClass();

	// Parse the URL and make sure we can handle the schema.
	$uri = parse_url($url);

	if ($uri == FALSE) {
		$result->error = 'unable to parse URL';
		$result->code = -1001;
		return $result;
	}

	if (!isset($uri['scheme'])) {
		$result->error = 'missing schema';
		$result->code = -1002;
		return $result;
	}

	switch ($uri['scheme']) {
		case 'http':
		case 'feed':
			$port = isset($uri['port']) ? $uri['port'] : 80;
			$host = $uri['host'] . ($port != 80 ? ':' . $port : '');
			$fp = @fsockopen($uri['host'], $port, $errno, $errstr, $timeout);
			break;
		case 'https':
			// Note: Only works for PHP 4.3 compiled with OpenSSL.
			$port = isset($uri['port']) ? $uri['port'] : 443;
			$host = $uri['host'] . ($port != 443 ? ':' . $port : '');
			$fp = @fsockopen('ssl://' . $uri['host'], $port, $errno, $errstr, $timeout);
			break;
		default:
			$result->error = 'invalid schema ' . $uri['scheme'];
			$result->code = -1003;
			return $result;
	}

	// Make sure the socket opened properly.
	if (!$fp) {
		// When a network error occurs, we use a negative number so it does not
		// clash with the HTTP status codes.
		$result->code = -$errno;
		$result->error = trim($errstr);

		return $result;
	}

	// Construct the path to act on.
	$path = isset($uri['path']) ? $uri['path'] : '/';
	if (isset($uri['query'])) {
		$path .= '?' . $uri['query'];
	}

	// Create HTTP request.
	$defaults = array(
			// RFC 2616: "non-standard ports MUST, default ports MAY be included".
			// We don't add the port to prevent from breaking rewrite rules checking the
			// host that do not take into account the port number.
			'Host' => "Host: $host",
			'User-Agent' => 'User-Agent: ChristianVolunteering Feeds (+http://www.christianvolunteering.org/)',
	);

	// Only add Content-Length if we actually have any content or if it is a POST
	// or PUT request. Some non-standard servers get confused by Content-Length in
	// at least HEAD/GET requests, and Squid always requires Content-Length in
	// POST/PUT requests.
	$content_length = strlen($data);
	if ($content_length > 0 || $method == 'POST' || $method == 'PUT') {
		$defaults['Content-Length'] = 'Content-Length: ' . $content_length;
	}

	// If the server url has a user then attempt to use basic authentication
	if (isset($uri['user'])) {
		$defaults['Authorization'] = 'Authorization: Basic ' . base64_encode($uri['user'] . (!empty($uri['pass']) ? ":" . $uri['pass'] : ''));
	}

	foreach ($headers as $header => $value) {
		$defaults[$header] = $header . ': ' . $value;
	}

	$request = $method . ' ' . $path . " HTTP/1.0\r\n";
	$request .= implode("\r\n", $defaults);
	$request .= "\r\n\r\n";
	$request .= $data;

	$result->request = $request;

	// Fetch response.
	$response = '';
	while (!feof($fp)) {
		$chunk = fread($fp, 1024);
		$response .= $chunk;
	}
	fclose($fp);

	// Parse response.
	list($split, $result->data) = explode("\r\n\r\n", $response, 2);
	$split = preg_split("/\r\n|\n|\r/", $split);

	list($protocol, $code, $status_message) = explode(' ', trim(array_shift($split)), 3);
	$result->protocol = $protocol;
	$result->status_message = $status_message;

	$result->headers = array();

	// Parse headers.
	while ($line = trim(array_shift($split))) {
		list($header, $value) = explode(':', $line, 2);
		if (isset($result->headers[$header]) && $header == 'Set-Cookie') {
			// RFC 2109: the Set-Cookie response header comprises the token Set-
			// Cookie:, followed by a comma-separated list of one or more cookies.
			$result->headers[$header] .= ',' . trim($value);
		}
		else {
			$result->headers[$header] = trim($value);
		}
	}

	$responses = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range not satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported',
	);
	// RFC 2616 states that all unknown HTTP codes must be treated the same as the
	// base code in their class.
	if (!isset($responses[$code])) {
		$code = floor($code / 100) * 100;
	}

	switch ($code) {
		case 200: // OK
		case 304: // Not modified
			break;
		case 301: // Moved permanently
		case 302: // Moved temporarily
		case 307: // Moved temporarily
			$location = $result->headers['Location'];
			$result->redirect_url = $location;
			break;
		default:
			$result->error = $status_message;
	}

	$result->code = $code;
	return $result;
}

/* Temp: Wrapper around krumo */
function dpm($var, $label = 'variable') {
	if(function_exists('krumo')) {
		krumo(array($label => $var));
	}
	else {
		echo $label . ': ' . $var;
	}
}

