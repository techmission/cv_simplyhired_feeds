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
	  elseif ( is_array($value) ) $array[$key] = xt_xml_to_array($value);
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
	function make_http_request($pUrl, array $pQuery = array(), $pMethod = HttpRequest::METH_GET, $pDebug = FALSE) {
		if(class_exists('HttpRequest')) {			
			if($pDebug == TRUE) {
				echo "<pre>";
				echo print_r(array('url' => $pUrl, 'query' => $pQuery), TRUE);
				echo "</pre>";
			}
			$r = new HttpRequest($pUrl, HttpRequest::METH_GET);
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