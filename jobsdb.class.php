<?php

/**
 * @class Class for the inserts into jobs database.
 *
 * Requires PHP Data Objects (PDO) for connection.
 */
class JobsDB {
  /* Define constants. */

  // Default database configuration file.
  const CFG_FILE_DEFAULT = 'dbconfig.ini';

  /* Record types that can be inserted/deleted. */
  const RECORDS_JOB = 0; // Job records - default.

  /* Validation error codes. */
  const RES_ERROR_NOT_ARRAY = 1;
  const RES_ERROR_NO_MEMBERS = 2;
  const RES_ERROR_UNDEFINED_TABLE = 3;
  const RES_ERROR_WRONG_TABLE = 4;
  const RES_ERROR_WRONG_DATA = 5;

  /* Define class variables. */
  private $dbInfo; // array: connection information for database 
  private $connStr; // string: connection string

  public $isLogging = FALSE; // boolean: whether to log data
  public $cfgFile; // string: the configuration file
                          // containing the connection information
  public $tableName; // string: the table to which to write
  public $dbh; // resource: the database handle (used to do writes)

  /**
   * Initialize the database connection as part of the constructor.
   * Uses a config file to get the connection string.
   *
   * @param string $cfg_file
   *   Configuration file with database info. (Optional)
   * @return void
   */
  function __construct($pCfgFile = '') {
    // Check requirements.    
    if(!class_exists('PDO')) {
      throw new Exception('PDO class not available.');
    }

    // Set the configuration file.
    if(is_empty($pCfgFile)) {
      $this->cfgFile = self::CFG_FILE_DEFAULT;
    }
    else {
       // Only read .ini files.
       if(substr($pCfgFile, -4) == '.ini') {

         $this->cfgFile = $pCfgFile;
       }
       else {
         $this->cfgFile = self::CFG_FILE_DEFAULT;
       }
    }

    // Set the database connection info.
    try {
      _setDBInfo($this->cfgFile);
    }
    catch(Exception $e) {
      echo $e->getMessage();
    }

    // Set the connection string.
    _setConnStr();

    // Use the connection string to connect to the database.
    try {
      $this->dbh = new PDO($this->connStr, $this->dbInfo['username'], $this->dbInfo['password']);
      // If set to logging, log that connection was successful.
      if($this->isLogging == TRUE) {
        echo 'Connected to database';
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }
  
  /**
   *  Counts the number of records in a database table.
   *  @todo: Find a more efficient way to do this. 
   */
  public function countRecords() {
  	$numRows = FALSE;
  	if(is_empty($this->tableName)) {
  	  return $numRows;
  	}
  	else {
  	  try {
  	  	$lSql = 'SELECT id FROM ' . $this->tableName;
  	  	$stmt = $this->$dbh->query($lSql);
  	  	$numRows = $stmt->rowCount();
  	  }
  	  catch(PDOException $e) {
  	  	echo $e->getMessage();
  	  }
  	}
  }

  /**
   * Write to the database an array of records. 
   * By default, write to the jobs table.
   */
  public function createRecords($records, $type = self::RECORDS_JOB) {
    $num_rows = FALSE; // Assume error condition to start.
    // Set the table name to which to write based on type,
    // if not already set.
    if(is_empty($this->tableName)) {
      $this->tableName = _lookupTableName($type);
    }
    // Validate that the records can be written to this table.
    $errors = _validateRecords($records, $type);
    if($errors == FALSE) {
      // Actually create the records.
      $numRows = _createRecords($records);
    } 
    return $numRows;
  }

  /* Private function to do the dirty work of writing to the DB. */
  private function _createRecords($records) {
  	try {
  	  // Begin a transaction.
  	  $this->dbh->beginTransaction();
  	  // Set up the PDo statement.
      $lFields = array_keys($records);
      $lPdoSql = _buildStmt($lFields);
      $lNumRows = 0;
      // Iterate and insert the records.
      // @todo: Bind them instead.
      foreach($records as $record) {
        $lPdoValues = _buildValues($record);
        $stmt = $this->dbh->prepare($lPdoSql);
        $stmt->execute($lPdoValues);
        $lResult = $stmt->rowCount;
        $lNumRows = $lNumRows + $lResult;
      }
      // End the transaction.
      $this->dbh->commit();
  	}
  	// Catch an error if there was one.
  	catch(PDOException $e) {
  	  $this->dbh->rollBack();
  	  echo $e->getMessage();
  	}
  }

  private function _buildStmt($pFields) {
    $lFieldNamesStr = implode(',', $pFields);
    $lNamedParams = array();
    $lNamedParamsStr = '';
    $lPdoSql = '';
    foreach($pFields as $field) {
      $lNamedParams[] = ':' . $field;
    }
    $lNamedParamsStr = implode(',', $lNamedParams);
    $lPdoSql = 'INSERT INTO ' . $this->tableName . '(' . $lFieldNamesStr . ') VALUES(' . $lNamedParamsStr . ')';
    return $lPdoSql;
  }
  
  private function _buildValues($pRecord) {
    $lPdoValues = array();
    foreach($pRecord as $fieldName => $value) {
      $lPdoValues[':' . $fieldName] = $value;
    }
    return $lPdoValues;
  }
 
  /* Lookup the database table name by record type. */
  private function _lookupTableName($type) {
    $lTableName = '';
    // By default, use tbl_feeds_jobs.
    switch($type) {
      case self::RECORDS_JOB:
      default:
        $lTableName = 'tbl_feeds_jobs'; 
    }
    return $lTableName;
  }
     

  private function _validateRecords($records, $type = self::RECORDS_JOB) {
    $validation_errors = FALSE;
    // Check that there is a records array.
    if(!is_array($records)) {
     $validation_errors[] = self::RES_ERR_NOT_ARRAY;
    }
    // Check that it has members.
    else if(count($records) == 0) {
     $validation_errors[] = self::RES_ERR_NO_MEMBERS;
    }
    // Compare the record type to the table schema.
    $record_type_error = _checkRecordType($type);
    if(!empty($record_type_error)) {
      $validation_errors[] = $record_type_error;
      // Compare the record values to the table schema.
      $schema_errors = _checkSchema($records);
      if(is_array($schema_errors) && count($schema_errors) > 0) {
        $validation_errors[] += $schema_errors;
      }
    }
    return $validation_errors;
  }

  private function _checkRecordType($type = self::RECORDS_JOB) {
    $lTables = getTables();
    $error = NULL;
    if(!array_key_exists($this->tableName, $lTables)) {
      $error = self::RES_ERR_UNDEFINED_TABLE;
    }
    else if($lTables[$this->tableTable] != $type) {
      $error = self::RES_ERR_WRONG_DATA;
    }
  }
  
  private function _checkSchema($records) {
    $lTableSchema = getSchema($this->tableName);
    // @todo: Actually check the records against the schema.
    return void;
  }

  public function getTables() {
    return array('tbl_feeds_jobs' => self::RECORDS_JOB);
  }
  
  /*
  public function getSchema($pTableName = '') {
   // Array of table schemas.
   // Concept borrowed from Drupal.
   $schema = array('tbl_feeds_jobs' =>
                array('id' =>
                  array('type' => TYPE_INT,
                        'required' => FALSE,
                        'description' => 'Autoincrement')),
                array('title' =>
                  array('type' => TYPE_STRING,
                        'required' => TRUE,
                        'description' => 'Title of job')),
                array('changed' =>
                   array('type' => TYPE_UNIXTIME,
                         'required' => TRUE,
                         'description' => 'Date job changed')),
                array('teaser' =>
                   array('type' => TYPE_STRING,
                         'required' => FALSE,
                         'description' => 'Short version of description')),
                array('description' =>
                   array('type' => TYPE_STRING,
                         'required' => TRUE,
                         'description' => 'Job description.')),
                array('requirements' =>
                   array('type' => TYPE_STRING,
                         'required' => FALSE,
                         'description' => 'Job requirements.')),
                array('org_name' =>
                   array('type' => TYPE_STRING,
                         'required' => TRUE,
                         'description' => 'Organization name.')),
                array('start_date' =>
                   array('type' => TYPE_UNIXTIME,
                         'required' => FALSE,
                         'description' => 'Start date of job.')),
                array('end_date' =>
                   array('type' => TYPE_UNIXTIME,
                         'required' => FALSE,
                         'description' => 'End date of job.')),
                array('source' =>
                   array('type' => TYPE_STRING,
                         'required' => TRUE,
                         'description' => 'Source of feed data.')),
                array('url_alias' =>
                   array('type' => TYPE_STRING,
                         'required' => FALSE,
                         'description' => 'Short URL alias.')),
                array('full_url_alias' =>
                    array('type' => TYPE_STRING,
                          'required' => FALSE,
                          'description' => 'Full URL alias.')),
    );
    if(empty($lTableName)) {
      return $schema;
    }
    else {
      if(array_key_exists($lTableName, $schema)) {
        return $schema[$lTableName];
      }
    }
  } */ 

  /* Sets the database information based on the config file. */
  private function _setDBInfo() {
    // Check that the ini file exists.
    if(!file_exists($this->cfgFile)) {
      throw new Exception('Ini file does not exist.');
    }

    // Check if the ini file was parseable.
    $lDbInfo = parse_ini_file($this->cfgFile);
    // Check if the ini file was parseable.
    if(empty($lDbInfo) || !is_array($lDbInfo)) {
      throw new Exception('Ini file not parseable.');
    }
    // Check if the ini file contained the proper values.
    if(!empty($lDbInfo['hostname']) && !empty($lDbInfo['db_name'])
      && !empty($lDbInfo['username'])
      && !empty($lDbInfo['password'])) {
      $this->dbInfo = $lDbInfo;
    }
    else {
      $err_result = var_dump($lDbInfo);
      throw new Exception('There were missing required properties for database connection in ' . $this->cfgFile . ' Parsed value was: ' . $err_result);
    }
  }

  /* Sets the connection string for connecting to the database. */
  public function setConnStr() {
    $this->connStr = 'mysql:host=' . $dbInfo['hostname'] . ';dbname=' . $dbInfo['db_name'];
  }

  /* Return the current connection string. */
  public function getConnStr($echo = FALSE) {
    _echoOrReturn($connStr, $echo);
  }

  private function _echoOrReturn($var, $echo = FALSE) {
    if($echo) {
      echo $var;
    }
    else {
      return $var;
    }
  }
}
