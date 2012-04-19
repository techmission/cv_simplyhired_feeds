<?php

/**
 * Gets innerXML from a string.
 * Based on a function on http://stackoverflow.com/questions/1937056 */
function xt_getInnerXML($element)
{
  if(!is_object($element)) {
    return '';
  }
  else {
    $xml_text = $element->asXML();
  }
  // Strip the first element.
  // Check also if the stripped tag is empty.
  trim($xml_text);
  $s1 = strpos($xml_text,">");        
  $s2 = trim(substr($xml_text,0,$s1)); //get the head with ">" and trim (note that string is indexed from 0)
  if ($s2[strlen($s2)-1]=="/") //tag is empty
     return "";
  $s3 = strrpos($xml_text,"<"); //get last closing "<"        
    return substr($xml_text,$s1+1,$s3-$s1-1);
}
