<?php

/**
 * Gets innerXML from a string.
 * Based on a function on http://stackoverflow.com/questions/1937056  
 */
function xt_getInnerXML($element)
{
  $stripped = '';
  // Return empty string if not passed an XML element.
  if(!is_object($element)) {
    return $stripped;
  }
  // Otherwise convert to an XML string using the SimpleXML method.
  else {
    $xml_text = $element->asXML();
  }
  // Remove an '<@attributes/> tag if there is one.
  $xml_text = str_replace('<@attributes/>', '', $xml_text);
  // Strip the first element.
  // Check also if the stripped tag is empty.
  trim($xml_text);
  $s1 = strpos($xml_text,'>');        
  $s2 = trim(substr($xml_text,0,$s1)); //get the head with ">" and trim (note that string is indexed from 0)
  if ($s2[strlen($s2)-1]=='/') //tag is empty
     return "";
  $s3 = strrpos($xml_text,'<'); //get last closing "<"        
    $stripped = substr($xml_text,$s1+1,$s3-$s1-1);
    // Decode any HTML entities.
    $stripped = html_entity_decode($stripped);
    return $stripped;
}

/**
 * Gets the attribute value from a SimpleXMLElement.
 */
function xt_getAttrVal($element)
{
  $stripped = '';
  // Return empty string if not passed an XML element.
  if(!is_object($element)) {
    return $stripped;
  }
  // Otherwise convert to an XML string using the SimpleXML method.
  else {
    $xml_text = $element->asXML();
  }
  // Strip the first element.
  // Check also if the stripped tag is empty.
  trim($xml_text);
  $s1 = strpos($xml_text,'=');
  $s2 = trim(substr($xml_text,0,$s1)); //get the head with "=" and trim (note that string is indexed from 0)
  $s3 = strrpos($xml_text,'"'); //get last closing '"'
    $stripped = substr($xml_text,$s1+2,$s3);
    $stripped = rtrim($stripped, '"');
    // Decode any HTML entities.
    $stripped = html_entity_decode($stripped);
    return $stripped;
}

// Converts XML to an array, recursively.
// From http://www.php.net/manual/en/book.simplexml.php#108039
function xt_xml_to_array(SimpleXMLElement $xml) {
  $array = json_decode(json_encode($xml), TRUE);

  foreach ( array_slice($array, 0) as $key => $value ) {
	if ( empty($value) ) $array[$key] = NULL;
	  elseif ( !is_array($value) ) $array[$key] = xt_xml_to_array($value);
	}

  return $array;
}

/**
 *  Make an HTTP Request, using the PECL HTTP library.
 *
 *  @return stdClass object
 *    An object with the following properties:
 *      - request: the raw request (GET or POST)
 *      - code: either the HTTP response code (200, 301, etc.), or the HttpRequest exception message
 *      - body: the response body (only set if the response code was 200)
 */
if(!function_exists('make_http_request')) {
	function make_http_request($pUrl, array $pQuery = array(), $pMethod = HttpRequest::METH_GET, $pDebug = FALSE, $options = array('redirect' => 1)) {
		if(class_exists('HttpRequest')) {			
			if($pDebug == TRUE) {
				echo "<pre>";
				echo print_r(array('url' => $pUrl, 'query' => $pQuery), TRUE);
				echo "</pre>";
			}
			$r = new HttpRequest($pUrl, HttpRequest::METH_GET, $options);
			$lResponse = new stdClass();
			// Set the query string data, if any.
			if(count($pQuery) > 0) {
				$r->addQueryData($pQuery);
			}
			try {
				$r->send();
				$lResponse->request = $r->getRawRequestMessage();
				$lResponse->code = $r->getResponseCode();
				if($lResponse->code == 200) {
					$lResponse->body = $r->getResponseBody();
				}
				else {
					$lResponse->body = NULL;
				}
			}
			catch(HttpException $e) {
				$lResponse->code = get_class($e) . ' ' . $e->getMessage();
				$lResponse->body = NULL;
			}
		}
		else {
			throw new Exception('Class does not exist: HttpRequest');
		}
		return $lResponse;
	}
}

/* Standard terms used for inclusion in a query. */
function _get_include_terms() {
 $include_terms = array(
   0 => 'pastor',
   1 => 'church',
   2 => 'chaplain',
   // 3 => 'minister',            /* only in US */
   4 => 'christian', 
   5 => 'jesus', 
   6 => 'gospel', 
   7 => 'catholic',
   // 8 => 'ministry',            /* only in US */
   //10 => 'evangelical',
   11 => 'christ',
   // 12 => 'faith-based',
   13 => 'Protestant',
   // 15 => '"rescue mission"',
   17 => '"Salvation Army"',
   // 18 => '"World Vision"',
   19 => 'missionary',
   20 => 'baptist',
   21 => 'lutheran',
   22 => 'methodist',
   23 => 'presbyterian',
   24 => 'pentecostal',
   // 25 => 'denominational',
   //26 => 'evangelical',
   // 27 => 'calvary',
   // 28 => '"born again"',
   29 => 'orthodox',
   30 => 'anglican',
   // 33 => 'apostolic',
   // 34 => 'worship',
   // 35 => 'choir',
   // 38 => 'diocese',
   // 41 => 'discipleship',
   // 43 => '"Volunteers of America"',
   // 46 => '"Samaritan\'s Purse"',
   // 48 => '"Compassion International"',
   // 51 => '"Covenant House"',
   // 56 => '"Young Life"',
   // 57 => '"Focus on the Family"',
   58 => 'bible',
   // 59 => '"Billy Graham"',
   // 67 => '"Operation Blessing"',
   // 68 => '"Prison Fellowship"',
   // 70 => '"Medical Teams International"',
   // 71 => '"MAP International"',
   // 72 => '"Kingsway Charities"',
   // 73 => 'gideons',
   // 75 => 'Biblica',
   // 76 => '"Life Outreach"',
   // 77 => '"World Relief"',
   // 78 => '"Trans World Radio"',
   // 79 => '"Mission Aviation Fellowship"',
   // 80 => '"Alliance Defense Fund"',
   // 81 => '"Blessings International"',
   // 82 => '"Eternal Word Television"',
   // 84 => 'Chi Alpha',
   // 85 => '"Church World Service"',
   // 87 => 'congregational',
   // 88 => '"crisis pregnancy"',
   // 90 => 'diaconal',
   // 91 => 'disciple',
   // 92 => 'Episcopal',
   // 93 => '"Feed the Children"',
   // 94 => '"Focus on the Family"',
   // 97 => '"Here\'s Life Inner City"',
   // 98 => 'holiness',
   // 99 => 'intercession',
   // 100 => 'Intervarsity',
   // 101 => 'liturgy',
   102 => 'Mennonite',
   // 103 => 'monastery',
   104 => 'Nazarene',
   105 => 'priest',
   106 => 'Quaker',
   // 107 => 'RUF',
   // 108 => '"spiritual director"',
   // 109 => 'theologian',
   // 110 => 'UYWI',
   // 111 => 'vicar',
   113 => 'worship',
 );
 return $include_terms;
}

/* Standard terms for exclusion on a query. */
function _get_exclude_terms() {
 $exclude_terms = array(
   1 => 'Muslim', 
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
   18 => 'synagogue',
   19 => '"Church St"',
   22 => 'transgender',
   23 => '"Church Rd"',
   24 => '"New Church"',
   25 => 'Universalist',
   26 => '"Church & Dwight"',
   27 => 'Buddhist',
 );
 return $exclude_terms;
}

