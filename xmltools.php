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

/**
 *  Make an HTTP Request, using the PECL HTTP library.
 *
 *  @return string
 *    Either the value or the error code.
 */
if(!function_exists('make_http_request')) {
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
	  $lResponse->code = $e->getMessage();
	  $lResponse->body = NULL;
		}
	}
	else {
		throw new Exception('Class does not exist: HttpRequest');
	}
	return $lResponse;
  }
}