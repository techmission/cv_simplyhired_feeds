<?php

class PDO_Ext extends PDO {

  /* From http://www.php.net/manual/en/pdostatement.bindvalue.php - comments */
	
  /**
   * Bind an array of values.
   * 
   * @param string $req : the query on which link the values
   * @param array $array : associative array containing the values ​​to bind
   * @param array $typeArray : associative array with the desired value for its corresponding key in $array
   **/
  function bindArrayValue($req, $array, $typeArray = false) {
    if(is_object($req) && ($req instanceof PDOStatement)) {
	  foreach($array as $key => $value) {
		if($typeArray) {
		  $req->bindValue(':' . $key, $value, $typeArray[$key]);
		}
		else {
		  if(is_int($value)) {
			$param = PDO::PARAM_INT;
		  }
		  else if(is_bool($value)) {
			$param = PDO::PARAM_BOOL;
		  }
		  else if(is_null($value)) {
			$param = PDO::PARAM_NULL;
		  }
		  else if(is_string($value)) {
			$param = PDO::PARAM_STR;
		  }
		  else {
		    $param = FALSE;
		  }
          if($param) {
			$req->bindValue(':$key',$value, $param);
		  }
		}
	  }
    }
  }
  
  public static function _valToInt(&$value) {
  	$value = (int) $value; 
  }
  
  public static function _valQuote(&$value) {
  	$value = PDO::quote($value);
  }

 /**
   * ## EXAMPLE ##
   * $array = array('language' => 'php','lines' => 254, 'publish' => true);
   * $typeArray = array('language' => PDO::PARAM_STR,'lines' => PDO::PARAM_INT,'publish' => PDO::PARAM_BOOL);
   * $req = 'SELECT * FROM code WHERE language = :language AND lines = :lines AND publish = :publish';
   * You can bind $array like that :
   * bindArrayValue($array,$req,$typeArray);
   * The function is more useful when you use limit clause because they need an integer.
   * */
}